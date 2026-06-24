<?php

namespace Tests\Feature;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\CompanyProfiles\CompanyProfileResource;
use App\Filament\Resources\Permissions\PermissionResource;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\UmrahPackages\Pages\EditUmrahPackage;
use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\Booking;
use App\Models\CompanyProfile;
use App\Models\Gallery;
use App\Models\Schedule;
use App\Models\UmrahPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminResourcePolishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_umrah_package_edit_route_uses_seed_slug_not_numeric_id(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $package = UmrahPackage::query()->orderBy('id')->firstOrFail();

        $editUrl = UmrahPackageResource::getUrl('edit', ['record' => $package]);

        $this->assertSame('slug', UmrahPackageResource::getRecordRouteKeyName());
        $this->assertStringContainsString("/admin/umrah-packages/{$package->slug}/edit", $editUrl);
        $this->assertStringNotContainsString("/admin/umrah-packages/{$package->id}/edit", $editUrl);

        $this->actingAs($admin)
            ->get($editUrl)
            ->assertOk()
            ->assertSee($package->name);

        if ((string) $package->id !== $package->slug) {
            $this->actingAs($admin)
                ->get("/admin/umrah-packages/{$package->id}/edit")
                ->assertNotFound();
        }
    }

    public function test_company_profile_admin_is_singleton_edit_form(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $profile = CompanyProfile::query()->findOrFail(1);

        $this->assertFalse(CompanyProfileResource::canCreate());
        $this->assertFalse(CompanyProfileResource::canDelete($profile));
        $this->assertFalse(CompanyProfileResource::canDeleteAny());

        $this->actingAs($admin)
            ->get('/admin/company-profiles')
            ->assertOk()
            ->assertSee('Profil Perusahaan')
            ->assertSee('Simpan Profil')
            ->assertSee($profile->company_name)
            ->assertDontSee('Tambah Profil')
            ->assertDontSee('Hapus terpilih');
    }

    public function test_legacy_company_profile_routes_redirect_to_singleton_page(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/company-profiles/create')
            ->assertRedirect('/admin/company-profiles');

        $this->actingAs($admin)
            ->get('/admin/company-profiles/1/edit')
            ->assertRedirect('/admin/company-profiles');
    }

    public function test_admin_image_forms_explain_public_output_context(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $package = UmrahPackage::query()->orderBy('id')->firstOrFail();
        $gallery = Gallery::query()->orderBy('id')->firstOrFail();

        $this->actingAs($admin)
            ->get(UmrahPackageResource::getUrl('edit', ['record' => $package]))
            ->assertOk()
            ->assertSee('Muncul di kartu beranda, daftar paket, dan detail paket.')
            ->assertSee('Poster/flyer akan tampil utuh di detail paket')
            ->assertSee('Khusus preview link WhatsApp/media sosial ukuran 1200x630.');

        $this->actingAs($admin)
            ->get("/admin/galleries/{$gallery->id}/edit")
            ->assertOk()
            ->assertSee('Tampil di halaman Galeri.')
            ->assertSee('Thumbnail publik memakai rasio 4:3');

        $this->actingAs($admin)
            ->get('/admin/company-profiles')
            ->assertOk()
            ->assertSee('Tampil di halaman Profil publik.');

        $this->actingAs($admin)
            ->get('/admin/settings/website')
            ->assertOk()
            ->assertSee('Tampil di header/footer publik')
            ->assertSee('Tampil sebagai gambar besar di bagian atas beranda.')
            ->assertSee('Tidak mengganti gambar hero atau gambar paket.');
    }

    public function test_settings_submenu_forms_use_clear_admin_copy(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();

        $this->assertFalse(PermissionResource::shouldRegisterNavigation());
        $this->assertFalse(PermissionResource::canViewAny());

        $this->actingAs($admin)
            ->get(PermissionResource::getUrl())
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(UserResource::getUrl('create'))
            ->assertOk()
            ->assertSee('Data Pengguna Admin')
            ->assertSee('Role / Hak Akses')
            ->assertSee('Saat edit, kosongkan jika tidak ingin mengganti kata sandi.');

        $this->actingAs($admin)
            ->get(RoleResource::getUrl('create'))
            ->assertOk()
            ->assertSee('Role / Hak Akses')
            ->assertSee('Role adalah paket hak akses')
            ->assertSee('Akses Panel')
            ->assertSee('Masuk panel admin')
            ->assertSee('Booking')
            ->assertSee('Setujui booking');

        $this->actingAs($admin)
            ->get('/admin/settings/website')
            ->assertOk()
            ->assertDontSee('Permission Teknis');
    }

    public function test_user_edit_password_fields_start_blank_and_disable_saved_password_autofill(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $role = Role::findByName('admin');
        $user = User::query()->create([
            'name' => 'Password Check Admin',
            'email' => 'password-check-admin@example.test',
            'password' => 'password',
        ]);
        $user->assignRole($role);

        $response = $this->actingAs($admin)
            ->get(UserResource::getUrl('edit', ['record' => $user]));

        $response->assertOk()
            ->assertSee('Saat edit, kosongkan jika tidak ingin mengganti kata sandi.')
            ->assertSee('autocomplete="new-password"', false)
            ->assertDontSee($user->password, false);

        $this->assertGreaterThanOrEqual(
            2,
            substr_count($response->getContent(), 'autocomplete="new-password"'),
        );
    }

    public function test_edit_pages_redirect_to_view_or_index_after_successful_save(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $schedule = Schedule::query()->where('quota', '>', 1)->firstOrFail();
        $booking = Booking::query()->create([
            'booking_number' => 'TEST-'.fake()->unique()->numerify('######'),
            'public_token' => fake()->unique()->sha256(),
            'umrah_package_id' => $schedule->umrah_package_id,
            'schedule_id' => $schedule->id,
            'customer_name' => 'Ahmad Fauzi',
            'whatsapp' => '082252239507',
            'pilgrims_count' => 1,
            'status' => Booking::STATUS_PENDING,
        ]);
        $package = UmrahPackage::query()->orderBy('id')->firstOrFail();
        $role = Role::findByName('admin');
        $user = User::query()->create([
            'name' => 'Operational Admin',
            'email' => 'operational-admin@example.test',
            'password' => 'password',
        ]);
        $user->assignRole($role);
        $currentPasswordHash = $user->password;

        $this->actingAs($admin);

        Livewire::test(EditBooking::class, ['record' => $booking->getKey()])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(BookingResource::getUrl('view', ['record' => $booking]));

        Livewire::test(EditUmrahPackage::class, ['record' => $package->getRouteKey()])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(UmrahPackageResource::getUrl());

        Livewire::test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm([
                'name' => 'Operational Admin',
                'email' => 'operational-admin@example.test',
                'password' => '',
                'password_confirmation' => '',
                'roles' => [(string) $role->getKey()],
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(UserResource::getUrl());

        $this->assertSame($currentPasswordHash, $user->refresh()->password);
    }

    public function test_role_permission_checklists_save_multiple_module_groups(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $role = Role::query()->create(['name' => 'limited-admin', 'guard_name' => 'web']);
        $permissions = Permission::query()
            ->whereIn('name', [
                'panel.access',
                'bookings.view',
                'bookings.approve',
                'packages.view',
                'settings.view',
                'logs.view',
                'logs.delete',
            ])
            ->pluck('id', 'name')
            ->map(fn (int|string $id): string => (string) $id);

        $this->actingAs($admin);

        Livewire::test(EditRole::class, ['record' => $role->getKey()])
            ->fillForm([
                'name' => 'limited-admin',
                'permissions_akses_panel' => [$permissions['panel.access']],
                'permissions_booking' => [
                    $permissions['bookings.view'],
                    $permissions['bookings.approve'],
                ],
                'permissions_paket_umrah' => [$permissions['packages.view']],
                'permissions_pengaturan_website' => [$permissions['settings.view']],
                'permissions_log' => [
                    $permissions['logs.view'],
                    $permissions['logs.delete'],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(RoleResource::getUrl());

        $role->refresh();

        $this->assertTrue($role->hasPermissionTo('panel.access'));
        $this->assertTrue($role->hasPermissionTo('bookings.view'));
        $this->assertTrue($role->hasPermissionTo('bookings.approve'));
        $this->assertTrue($role->hasPermissionTo('packages.view'));
        $this->assertTrue($role->hasPermissionTo('settings.view'));
        $this->assertTrue($role->hasPermissionTo('logs.view'));
        $this->assertTrue($role->hasPermissionTo('logs.delete'));
        $this->assertFalse($role->hasPermissionTo('permissions.view'));
    }
}
