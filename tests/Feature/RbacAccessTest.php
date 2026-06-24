<?php

namespace Tests\Feature;

use App\Filament\Resources\VisitorLogs\VisitorLogResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
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
        $this->actingAs($superAdmin)->get('/admin/settings/users')->assertOk()->assertSee('Pengguna');
        $this->actingAs($superAdmin)->get('/admin/settings/roles')->assertOk()->assertSee('Role / Hak Akses');
        $this->actingAs($superAdmin)->get('/admin/settings/logs')->assertOk()->assertSee('Log');
        $this->actingAs($superAdmin)->get('/admin/settings/permissions')->assertForbidden();
        $this->actingAs($superAdmin)->get('/admin/users')->assertRedirect('/admin/settings/users');
    }

    public function test_log_menu_access_is_controlled_by_role_permission(): void
    {
        $this->assertDatabaseHas('permissions', ['name' => 'logs.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'logs.delete']);
        $this->assertTrue(Role::findByName('super-admin')->hasPermissionTo('logs.delete'));
        $this->assertTrue(Role::findByName('admin')->hasPermissionTo('logs.view'));
        $this->assertFalse(Role::findByName('admin')->hasPermissionTo('logs.delete'));

        $viewer = User::query()->create([
            'name' => 'Log Viewer',
            'email' => 'log-viewer@example.test',
            'password' => 'password',
        ]);
        $viewerRole = Role::query()->create(['name' => 'log-viewer', 'guard_name' => 'web']);
        $viewerRole->givePermissionTo(['panel.access', 'logs.view']);
        $viewer->assignRole($viewerRole);

        $blocked = User::query()->create([
            'name' => 'No Log Viewer',
            'email' => 'no-log-viewer@example.test',
            'password' => 'password',
        ]);
        $blockedRole = Role::query()->create(['name' => 'no-log-viewer', 'guard_name' => 'web']);
        $blockedRole->givePermissionTo(['panel.access', 'settings.view']);
        $blocked->assignRole($blockedRole);

        $this->actingAs($viewer)
            ->get(VisitorLogResource::getUrl())
            ->assertOk()
            ->assertSee('Log');

        $this->assertTrue(VisitorLogResource::canViewAny());

        $this->actingAs($blocked)
            ->get(VisitorLogResource::getUrl())
            ->assertRedirect();

        $this->assertFalse(VisitorLogResource::canViewAny());

        $this->actingAs($blocked)
            ->get('/admin/settings/website')
            ->assertOk()
            ->assertDontSee('/admin/settings/logs', false);
    }
}
