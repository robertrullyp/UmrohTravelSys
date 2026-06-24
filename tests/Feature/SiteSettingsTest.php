<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_website_settings_page_shows_structured_controls(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/settings/website')
            ->assertOk()
            ->assertSee('Pengaturan Website')
            ->assertSee('Logo &amp; Ikon', false)
            ->assertSee('Logo Brand')
            ->assertSee('Favicon')
            ->assertSee('Gambar Hero Beranda')
            ->assertSee('Highlight Judul')
            ->assertSee('Nomor WhatsApp CTA')
            ->assertSee('SEO Default')
            ->assertSee('Judul Google Default')
            ->assertSee('Token Verifikasi Google')
            ->assertSee('SEO Halaman - Beranda')
            ->assertSee('SEO Halaman - Booking');
    }

    public function test_legacy_settings_routes_redirect_to_settings_cluster(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        $setting = SiteSetting::query()->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/site-settings')
            ->assertRedirect('/admin/settings/website');

        $this->actingAs($admin)
            ->get('/admin/site-settings/create')
            ->assertRedirect('/admin/settings/website');

        $this->actingAs($admin)
            ->get("/admin/site-settings/{$setting->getRouteKey()}/edit")
            ->assertRedirect('/admin/settings/website');
    }

    public function test_public_pages_use_site_setting_assets(): void
    {
        SiteSetting::query()->updateOrCreate(['key' => 'brand_logo_path'], ['value' => 'images/site/uploads/test-logo.png']);
        SiteSetting::query()->updateOrCreate(['key' => 'favicon_path'], ['value' => 'images/site/uploads/test-favicon.png']);
        SiteSetting::query()->updateOrCreate(['key' => 'hero_image_path'], ['value' => 'images/site/uploads/test-hero.jpg']);

        $this->get('/')
            ->assertOk()
            ->assertSee('images/site/uploads/test-logo.png')
            ->assertSee('images/site/uploads/test-favicon.png')
            ->assertSee('images/site/uploads/test-hero.jpg');
    }

    public function test_site_setting_definitions_cover_public_runtime_keys(): void
    {
        foreach ([
            'brand_logo_path',
            'favicon_path',
            'hero_image_path',
            'hero_title_highlight',
            'hero_title',
            'hero_subtitle',
            'cta_whatsapp',
            'seo_site_name',
            'seo_default_title',
            'seo_default_description',
            'seo_default_image_path',
            'google_site_verification',
        ] as $key) {
            $this->assertTrue(SiteSetting::isSystemKey($key));
            $this->assertDatabaseHas('site_settings', ['key' => $key]);
        }

        foreach (array_keys(SiteSetting::SEO_PAGES) as $page) {
            foreach (['title', 'description', 'image_path'] as $field) {
                $key = "seo_{$page}_{$field}";

                $this->assertTrue(SiteSetting::isSystemKey($key));
                $this->assertDatabaseHas('site_settings', ['key' => $key]);
            }
        }
    }
}
