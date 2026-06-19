<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\SiteSetting;
use App\Models\UmrahPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SeoSupportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'https://lulu.kapul.my.id']);
        $this->seed();
    }

    public function test_home_renders_complete_metadata_and_organization_graph(): void
    {
        SiteSetting::query()->updateOrCreate(
            ['key' => 'google_site_verification'],
            ['value' => 'verification-token'],
        );

        $response = $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertSee('<meta name="robots" content="index,follow">', false)
            ->assertSee('<link rel="canonical" href="https://lulu.kapul.my.id/">', false)
            ->assertSee('<meta name="google-site-verification" content="verification-token">', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);

        $graph = collect($this->jsonLd($response->getContent())['@graph']);

        $this->assertTrue($graph->contains('@type', 'TravelAgency'));
        $this->assertTrue($graph->contains('@type', 'WebSite'));
    }

    public function test_page_metadata_override_is_sanitized_and_canonical_ignores_query(): void
    {
        SiteSetting::query()->updateOrCreate(['key' => 'seo_profile_title'], ['value' => '<b>Profil Resmi Travel</b>']);
        SiteSetting::query()->updateOrCreate(['key' => 'seo_profile_description'], ['value' => '<script>alert(1)</script>Informasi perusahaan resmi.']);

        $this->get('/profil?utm_source=test')
            ->assertOk()
            ->assertSee('<title>Profil Resmi Travel</title>', false)
            ->assertSee('content="alert(1)Informasi perusahaan resmi."', false)
            ->assertSee('<link rel="canonical" href="https://lulu.kapul.my.id/profil">', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_package_metadata_product_graph_and_indexability_are_consistent(): void
    {
        $package = UmrahPackage::query()->firstOrFail();
        $package->update([
            'seo_title' => '<b>Paket Umrah Pilihan</b>',
            'seo_description' => '<i>Paket dengan fasilitas lengkap.</i>',
            'seo_image_path' => 'packages/seo/share.jpg',
        ]);

        $response = $this->get(route('packages.show', $package))
            ->assertOk()
            ->assertSee('<title>Paket Umrah Pilihan</title>', false)
            ->assertSee('<link rel="canonical" href="https://lulu.kapul.my.id/paket-umrah/'.$package->slug.'">', false)
            ->assertSee('https://lulu.kapul.my.id/storage/packages/seo/share.jpg', false)
            ->assertDontSee('<b>', false);

        $graph = collect($this->jsonLd($response->getContent())['@graph']);
        $product = $graph->firstWhere('@type', 'Product');

        $this->assertNotNull($product);
        $this->assertSame('IDR', data_get($product, 'offers.priceCurrency'));
        $this->assertContains(data_get($product, 'offers.availability'), [
            'https://schema.org/InStock',
            'https://schema.org/OutOfStock',
        ]);

        $package->update(['is_indexable' => false]);

        $this->get(route('packages.show', $package))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertDontSee('rel="canonical"', false)
            ->assertDontSee('application/ld+json', false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee('/paket-umrah/'.$package->slug, false);
    }

    public function test_sitemap_contains_only_indexable_public_urls(): void
    {
        $indexable = UmrahPackage::query()->where('is_active', true)->firstOrFail();
        $excluded = UmrahPackage::query()->whereKeyNot($indexable->getKey())->firstOrFail();
        $excluded->update(['is_indexable' => false]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertCookieMissing('XSRF-TOKEN')
            ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
            ->assertSee('https://lulu.kapul.my.id/booking', false)
            ->assertSee('/paket-umrah/'.$indexable->slug, false)
            ->assertDontSee('/paket-umrah/'.$excluded->slug, false)
            ->assertDontSee('/booking/paket/', false)
            ->assertDontSee('/admin', false)
            ->assertSee('<lastmod>', false);
    }

    public function test_booking_and_admin_routes_send_expected_indexing_policy(): void
    {
        $package = UmrahPackage::query()->firstOrFail();

        $this->get('/booking')
            ->assertOk()
            ->assertSee('<meta name="robots" content="index,follow">', false)
            ->assertSee('rel="canonical"', false);

        $this->get(route('bookings.package', $package))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, follow')
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertDontSee('rel="canonical"', false);

        $booking = $this->makeBooking(Schedule::query()->firstOrFail());

        $this->get(route('bookings.show', $booking->public_token))
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, nosnippet')
            ->assertSee('<meta name="robots" content="noindex,nofollow,nosnippet">', false)
            ->assertSee('<title>Status Booking - PT Amara Al Medina Travel</title>', false)
            ->assertDontSee('<title>Status Booking '.$booking->booking_number, false);

        $this->get('/admin/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, nosnippet')
            ->assertSee('<meta name="robots" content="noindex,nofollow,nosnippet">', false);
    }

    public function test_production_redirect_normalizes_origin_and_trailing_slash(): void
    {
        config([
            'app.env' => 'production',
            'app.url' => 'https://lulu.kapul.my.id',
            'seo.canonical_redirect' => true,
            'seo.force_canonical_redirect' => true,
        ]);

        $this->get('http://lulu.kapul.my.id/profil/?utm_source=test')
            ->assertStatus(301)
            ->assertRedirect('https://lulu.kapul.my.id/profil?utm_source=test');
    }

    /** @return array<string, mixed> */
    private function jsonLd(string $html): array
    {
        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

        $this->assertArrayHasKey(1, $matches, 'JSON-LD script was not rendered.');

        return json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
    }

    private function makeBooking(Schedule $schedule): Booking
    {
        return Booking::query()->create([
            'booking_number' => 'AMA-SEO-TEST',
            'public_token' => Str::random(48),
            'umrah_package_id' => $schedule->umrah_package_id,
            'schedule_id' => $schedule->id,
            'customer_name' => 'SEO Test',
            'whatsapp' => '081234567890',
            'pilgrims_count' => 1,
            'status' => Booking::STATUS_PENDING,
        ]);
    }
}
