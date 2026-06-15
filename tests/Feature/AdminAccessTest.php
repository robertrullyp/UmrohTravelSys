<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('Username')
            ->assertSee('Masukkan username');
    }

    public function test_admin_can_open_filament_dashboard(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Grafik Pengunjung');
    }

    public function test_admin_can_open_profile_page(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get('/admin/profile')
            ->assertOk()
            ->assertSee('My Account')
            ->assertSee('Foto Avatar')
            ->assertSee('Kata sandi baru')
            ->assertSee('Konfirmasi Kata sandi baru')
            ->assertSee('Mode Terang')
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

        $this->assertStringContainsString('/storage/avatars/admin.jpg', $admin->getFilamentAvatarUrl());
    }
}
