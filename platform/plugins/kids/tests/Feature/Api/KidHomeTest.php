<?php

namespace Botble\Kids\Tests\Feature\Api;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Kids\Models\KidsAssignment;
use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsParent;
use Botble\Kids\Models\KidsReward;
use Botble\Kids\Models\KidsTopic;
use Botble\Kids\Tests\KidsTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class KidHomeTest extends KidsTestCase
{
    private KidsParent $parent;

    private KidsChild $child;

    private string $kidToken;

    private KidsTopic $topic1;

    private KidsTopic $topic2;

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
            'assigned_first' => true,
        ]);

        $this->kidToken = $this->child->createToken('kids-kid', ['kid'])->plainTextToken;

        $this->topic1 = KidsTopic::query()->create([
            'grade' => 2,
            'code' => 'g2.t1',
            'name' => 'Chủ đề 1',
            'generator' => 'arithmetic',
            'status' => TopicStatusEnum::APPROVED,
        ]);

        $this->topic2 = KidsTopic::query()->create([
            'grade' => 2,
            'code' => 'g2.t2',
            'name' => 'Chủ đề 2 (Tắt)',
            'generator' => 'arithmetic',
            'status' => TopicStatusEnum::APPROVED,
        ]);

        // Disable topic2 for this child
        $this->child->update(['disabled_topic_ids' => [$this->topic2->getKey()]]);
    }

    public function test_get_me_home(): void
    {
        KidsAssignment::query()->create([
            'parent_id' => $this->parent->getKey(),
            'child_id' => $this->child->getKey(),
            'title' => 'Bài tập số 1',
            'topic_ids' => [$this->topic1->getKey()],
            'question_count' => 10,
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->getJson('/api/v1/kids/me');

        $res->assertStatus(200)
            ->assertJsonPath('error', false)
            ->assertJsonPath('data.child.name', 'Bé Bo')
            ->assertJsonCount(1, 'data.pending_assignments')
            ->assertJsonPath('data.pending_assignments.0.title', 'Bài tập số 1')
            ->assertJsonPath('data.pending_assignments.0.is_new', true)
            ->assertJsonPath('data.practice_locked', true);
    }

    public function test_get_me_topics_hides_disabled_and_draft_topics(): void
    {
        KidsTopic::query()->create([
            'grade' => 2,
            'code' => 'g2.draft',
            'name' => 'Draft topic',
            'generator' => 'arithmetic',
            'status' => TopicStatusEnum::DRAFT,
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->getJson('/api/v1/kids/me/topics');

        $res->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'g2.t1');
    }

    public function test_get_me_rewards(): void
    {
        KidsReward::query()->create([
            'child_id' => $this->child->getKey(),
            'sticker_key' => 'star-gold',
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$this->kidToken}")
            ->getJson('/api/v1/kids/me/rewards');

        $res->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sticker_key', 'star-gold');
    }
}
