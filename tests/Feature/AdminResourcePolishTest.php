<?php

namespace Tests\Feature;

use App\Filament\Resources\CompanyProfiles\CompanyProfileResource;
use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use App\Models\CompanyProfile;
use App\Models\UmrahPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
