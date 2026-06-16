<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_user_without_panel_access_cannot_open_admin_panel(): void
    {
        $user = User::query()->create([
            'name' => 'No Access',
            'email' => 'no-access@example.test',
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_operational_admin_cannot_open_account_management(): void
    {
        $admin = User::query()->create([
            'name' => 'Operational Admin',
            'email' => 'ops@example.test',
            'password' => 'password',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/settings/users')->assertForbidden();
        $this->actingAs($admin)->get('/admin/settings/roles')->assertForbidden();
        $this->actingAs($admin)->get('/admin/settings/permissions')->assertForbidden();
    }

    public function test_initial_admin_is_super_admin_and_can_open_account_management(): void
    {
        $superAdmin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();

        $this->assertTrue($superAdmin->hasRole('super-admin'));

        $this->actingAs($superAdmin)->get('/admin/settings')->assertRedirect('/admin/settings/website');
        $this->actingAs($superAdmin)->get('/admin/settings/users')->assertOk()->assertSee('Users');
        $this->actingAs($superAdmin)->get('/admin/settings/roles')->assertOk()->assertSee('Roles');
        $this->actingAs($superAdmin)->get('/admin/settings/permissions')->assertOk()->assertSee('Permissions');
        $this->actingAs($superAdmin)->get('/admin/users')->assertRedirect('/admin/settings/users');
    }
}
