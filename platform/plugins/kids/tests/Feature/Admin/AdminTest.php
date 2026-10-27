<?php

namespace Botble\Kids\Tests\Feature\Admin;

require_once dirname(__DIR__, 2).'/KidsTestCase.php';

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Kids\Models\KidsTopic;
use Botble\Kids\Tests\KidsTestCase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Cache;

class AdminTest extends KidsTestCase
{
    private User $superUser;

    private User $limitedUser;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->superUser = new User;
        $this->superUser->forceFill([
            'first_name' => 'Admin',
            'last_name' => 'Super',
            'email' => 'admin-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'super_user' => 1,
        ]);
        $this->superUser->save();
        app(ActivateUserService::class)->activate($this->superUser);

        $this->limitedUser = new User;
        $this->limitedUser->forceFill([
            'first_name' => 'Limited',
            'last_name' => 'User',
            'email' => 'limited-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'super_user' => 0,
            'permissions' => [],
        ]);
        $this->limitedUser->save();
        app(ActivateUserService::class)->activate($this->limitedUser);
    }

    public function test_admin_index_pages_return_200(): void
    {
        $this->actingAs($this->superUser)
            ->get(route('kids.topics.index'))
            ->assertStatus(200);

        $this->actingAs($this->superUser)
            ->get(route('kids.word-templates.index'))
            ->assertStatus(200);

        $this->actingAs($this->superUser)
            ->get(route('kids.parents.index'))
            ->assertStatus(200);

        $this->actingAs($this->superUser)
            ->get(route('kids.reports.index'))
            ->assertStatus(200);
    }

    public function test_create_topic_with_broken_generator_config_fails_validation(): void
    {
        // Broken JSON
        $res1 = $this->actingAs($this->superUser)->post(route('kids.topics.store'), [
            'name' => 'Broken Topic 1',
            'code' => 'g2.broken1',
            'grade' => 2,
            'generator' => 'arithmetic',
            'generator_config' => '{broken_json',
            'status' => 'draft',
        ]);
        $res1->assertSessionHasErrors('generator_config');

        // Generator exception: missing level 2..5 config
        $res2 = $this->actingAs($this->superUser)->post(route('kids.topics.store'), [
            'name' => 'Broken Topic 2',
            'code' => 'g2.broken2',
            'grade' => 2,
            'generator' => 'arithmetic',
            'generator_config' => json_encode(['levels' => ['1' => ['ops' => ['+'], 'a' => [1, 9], 'b' => [1, 9]]]]),
            'status' => 'draft',
        ]);
        $res2->assertSessionHasErrors('generator_config');
    }

    public function test_topic_preview_renders_one_hundred_questions(): void
    {
        $levelsConfig = [];
        for ($i = 1; $i <= 5; $i++) {
            $levelsConfig[(string) $i] = [
                'ops' => ['+'],
                'a' => [1, 10 * $i],
                'b' => [1, 10 * $i],
                'carry' => false,
                'result_max' => 100,
            ];
        }

        $topic = KidsTopic::query()->create([
            'name' => 'Phép cộng có nhớ',
            'code' => 'g2.add_carry_preview',
            'grade' => 2,
            'generator' => 'arithmetic',
            'generator_config' => ['levels' => $levelsConfig],
            'status' => TopicStatusEnum::APPROVED,
        ]);

        $res = $this->actingAs($this->superUser)->get(route('kids.topics.preview', $topic));

        $res->assertStatus(200)
            ->assertViewHas('totalQuestions', 100)
            ->assertSee('preview-question-row');
    }

    public function test_create_parent_in_admin_and_login_via_api(): void
    {
        $res = $this->actingAs($this->superUser)->post(route('kids.parents.store'), [
            'name' => 'Phụ huynh Admin',
            'username' => 'adminparent',
            'password' => 'secret1234',
            'status' => 'active',
        ]);

        $res->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kids_parents', ['username' => 'adminparent']);

        $loginRes = $this->postJson('/api/v1/kids/auth/login', [
            'username' => 'adminparent',
            'password' => 'secret1234',
            'device_name' => 'iPad Super Admin',
        ]);

        $loginRes->assertStatus(200)
            ->assertJsonPath('error', false)
            ->assertJsonPath('data.parent.username', 'adminparent');
    }

    public function test_admin_user_without_permission_returns_403(): void
    {
        $res = $this->actingAs($this->limitedUser)->get(route('kids.topics.index'));

        $res->assertStatus(403);
    }
}
