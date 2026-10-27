<?php

namespace Botble\Kids\Tests\Feature;

require_once dirname(__DIR__).'/KidsTestCase.php';

use Botble\Kids\Engine\Generators\ArithmeticGenerator;
use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsParent;
use Botble\Kids\Models\KidsSession;
use Botble\Kids\Models\KidsSessionQuestion;
use Botble\Kids\Models\KidsTopic;
use Botble\Kids\Tests\KidsTestCase;
use Random\Engine\Mt19937;
use Random\Randomizer;

class ModelsTest extends KidsTestCase
{
    public function test_models_relationships_and_question_conversion(): void
    {
        $parent = KidsParent::query()->create([
            'name' => 'Nguyen Van A',
            'username' => 'parent_test_1',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        $child = KidsChild::query()->create([
            'parent_id' => $parent->getKey(),
            'name' => 'Bé Bo',
            'avatar' => 'fox',
            'grade' => 2,
            'textbook' => 'ket_noi',
        ]);

        $this->assertTrue($child->parent->is($parent));
        $this->assertTrue($parent->children->first()->is($child));

        $topic = KidsTopic::query()->create([
            'grade' => 2,
            'code' => 'g2.add_sub_test',
            'name' => 'Cộng trừ cơ bản',
            'generator' => 'arithmetic',
            'status' => TopicStatusEnum::APPROVED,
        ]);

        $session = KidsSession::query()->create([
            'child_id' => $child->getKey(),
            'seed' => 123456,
            'started_at' => now(),
            'total' => 10,
        ]);

        $generator = new ArithmeticGenerator;
        $config = [
            'levels' => [
                '1' => [
                    'ops' => ['+'],
                    'a' => [10, 20],
                    'b' => [1, 9],
                    'carry' => false,
                    'result_max' => 30,
                    'missing' => false,
                ],
            ],
        ];

        $rng = new Randomizer(new Mt19937(123456));
        $engineQuestion = $generator->generate(1, $rng, $config);

        $sessionQuestion = new KidsSessionQuestion([
            'session_id' => $session->getKey(),
            'position' => 1,
            'topic_id' => $topic->getKey(),
            'level' => 1,
        ]);
        $sessionQuestion->fillFromQuestion($engineQuestion);
        $sessionQuestion->save();

        $reloaded = KidsSessionQuestion::query()->find($sessionQuestion->getKey());
        $this->assertNotNull($reloaded);
        $this->assertSame($engineQuestion->answer->value, $reloaded->toQuestion()->answer->value);
        $this->assertSame($engineQuestion->prompt['text'], $reloaded->toQuestion()->prompt['text']);

        // Test cascade delete
        $parent->delete();
        $this->assertDatabaseMissing('kids_children', ['id' => $child->getKey()]);
    }

    public function test_topic_approved_scope(): void
    {
        KidsTopic::query()->create([
            'grade' => 2,
            'code' => 'g2.draft_topic',
            'name' => 'Chủ đề nháp',
            'status' => TopicStatusEnum::DRAFT,
        ]);

        KidsTopic::query()->create([
            'grade' => 2,
            'code' => 'g2.approved_topic',
            'name' => 'Chủ đề duyệt',
            'status' => TopicStatusEnum::APPROVED,
        ]);

        $approvedTopics = KidsTopic::query()->approved()->pluck('code')->all();

        $this->assertContains('g2.approved_topic', $approvedTopics);
        $this->assertNotContains('g2.draft_topic', $approvedTopics);
    }
}
