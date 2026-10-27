<?php

namespace Botble\Kids\Tests\Feature\Api;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Kids\Models\KidsAssignment;
use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsParent;
use Botble\Kids\Models\KidsTopic;
use Botble\Kids\Tests\KidsTestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class ParentApiTest extends KidsTestCase
{
    private KidsParent $parent;

    private string $parentToken;

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
    }

    public function test_parent_can_crud_own_children(): void
    {
        // 1. Create child
        $res = $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->postJson('/api/v1/kids/parent/children', [
                'name' => 'Bé Bo',
                'avatar' => 'fox',
                'grade' => 2,
                'textbook' => 'ket_noi',
                'assigned_first' => true,
            ]);

        $res->assertStatus(201)
            ->assertJsonPath('error', false)
            ->assertJsonPath('data.name', 'Bé Bo')
            ->assertJsonPath('data.grade', 2)
            ->assertJsonPath('data.assigned_first', true);

        $childId = $res->json('data.id');

        // 2. List children
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->getJson('/api/v1/kids/parent/children')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $childId);

        // 3. Update child
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->patchJson("/api/v1/kids/parent/children/{$childId}", [
                'name' => 'Bé Bo Đẹp Trai',
                'avatar' => 'dino',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Bé Bo Đẹp Trai')
            ->assertJsonPath('data.avatar', 'dino');
    }

    public function test_cannot_update_other_parents_child(): void
    {
        $otherParent = KidsParent::query()->create([
            'name' => 'Other',
            'username' => 'other',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]);

        $childOther = KidsChild::query()->create([
            'parent_id' => $otherParent->getKey(),
            'name' => 'Bé Khác',
            'avatar' => 'cat',
            'grade' => 2,
        ]);

        $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->patchJson("/api/v1/kids/parent/children/{$childOther->getKey()}", [
                'name' => 'Hack name',
            ])
            ->assertStatus(404);
    }

    public function test_assignment_validation(): void
    {
        $child = KidsChild::query()->create([
            'parent_id' => $this->parent->getKey(),
            'name' => 'Bé Bo',
            'avatar' => 'fox',
            'grade' => 2,
        ]);

        $approvedLeafGrade2 = KidsTopic::query()->create([
            'grade' => 2,
            'code' => 'g2.leaf_ok',
            'name' => 'Cộng trừ lá',
            'generator' => 'arithmetic',
            'status' => TopicStatusEnum::APPROVED,
        ]);

        $draftTopic = KidsTopic::query()->create([
            'grade' => 2,
            'code' => 'g2.draft',
            'name' => 'Draft',
            'generator' => 'arithmetic',
            'status' => TopicStatusEnum::DRAFT,
        ]);

        $grade3Topic = KidsTopic::query()->create([
            'grade' => 3,
            'code' => 'g3.leaf',
            'name' => 'Grade 3',
            'generator' => 'arithmetic',
            'status' => TopicStatusEnum::APPROVED,
        ]);

        // Draft topic rejected -> 422
        $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->postJson('/api/v1/kids/parent/assignments', [
                'child_id' => $child->getKey(),
                'title' => 'Bài tập draft',
                'topic_ids' => [$draftTopic->getKey()],
            ])
            ->assertStatus(422);

        // Different grade topic rejected -> 422
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->postJson('/api/v1/kids/parent/assignments', [
                'child_id' => $child->getKey(),
                'title' => 'Bài tập lớp 3',
                'topic_ids' => [$grade3Topic->getKey()],
            ])
            ->assertStatus(422);

        // question_count: 31 rejected -> 422
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->postJson('/api/v1/kids/parent/assignments', [
                'child_id' => $child->getKey(),
                'title' => 'Quá nhiều câu',
                'topic_ids' => [$approvedLeafGrade2->getKey()],
                'question_count' => 31,
            ])
            ->assertStatus(422);

        // Valid assignment -> 200
        $this->app['auth']->forgetGuards();
        $res = $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->postJson('/api/v1/kids/parent/assignments', [
                'child_id' => $child->getKey(),
                'title' => 'Bài tập hợp lệ',
                'topic_ids' => [$approvedLeafGrade2->getKey()],
                'question_count' => 10,
                'difficulty' => 'easy',
            ]);

        $res->assertStatus(201)
            ->assertJsonPath('data.title', 'Bài tập hợp lệ')
            ->assertJsonPath('data.difficulty', 'easy');

        $assignmentId = $res->json('data.id');

        // List assignments
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->getJson("/api/v1/kids/parent/assignments?child_id={$child->getKey()}")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // Delete own assignment -> 200
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->deleteJson("/api/v1/kids/parent/assignments/{$assignmentId}")
            ->assertStatus(200);
    }

    public function test_cannot_delete_other_parents_assignment(): void
    {
        $otherParent = KidsParent::query()->create([
            'name' => 'Other',
            'username' => 'other',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]);

        $childOther = KidsChild::query()->create([
            'parent_id' => $otherParent->getKey(),
            'name' => 'Bé Khác',
            'avatar' => 'cat',
            'grade' => 2,
        ]);

        $assignment = KidsAssignment::query()->create([
            'parent_id' => $otherParent->getKey(),
            'child_id' => $childOther->getKey(),
            'title' => 'Bài của nhà khác',
            'topic_ids' => [1],
        ]);

        $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->deleteJson("/api/v1/kids/parent/assignments/{$assignment->getKey()}")
            ->assertStatus(404);
    }

    public function test_put_pin(): void
    {
        // Wrong password -> 422
        $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->putJson('/api/v1/kids/parent/pin', [
                'password' => 'wrongpass',
                'pin' => '1234',
            ])
            ->assertStatus(422);

        // Invalid pin format -> 422
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->putJson('/api/v1/kids/parent/pin', [
                'password' => 'secret123',
                'pin' => '12',
            ])
            ->assertStatus(422);

        // Success -> 200
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->putJson('/api/v1/kids/parent/pin', [
                'password' => 'secret123',
                'pin' => '5678',
            ])
            ->assertStatus(200);

        $this->parent->refresh();
        $this->assertTrue(Hash::check('5678', $this->parent->pin_hash));
    }

    public function test_get_topics_filters_by_grade_and_approved_only(): void
    {
        KidsTopic::query()->create([
            'grade' => 2,
            'code' => 'g2.t1',
            'name' => 'Topic Lớp 2',
            'generator' => 'arithmetic',
            'status' => TopicStatusEnum::APPROVED,
        ]);

        KidsTopic::query()->create([
            'grade' => 2,
            'code' => 'g2.draft',
            'name' => 'Topic Lớp 2 Draft',
            'generator' => 'arithmetic',
            'status' => TopicStatusEnum::DRAFT,
        ]);

        KidsTopic::query()->create([
            'grade' => 3,
            'code' => 'g3.t1',
            'name' => 'Topic Lớp 3',
            'generator' => 'arithmetic',
            'status' => TopicStatusEnum::APPROVED,
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$this->parentToken}")
            ->getJson('/api/v1/kids/parent/topics?grade=2');

        $res->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'g2.t1')
            ->assertJsonPath('data.0.is_leaf', true);
    }
}
