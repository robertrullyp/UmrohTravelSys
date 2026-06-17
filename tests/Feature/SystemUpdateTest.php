<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SystemUpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
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
            ->assertSee('Ringkasan Sistem')
            ->assertSee('Versi aplikasi')
            ->assertSee('Rilis terakhir')
            ->assertSee('Status update')
            ->assertSee('Catatan Rilis')
            ->assertSee('Input Token FAT')
            ->assertSee(config('admin.version'))
            ->assertSee('Menambahkan sistem booking publik')
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

    public function test_github_fat_is_stored_encrypted_for_system_update(): void
    {
        $token = 'github_pat_' . str_repeat('a', 48);
        $service = app(SystemUpdateService::class);

        $service->storeGitHubToken($token);

        $setting = SiteSetting::query()
            ->where('key', 'update_github_fat')
            ->firstOrFail();

        $this->assertNotSame($token, $setting->value);
        $this->assertSame($token, Crypt::decryptString($setting->value));
        $this->assertTrue($service->githubTokenInfo()['configured']);

        $service->forgetGitHubToken();

        $this->assertDatabaseMissing('site_settings', ['key' => 'update_github_fat']);
    }

    public function test_latest_release_notes_are_loaded_from_changelog(): void
    {
        $notes = app(SystemUpdateService::class)->latestReleaseNotes();

        $this->assertSame('v2026.06.16', $notes['version']);
        $this->assertSame('2026-06-16', $notes['date']);
        $this->assertContains('Menambahkan sistem booking publik lengkap dengan cek status booking dan review admin.', $notes['notes']);
    }
}
