<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_system_update_page_is_available_to_super_admin(): void
    {
        $superAdmin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();

        $this->assertDatabaseHas('permissions', ['name' => 'updates.view']);
        $this->assertDatabaseHas('permissions', ['name' => 'updates.run']);

        $this->actingAs($superAdmin)
            ->get('/admin/settings/system-update')
            ->assertOk()
            ->assertSee('System Update')
            ->assertSee(config('admin.version'))
            ->assertSee('https://github.com/robertrullyp/UmrohTravelSys.git');
    }

    public function test_operational_admin_cannot_open_system_update(): void
    {
        $admin = User::query()->create([
            'name' => 'Operational Admin',
            'email' => 'ops-update@example.test',
            'password' => 'password',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get('/admin/settings/system-update')
            ->assertForbidden();
    }
}
