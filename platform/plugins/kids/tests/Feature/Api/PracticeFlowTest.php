<?php

namespace Botble\Kids\Tests\Feature\Api;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Kids\Models\KidsAssignment;
use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsParent;
use Botble\Kids\Models\KidsReviewItem;
use Botble\Kids\Models\KidsSession;
use Botble\Kids\Models\KidsSkillLevel;
use Botble\Kids\Models\KidsTopic;
use Botble\Kids\Tests\KidsTestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class PracticeFlowTest extends KidsTestCase
{
    private KidsParent $parent;

    private KidsChild $child;

    private string $kidToken;

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

        $this->child = KidsChild::query()->create([
            'parent_id' => $this->parent->getKey(),
            'name' => 'Bé Bo',
            'avatar' => 'fox',
            'grade' => 2,
            'textbook' => 'ket_noi',
            'assigned_first' => false,
            'streak_current' => 0,
            'streak_best' => 0,
        ]);

        $this->kidToken = $this->child->createToken('kids-kid', ['kid'])->plainTextToken;

        $this->topic = KidsTopic::query()->create([
            'grade' => 2,
            'code' => 'g2.add_sub_100',
            'name' => 'Cộng trừ phạm vi 100',
            'generator' => 'arithmetic',
            'generator_config' => [
                'levels' => [
                    '1' => [
                        'ops' => ['+'],
                        'a' => [10, 20],
                        'b' => [1, 9],
                        'carry' => false,
                        'result_max' => 30,
                        'missing' => false,
                    ],
                    '2' => [
                        'ops' => ['+'],
                        'a' => [20, 40],
                        'b' => [10, 20],
                        'carry' => false,
                        'result_max' => 60,
                        'missing' => false,
                    ],
                ],
            ],
            'status' => TopicStatusEnum::APPROVED,
        ]);
    }

    public function test_session_payload_never_contains_answers(): void
    {
        $res = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', [
                'topic_id' => $this->topic->getKey(),
            ]);

        $res->assertStatus(201)
            ->assertJsonPath('error', false)
            ->assertJsonCount(10, 'data.questions');

        $data = $res->json('data');

        $this->assertPayloadDoesNotContainForbiddenKeys($data);
    }

    private function assertPayloadDoesNotContainForbiddenKeys(array $array): void
    {
        $forbidden = ['answer', 'solution', 'hints', 'params'];
        foreach ($array as $key => $value) {
            $this->assertNotContains($key, $forbidden, "Payload leaked forbidden key: {$key}");
            if (is_array($value)) {
                $this->assertPayloadDoesNotContainForbiddenKeys($value);
            }
        }
    }

    public function test_answering_flow_hints_and_revelation(): void
    {
        $sessionRes = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', [
                'topic_id' => $this->topic->getKey(),
            ]);

        $sessionId = $sessionRes->json('data.id');
        $questionId = $sessionRes->json('data.questions.0.id');

        // Empty answer -> 422 and does not consume attempt
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$sessionId}/questions/{$questionId}/answer", [
                'value' => '   ',
            ])
            ->assertStatus(422);

        // 1st wrong attempt -> returns hints without final answer
        $this->app['auth']->forgetGuards();
        $wrong1 = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$sessionId}/questions/{$questionId}/answer", [
                'value' => '999999',
            ]);

        $wrong1->assertStatus(200)
            ->assertJsonPath('data.correct', false)
            ->assertJsonPath('data.attempts', 1)
            ->assertJsonPath('data.state', 'open');

        $this->assertNotNull($wrong1->json('data.hint'));
        $this->assertNull($wrong1->json('data.revealed'));

        // 2nd wrong attempt -> reveals answer and solution
        $this->app['auth']->forgetGuards();
        $wrong2 = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$sessionId}/questions/{$questionId}/answer", [
                'value' => '999999',
            ]);

        $wrong2->assertStatus(200)
            ->assertJsonPath('data.correct', false)
            ->assertJsonPath('data.attempts', 2)
            ->assertJsonPath('data.state', 'revealed');

        $this->assertNull($wrong2->json('data.hint'));
        $this->assertNotNull($wrong2->json('data.revealed.answer'));
        $this->assertNotNull($wrong2->json('data.revealed.solution'));

        // 3rd attempt -> 409 Conflict
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$sessionId}/questions/{$questionId}/answer", [
                'value' => '999999',
            ])
            ->assertStatus(409);
    }

    public function test_answer_to_other_childs_session_is_404(): void
    {
        $sessionRes = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', [
                'topic_id' => $this->topic->getKey(),
            ]);

        $sessionId = $sessionRes->json('data.id');
        $questionId = $sessionRes->json('data.questions.0.id');

        $otherChild = KidsChild::query()->create([
            'parent_id' => $this->parent->getKey(),
            'name' => 'Bé Bi',
            'avatar' => 'cat',
            'grade' => 2,
        ]);
        $otherToken = $otherChild->createToken('kids-kid', ['kid'])->plainTextToken;

        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$otherToken}")
            ->postJson("/api/v1/kids/me/sessions/{$sessionId}/questions/{$questionId}/answer", [
                'value' => '10',
            ])
            ->assertStatus(404);
    }

    public function test_expired_session_rejects_answers_with_409(): void
    {
        Carbon::setTestNow('2026-10-27 10:00:00');

        $assignment = KidsAssignment::query()->create([
            'parent_id' => $this->parent->getKey(),
            'child_id' => $this->child->getKey(),
            'title' => 'Timed Assignment',
            'topic_ids' => [$this->topic->getKey()],
            'question_count' => 5,
            'time_limit_seconds' => 60,
        ]);

        $sessionRes = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', [
                'assignment_id' => $assignment->getKey(),
            ]);

        $sessionId = $sessionRes->json('data.id');
        $questionId = $sessionRes->json('data.questions.0.id');

        // Travel 65 seconds into the future
        Carbon::setTestNow('2026-10-27 10:01:05');

        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$sessionId}/questions/{$questionId}/answer", [
                'value' => '10',
            ])
            ->assertStatus(409);

        Carbon::setTestNow();
    }

    public function test_practice_locked_when_assigned_first_and_pending_assignment(): void
    {
        $this->child->update(['assigned_first' => true]);

        $assignment = KidsAssignment::query()->create([
            'parent_id' => $this->parent->getKey(),
            'child_id' => $this->child->getKey(),
            'title' => 'Mandatory Homework',
            'topic_ids' => [$this->topic->getKey()],
            'question_count' => 5,
        ]);

        // Self-practice is locked -> 423
        $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', [
                'topic_id' => $this->topic->getKey(),
            ])
            ->assertStatus(423);

        // Start assignment session -> 201
        $this->app['auth']->forgetGuards();
        $res = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', [
                'assignment_id' => $assignment->getKey(),
            ]);
        $res->assertStatus(201);
        $sessionId = $res->json('data.id');

        // Complete assignment session
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$sessionId}/complete")
            ->assertStatus(200);

        // Now self-practice is unlocked -> 201
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', [
                'topic_id' => $this->topic->getKey(),
            ])
            ->assertStatus(201);
    }

    public function test_completion_updates_level_after_five_first_try_correct(): void
    {
        $sessionRes = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', [
                'topic_id' => $this->topic->getKey(),
            ]);

        $sessionId = $sessionRes->json('data.id');
        $session = KidsSession::query()->find($sessionId);

        // Answer first 5 questions correctly on first try
        $questions = $session->questions()->take(5)->get();
        foreach ($questions as $q) {
            $expected = $q->toQuestion()->answer->value;
            $this->app['auth']->forgetGuards();
            $this->withHeader('Authorization', "Bearer {$this->kidToken}")
                ->postJson("/api/v1/kids/me/sessions/{$sessionId}/questions/{$q->getKey()}/answer", [
                    'value' => $expected,
                ])
                ->assertStatus(200);
        }

        // Complete session
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$sessionId}/complete")
            ->assertStatus(200);

        // Level should be promoted from 1 to 2
        $skill = KidsSkillLevel::query()->where('child_id', $this->child->getKey())
            ->where('topic_id', $this->topic->getKey())
            ->first();

        $this->assertNotNull($skill);
        $this->assertSame(2, $skill->level);
    }

    public function test_wrong_first_try_creates_review_item_due_tomorrow(): void
    {
        Carbon::setTestNow('2026-10-27 10:00:00');

        $sessionRes = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', [
                'topic_id' => $this->topic->getKey(),
            ]);

        $sessionId = $sessionRes->json('data.id');
        $firstQuestion = KidsSession::query()->find($sessionId)->questions()->first();

        // Answer wrong on first attempt
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$sessionId}/questions/{$firstQuestion->getKey()}/answer", [
                'value' => '9999',
            ]);

        // Complete session
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$sessionId}/complete")
            ->assertStatus(200);

        // Review item created, due tomorrow
        $reviewItem = KidsReviewItem::query()->where('child_id', $this->child->getKey())
            ->where('topic_id', $this->topic->getKey())
            ->first();

        $this->assertNotNull($reviewItem);
        $this->assertSame(1, $reviewItem->stage);
        $this->assertSame('2026-10-28 10:00:00', $reviewItem->due_at->format('Y-m-d H:i:s'));

        // Fast forward to tomorrow
        Carbon::setTestNow('2026-10-28 10:05:00');

        // Start new self-practice session -> should include the review item
        $this->app['auth']->forgetGuards();
        $newSessionRes = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', [
                'topic_id' => $this->topic->getKey(),
            ]);

        $newSession = KidsSession::query()->find($newSessionRes->json('data.id'));
        $hasReviewQuestion = $newSession->questions()->whereNotNull('review_item_id')->exists();
        $this->assertTrue($hasReviewQuestion, 'Tomorrow session must include due review item');

        Carbon::setTestNow();
    }

    public function test_streak_increments_on_consecutive_days_and_resets_after_gap(): void
    {
        // Day 1
        Carbon::setTestNow('2026-10-27 10:00:00');
        $s1 = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', ['topic_id' => $this->topic->getKey()])
            ->json('data.id');

        $this->app['auth']->forgetGuards();
        $sum1 = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$s1}/complete");
        $this->assertSame(1, $sum1->json('data.streak_current'));

        // Day 2 (consecutive) -> streak = 2
        Carbon::setTestNow('2026-10-28 10:00:00');
        $this->app['auth']->forgetGuards();
        $s2 = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', ['topic_id' => $this->topic->getKey()])
            ->json('data.id');

        $this->app['auth']->forgetGuards();
        $sum2 = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$s2}/complete");
        $this->assertSame(2, $sum2->json('data.streak_current'));

        // Day 4 (gap on day 3) -> resets streak to 1
        Carbon::setTestNow('2026-10-30 10:00:00');
        $this->app['auth']->forgetGuards();
        $s3 = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', ['topic_id' => $this->topic->getKey()])
            ->json('data.id');

        $this->app['auth']->forgetGuards();
        $sum3 = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$s3}/complete");
        $this->assertSame(1, $sum3->json('data.streak_current'));

        Carbon::setTestNow();
    }

    public function test_complete_is_idempotent(): void
    {
        $sessionRes = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson('/api/v1/kids/me/sessions', ['topic_id' => $this->topic->getKey()]);
        $sessionId = $sessionRes->json('data.id');

        $firstComplete = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$sessionId}/complete");
        $firstComplete->assertStatus(200);

        $this->app['auth']->forgetGuards();
        $secondComplete = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->postJson("/api/v1/kids/me/sessions/{$sessionId}/complete");
        $secondComplete->assertStatus(200);

        $this->assertSame($firstComplete->json('data'), $secondComplete->json('data'));
    }
}
