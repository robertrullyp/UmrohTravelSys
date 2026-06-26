<?php

namespace Tests\Feature;

use App\Filament\Clusters\Settings\Pages\WebsiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use RuntimeException;
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
            ->assertSee('SEO Halaman - Booking')
            ->assertSee('WhatsApp Gateway &amp; OTP Admin', false)
            ->assertSee('Test Kirim WhatsApp')
            ->assertSee('Aktifkan Gateway WhatsApp')
            ->assertSee('Wajibkan OTP Login Admin')
            ->assertSee('Masa Berlaku OTP Tindak Lanjut');
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
            'wa_gateway_enabled',
            'wa_gateway_post_url',
            'wa_gateway_auth_mode',
            'wa_gateway_basic_username',
            'wa_gateway_basic_password',
            'wa_gateway_header_name',
            'wa_gateway_header_value',
            'wa_gateway_bearer_token',
            'admin_otp_enabled',
            'admin_otp_expires_minutes',
            'admin_otp_resend_interval_seconds',
            'booking_followup_link_expires_minutes',
            'booking_followup_otp_expires_minutes',
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

    public function test_whatsapp_gateway_secret_settings_are_not_rendered_plainly(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        SiteSetting::setEncryptedValue('wa_gateway_post_url', 'https://gateway.example/ext/super-secret/wa');
        SiteSetting::setEncryptedValue('wa_gateway_bearer_token', 'secret-token-value');

        $this->actingAs($admin)
            ->get('/admin/settings/website')
            ->assertOk()
            ->assertSee('URL gateway sudah tersimpan')
            ->assertSee('Token sudah tersimpan')
            ->assertDontSee('super-secret')
            ->assertDontSee('secret-token-value');

        $this->assertSame('https://gateway.example/ext/super-secret/wa', SiteSetting::getEncryptedValue('wa_gateway_post_url'));
    }

    public function test_admin_can_send_whatsapp_gateway_test_message(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        SiteSetting::query()->updateOrCreate(['key' => 'wa_gateway_enabled'], ['value' => '1']);
        SiteSetting::query()->updateOrCreate(['key' => 'wa_gateway_auth_mode'], ['value' => 'none']);
        SiteSetting::setEncryptedValue('wa_gateway_post_url', 'https://gateway.example/ext/secret/wa');

        Http::fake([
            'https://gateway.example/ext/secret/wa' => Http::response(['ok' => true, 'id' => 'test-message']),
        ]);

        $this->actingAs($admin);

        Livewire::test(WebsiteSettings::class)
            ->call('sendWhatsAppGatewayTest', ['test_whatsapp' => '0822-5223-9507'])
            ->assertHasNoErrors();

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://gateway.example/ext/secret/wa'
                && $request['action'] === 'send'
                && $request['to'] === '6282252239507'
                && str_contains((string) $request['text'], 'Tes WhatsApp Gateway PT Amara Al Medina Travel berhasil dikirim')
                && $request->hasHeader('Idempotency-Key');
        });
    }

    public function test_whatsapp_gateway_test_does_not_send_when_gateway_is_disabled(): void
    {
        $admin = User::query()->where('email', config('admin.initial_email'))->firstOrFail();
        SiteSetting::query()->updateOrCreate(['key' => 'wa_gateway_enabled'], ['value' => '0']);
        SiteSetting::setEncryptedValue('wa_gateway_post_url', 'https://gateway.example/ext/secret/wa');

        Http::fake();

        $this->actingAs($admin);

        Livewire::test(WebsiteSettings::class)
            ->call('sendWhatsAppGatewayTest', ['test_whatsapp' => '082252239507'])
            ->assertHasNoErrors();

        Http::assertNothingSent();
    }

    public function test_whatsapp_gateway_test_error_message_hides_raw_connection_details(): void
    {
        $component = new WebsiteSettings;
        $method = new \ReflectionMethod($component, 'whatsAppGatewayTestErrorMessage');

        $message = $method->invoke(
            $component,
            new RuntimeException('cURL error for https://gateway.example/ext/super-secret/wa with token secret-token'),
        );

        $this->assertSame('Koneksi gateway gagal. Periksa status gateway, URL, dan auth yang tersimpan.', $message);
        $this->assertStringNotContainsString('super-secret', $message);
        $this->assertStringNotContainsString('secret-token', $message);
    }
}
