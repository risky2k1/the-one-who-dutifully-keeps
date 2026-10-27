<?php

namespace Botble\Kids\Tests\Feature\Api;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsParent;
use Botble\Kids\Tests\KidsTestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthTest extends KidsTestCase
{
    private KidsParent $parent;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->parent = KidsParent::query()->create([
            'name' => 'Nguyen Van A',
            'username' => 'parent1',
            'password' => Hash::make('secret123'),
            'pin_hash' => Hash::make('1234'),
            'status' => 'active',
        ]);
    }

    public function test_login_returns_parent_and_device_tokens(): void
    {
        $res = $this->postJson('/api/v1/kids/auth/login', [
            'username' => 'parent1',
            'password' => 'secret123',
            'device_name' => 'iPad Living Room',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('error', false)
            ->assertJsonStructure([
                'data' => [
                    'parent_token',
                    'device_token',
                    'parent' => ['id', 'name', 'username', 'has_pin'],
                ],
            ]);

        $this->assertTrue($res->json('data.parent.has_pin'));

        // Wrong password -> 422
        $this->postJson('/api/v1/kids/auth/login', [
            'username' => 'parent1',
            'password' => 'wrongpass',
            'device_name' => 'iPad',
        ])->assertStatus(422);

        // Inactive parent -> 422
        $this->parent->update(['status' => 'inactive']);
        $this->postJson('/api/v1/kids/auth/login', [
            'username' => 'parent1',
            'password' => 'secret123',
            'device_name' => 'iPad',
        ])->assertStatus(422);
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/kids/auth/login', [
                'username' => 'parent1',
                'password' => 'wrongpass',
                'device_name' => 'TestDevice',
            ])->assertStatus(422);
        }

        // 6th attempt -> 429
        $this->postJson('/api/v1/kids/auth/login', [
            'username' => 'parent1',
            'password' => 'secret123',
            'device_name' => 'TestDevice',
        ])->assertStatus(429);
    }

    public function test_device_token_can_list_own_children_only(): void
    {
        $child1 = KidsChild::query()->create([
            'parent_id' => $this->parent->getKey(),
            'name' => 'Bé Bo',
            'avatar' => 'fox',
            'grade' => 2,
        ]);

        $otherParent = KidsParent::query()->create([
            'name' => 'Tran Van B',
            'username' => 'parent2',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]);

        KidsChild::query()->create([
            'parent_id' => $otherParent->getKey(),
            'name' => 'Bé Bi',
            'avatar' => 'cat',
            'grade' => 2,
        ]);

        $deviceToken = $this->parent->createToken('kids-device', ['device'])->plainTextToken;

        $res = $this->withHeader('Authorization', "Bearer {$deviceToken}")
            ->getJson('/api/v1/kids/device/children');

        $res->assertStatus(200)
            ->assertJsonPath('error', false)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $child1->getKey())
            ->assertJsonPath('data.0.name', 'Bé Bo');
    }

    public function test_device_can_start_kid_session_for_own_child_and_404_for_other_family(): void
    {
        $child1 = KidsChild::query()->create([
            'parent_id' => $this->parent->getKey(),
            'name' => 'Bé Bo',
            'avatar' => 'fox',
            'grade' => 2,
        ]);

        $otherParent = KidsParent::query()->create([
            'name' => 'Tran Van B',
            'username' => 'parent2',
            'password' => Hash::make('secret123'),
            'status' => 'active',
        ]);

        $childOther = KidsChild::query()->create([
            'parent_id' => $otherParent->getKey(),
            'name' => 'Bé Bi',
            'avatar' => 'cat',
            'grade' => 2,
        ]);

        $deviceToken = $this->parent->createToken('kids-device', ['device'])->plainTextToken;

        // Own child -> 200 with kid_token
        $res = $this->withHeader('Authorization', "Bearer {$deviceToken}")
            ->postJson("/api/v1/kids/device/children/{$child1->getKey()}/session");

        $res->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'kid_token',
                    'child' => ['id', 'name', 'avatar'],
                ],
            ]);

        // Other child -> 404
        $this->withHeader('Authorization', "Bearer {$deviceToken}")
            ->postJson("/api/v1/kids/device/children/{$childOther->getKey()}/session")
            ->assertStatus(404);
    }

    public function test_role_mismatch_returns_403(): void
    {
        $child = KidsChild::query()->create([
            'parent_id' => $this->parent->getKey(),
            'name' => 'Bé Bo',
            'avatar' => 'fox',
            'grade' => 2,
        ]);

        $parentToken = $this->parent->createToken('kids-parent', ['parent'])->plainTextToken;
        $deviceToken = $this->parent->createToken('kids-device', ['device'])->plainTextToken;
        $kidToken = $child->createToken('kids-kid', ['kid'])->plainTextToken;

        // Kid calling parent endpoint -> 403
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$kidToken}")
            ->getJson('/api/v1/kids/parent/children')
            ->assertStatus(403);

        // Device calling kid endpoint -> 403
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$deviceToken}")
            ->getJson('/api/v1/kids/me')
            ->assertStatus(403);

        // Parent calling kid endpoint -> 403
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$parentToken}")
            ->getJson('/api/v1/kids/me')
            ->assertStatus(403);
    }

    public function test_unlock_with_correct_pin_returns_parent_token(): void
    {
        $deviceToken = $this->parent->createToken('kids-device', ['device'])->plainTextToken;

        // Unlock with PIN
        $res = $this->withHeader('Authorization', "Bearer {$deviceToken}")
            ->postJson('/api/v1/kids/device/unlock', ['pin' => '1234']);

        $res->assertStatus(200)
            ->assertJsonStructure(['data' => ['parent_token']]);

        // Wrong PIN -> 422 (attempt 2)
        $this->withHeader('Authorization', "Bearer {$deviceToken}")
            ->postJson('/api/v1/kids/device/unlock', ['pin' => '9999'])
            ->assertStatus(422);

        // Rate limit PIN: attempts 3, 4, 5
        for ($i = 0; $i < 3; $i++) {
            $this->withHeader('Authorization', "Bearer {$deviceToken}")
                ->postJson('/api/v1/kids/device/unlock', ['pin' => '9999'])
                ->assertStatus(422);
        }

        // Attempt 6 -> 429
        $this->withHeader('Authorization', "Bearer {$deviceToken}")
            ->postJson('/api/v1/kids/device/unlock', ['pin' => '1234'])
            ->assertStatus(429);
    }

    public function test_unlock_when_no_pin_requires_password(): void
    {
        $this->parent->update(['pin_hash' => null]);
        $deviceToken = $this->parent->createToken('kids-device', ['device'])->plainTextToken;

        // Trying PIN when no pin configured -> 422
        $this->withHeader('Authorization', "Bearer {$deviceToken}")
            ->postJson('/api/v1/kids/device/unlock', ['pin' => '1234'])
            ->assertStatus(422);

        // Trying password -> 200
        $this->withHeader('Authorization', "Bearer {$deviceToken}")
            ->postJson('/api/v1/kids/device/unlock', ['password' => 'secret123'])
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['parent_token']]);
    }

    public function test_logout_revokes_parent_token(): void
    {
        $parentToken = $this->parent->createToken('kids-parent', ['parent'])->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$parentToken}")
            ->postJson('/api/v1/kids/auth/logout')
            ->assertStatus(200);

        // Calling again -> 401
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', "Bearer {$parentToken}")
            ->postJson('/api/v1/kids/auth/logout')
            ->assertStatus(401);
    }

    public function test_expired_kid_token_is_rejected(): void
    {
        $child = KidsChild::query()->create([
            'parent_id' => $this->parent->getKey(),
            'name' => 'Bé Bo',
            'avatar' => 'fox',
            'grade' => 2,
        ]);

        $tokenResult = $child->createToken('kids-kid', ['kid'], Carbon::now()->subMinutes(10));
        $kidToken = $tokenResult->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$kidToken}")
            ->getJson('/api/v1/kids/me')
            ->assertStatus(401);
    }
}
