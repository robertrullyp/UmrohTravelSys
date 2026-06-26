<?php

namespace Tests\Feature;

use App\Filament\Resources\Contacts\ContactResource;
use App\Filament\Resources\Galleries\GalleryResource;
use App\Filament\Resources\Schedules\ScheduleResource;
use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_login_uses_branded_copy(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Login Admin')
            ->assertSee('Email')
            ->assertSee('Masukkan email admin');
    }

    public function test_admin_can_open_filament_dashboard(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'is_admin' => true,
        ]);
        $admin->syncRoles([Role::findByName('super-admin')]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Grafik Pengunjung')
            ->assertSee(UmrahPackageResource::getUrl('index'), false)
            ->assertSee(ScheduleResource::getUrl('index'), false)
            ->assertSee(GalleryResource::getUrl('index'), false)
            ->assertSee(ContactResource::getUrl('index'), false);
    }

    public function test_admin_can_open_profile_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'is_admin' => true,
        ]);
        $admin->syncRoles([Role::findByName('super-admin')]);

        $this->actingAs($admin)
            ->get('/admin/profile')
            ->assertOk()
            ->assertSee('Akun Saya')
            ->assertSee('Foto Profil')
            ->assertSee('Nomor Telepon')
            ->assertSee('Kata Sandi Baru')
            ->assertSee('Konfirmasi Kata Sandi Baru')
            ->assertSee('Mode Terang')
            ->assertSee('Keluar')
            ->assertDontSee('admin-topbar-theme-switcher');
    }

    public function test_user_avatar_uses_public_storage_url(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'avatar_path' => 'avatars/admin.jpg',
            'is_admin' => true,
        ]);
        $admin->syncRoles([Role::findByName('super-admin')]);

        $this->assertStringContainsString('/storage/avatars/admin.jpg', $admin->getFilamentAvatarUrl());
    }
}
