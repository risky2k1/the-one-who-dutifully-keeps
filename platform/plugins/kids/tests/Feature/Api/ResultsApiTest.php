<?php

namespace Botble\Kids\Tests\Feature\Api;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Enums\SessionStatusEnum;
use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Kids\Models\KidsAssignment;
use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsParent;
use Botble\Kids\Models\KidsSession;
use Botble\Kids\Models\KidsSessionQuestion;
use Botble\Kids\Models\KidsTopic;
use Botble\Kids\Tests\KidsTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class ResultsApiTest extends KidsTestCase
{
    private KidsParent $parent;

    private string $parentToken;

    private KidsChild $child;

    private KidsTopic $topic;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->parent = KidsParent::query()->create([
            'name' => 'Nguyen Van A',
            'username' => 'parent1',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]);
        $this->parentToken = $this->parent->createToken('kids-parent', ['parent'])->plainTextToken;

        $this->child = KidsChild::query()->create([
            'parent_id' => $this->parent->getKey(),
            'name' => 'Bé Bo',
            'avatar' => 'fox',
            'grade' => 2,
        ]);

        $this->topic = KidsTopic::query()->create([
            'grade' => 2,
            'code' => 'g2.add_sub_100',
            'name' => 'Cộng trừ phạm vi 100',
            'generator' => 'arithmetic',
            'status' => TopicStatusEnum::APPROVED,
        ]);
    }

    public function test_assignment_results_first_and_latest(): void
    {
        $assignment = KidsAssignment::query()->create([
            'parent_id' => $this->parent->getKey(),
            'child_id' => $this->child->getKey(),
            'title' => 'Bài tập 1',
            'topic_ids' => [$this->topic->getKey()],
            'question_count' => 10,
        ]);

        // Session 1 (first)
        KidsSession::query()->create([
            'child_id' => $this->child->getKey(),
            'assignment_id' => $assignment->getKey(),
            'seed' => 1,
            'status' => SessionStatusEnum::COMPLETED,
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHours(2)->addMinutes(5),
            'first_try_correct' => 6,
            'total' => 10,
        ]);

        // Session 2 (latest)
        KidsSession::query()->create([
            'child_id' => $this->child->getKey(),
            'assignment_id' => $assignment->getKey(),
            'seed' => 2,
            'status' => SessionStatusEnum::COMPLETED,
            'started_at' => now()->subHour(),
            'completed_at' => now()->subHour()->addMinutes(4),
            'first_try_correct' => 9,
            'total' => 10,
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->getJson("/api/v1/kids/parent/children/{$this->child->getKey()}/results?assignment_id={$assignment->getKey()}");

        $res->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attempts_count', 2)
            ->assertJsonPath('data.0.first.first_try_correct', 6)
            ->assertJsonPath('data.0.latest.first_try_correct', 9);
    }

    public function test_weak_skills_requires_at_least_five_answered_and_accuracy_below_point_seven(): void
    {
        $session = KidsSession::query()->create([
            'child_id' => $this->child->getKey(),
            'seed' => 1,
            'status' => SessionStatusEnum::COMPLETED,
            'started_at' => now(),
            'total' => 10,
        ]);

        // Create 4 questions for topic (not enough, minimum 5 required)
        for ($i = 1; $i <= 4; $i++) {
            KidsSessionQuestion::query()->create([
                'session_id' => $session->getKey(),
                'position' => $i,
                'topic_id' => $this->topic->getKey(),
                'type' => 'arithmetic',
                'prompt' => ['type' => 'arithmetic', 'text' => "1 + {$i} = ?", 'input' => 'number'],
                'answer' => ['kind' => 'integer', 'value' => (string) (1 + $i)],
                'hints' => [],
                'solution' => [],
                'params' => [],
                'first_try_correct' => false,
            ]);
        }

        // Check weak-skills: 4 questions -> empty
        $res1 = $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->getJson("/api/v1/kids/parent/children/{$this->child->getKey()}/weak-skills");
        $res1->assertStatus(200)->assertJsonCount(0, 'data');

        // Add 5th and 6th questions: 1 correct, 5 wrong -> accuracy = 1/6 ~ 0.1667 (< 0.7)
        for ($i = 5; $i <= 6; $i++) {
            KidsSessionQuestion::query()->create([
                'session_id' => $session->getKey(),
                'position' => $i,
                'topic_id' => $this->topic->getKey(),
                'type' => 'arithmetic',
                'prompt' => ['type' => 'arithmetic', 'text' => "1 + {$i} = ?", 'input' => 'number'],
                'answer' => ['kind' => 'integer', 'value' => (string) (1 + $i)],
                'hints' => [],
                'solution' => [],
                'params' => [],
                'first_try_correct' => ($i === 5),
            ]);
        }

        $this->app['auth']->forgetGuards();
        $res2 = $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->getJson("/api/v1/kids/parent/children/{$this->child->getKey()}/weak-skills");

        $res2->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.topic_id', $this->topic->getKey())
            ->assertJsonPath('data.0.answered', 6);
    }

    public function test_session_review_includes_answers_and_solutions(): void
    {
        $session = KidsSession::query()->create([
            'child_id' => $this->child->getKey(),
            'seed' => 1,
            'status' => SessionStatusEnum::COMPLETED,
            'started_at' => now(),
            'total' => 1,
        ]);

        $q = KidsSessionQuestion::query()->create([
            'session_id' => $session->getKey(),
            'position' => 1,
            'topic_id' => $this->topic->getKey(),
            'type' => 'arithmetic',
            'prompt' => ['type' => 'arithmetic', 'text' => '10 + 20 = ?', 'input' => 'number'],
            'answer' => ['kind' => 'integer', 'value' => '30'],
            'hints' => ['10 + 20 = ?'],
            'solution' => ['10 + 20 = 30'],
            'params' => [],
            'attempts' => 1,
            'state' => 'correct',
            'first_try_correct' => true,
            'given_answers' => ['30'],
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->getJson("/api/v1/kids/parent/sessions/{$session->getKey()}");

        $res->assertStatus(200)
            ->assertJsonPath('data.id', $session->getKey())
            ->assertJsonPath('data.questions.0.answer', '30')
            ->assertJsonPath('data.questions.0.given_answers.0', '30')
            ->assertJsonPath('data.questions.0.solution.0', '10 + 20 = 30');
    }

    public function test_question_report(): void
    {
        $session = KidsSession::query()->create([
            'child_id' => $this->child->getKey(),
            'seed' => 1,
            'status' => SessionStatusEnum::COMPLETED,
            'started_at' => now(),
            'total' => 1,
        ]);

        $q = KidsSessionQuestion::query()->create([
            'session_id' => $session->getKey(),
            'position' => 1,
            'topic_id' => $this->topic->getKey(),
            'type' => 'arithmetic',
            'prompt' => ['type' => 'arithmetic', 'text' => '1 + 1 = ?', 'input' => 'number'],
            'answer' => ['kind' => 'integer', 'value' => '2'],
            'hints' => [],
            'solution' => [],
            'params' => [],
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->postJson("/api/v1/kids/parent/questions/{$q->getKey()}/report", [
                'note' => 'Đề bài bị nhầm phép tính',
            ]);

        $res->assertStatus(200);

        $this->assertDatabaseHas('kids_reports', [
            'question_id' => $q->getKey(),
            'parent_id' => $this->parent->getKey(),
            'note' => 'Đề bài bị nhầm phép tính',
            'status' => 'open',
        ]);
    }
}
