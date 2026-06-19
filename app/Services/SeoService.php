<?php

namespace App\Services;

use App\Models\CompanyProfile;
use App\Models\Contact;
use App\Models\UmrahPackage;
use App\Support\SeoData;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SeoService
{
    private const PAGE_DEFAULTS = [
        'home' => [
            'route' => 'home',
            'label' => 'Beranda',
            'title' => 'PT Amara Al Medina Travel - Travel Umrah Terpercaya',
            'description' => 'Paket dan jadwal umrah bersama PT Amara Al Medina Travel dengan pelayanan profesional dan amanah.',
        ],
        'profile' => [
            'route' => 'profile',
            'label' => 'Profil',
            'title' => 'Profil - PT Amara Al Medina Travel',
            'description' => 'Profil, visi, misi, dan komitmen pelayanan PT Amara Al Medina Travel.',
        ],
        'packages' => [
            'route' => 'packages',
            'label' => 'Paket Umrah',
            'title' => 'Paket Umrah - PT Amara Al Medina Travel',
            'description' => 'Pilihan paket umrah dengan fasilitas, harga, dan jadwal keberangkatan yang jelas.',
        ],
        'schedules' => [
            'route' => 'schedules',
            'label' => 'Jadwal',
            'title' => 'Jadwal Keberangkatan - PT Amara Al Medina Travel',
            'description' => 'Jadwal keberangkatan dan ketersediaan kuota paket umrah PT Amara Al Medina Travel.',
        ],
        'galleries' => [
            'route' => 'galleries',
            'label' => 'Galeri',
            'title' => 'Galeri Kegiatan - PT Amara Al Medina Travel',
            'description' => 'Dokumentasi kegiatan jamaah bersama PT Amara Al Medina Travel.',
        ],
        'contact' => [
            'route' => 'contact',
            'label' => 'Kontak',
            'title' => 'Kontak - PT Amara Al Medina Travel',
            'description' => 'Alamat, WhatsApp, email, dan lokasi PT Amara Al Medina Travel.',
        ],
        'booking' => [
            'route' => 'bookings.create',
            'label' => 'Booking',
            'title' => 'Booking Umrah - PT Amara Al Medina Travel',
            'description' => 'Ajukan booking paket umrah PT Amara Al Medina Travel secara online.',
        ],
    ];

    /**
     * @param  array<string, mixed>  $context
     */
    public function forPage(string $page, array $context): SeoData
    {
        return match ($page) {
            'package' => $this->forPackage($context),
            'booking-package' => $this->forBookingPackage($context),
            'booking-detail' => $this->forBookingDetail($context),
            default => $this->forStaticPage($page, $context),
        };
    }

    public function routeUrl(string $route, mixed $parameters = []): string
    {
        $path = parse_url(route($route, $parameters, false), PHP_URL_PATH) ?: '/';
        $path = $path === '/' ? '/' : '/'.trim($path, '/');

        return rtrim((string) config('app.url'), '/').$path;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function forStaticPage(string $page, array $context): SeoData
    {
        $definition = self::PAGE_DEFAULTS[$page] ?? self::PAGE_DEFAULTS['home'];
        $settings = $this->settings($context);
        $siteName = $this->setting($settings, 'seo_site_name', 'PT Amara Al Medina Travel');
        $title = $this->setting($settings, "seo_{$page}_title", $definition['title']);
        $description = $this->setting($settings, "seo_{$page}_description", $definition['description']);
        $canonical = $this->routeUrl($definition['route']);
        $image = $this->pageImage($page, $settings);
        $graph = $this->baseGraph($context, $siteName);

        if ($page !== 'home') {
            $graph[] = $this->breadcrumb([
                ['name' => 'Beranda', 'url' => $this->routeUrl('home')],
                ['name' => $definition['label'], 'url' => $canonical],
            ]);
        }

        return $this->makeData(
            title: $title,
            description: $description,
            robots: 'index,follow',
            canonical: $canonical,
            image: $image,
            imageAlt: $title,
            type: 'website',
            siteName: $siteName,
            settings: $settings,
            graph: $graph,
        );
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function forPackage(array $context): SeoData
    {
        /** @var UmrahPackage $package */
        $package = $context['package'];
        $settings = $this->settings($context);
        $siteName = $this->setting($settings, 'seo_site_name', 'PT Amara Al Medina Travel');
        $canonical = $package->is_indexable ? $this->routeUrl('packages.show', $package) : null;
        $title = $this->clean($package->seo_title) ?: Str::limit($package->name.' - '.$siteName, 70, '');
        $description = $this->clean($package->seo_description)
            ?: Str::limit($this->clean($package->description) ?: $this->setting($settings, 'seo_default_description', self::PAGE_DEFAULTS['packages']['description']), 170, '');
        $image = $this->packageImage($package, $settings);
        $graph = [];

        if ($package->is_indexable && $canonical !== null) {
            $graph = $this->baseGraph($context, $siteName);
            $graph[] = $this->breadcrumb([
                ['name' => 'Beranda', 'url' => $this->routeUrl('home')],
                ['name' => 'Paket Umrah', 'url' => $this->routeUrl('packages')],
                ['name' => $package->name, 'url' => $canonical],
            ]);

            $schedules = collect($context['schedules'] ?? []);
            $available = $schedules->contains(fn ($schedule): bool => $schedule->departure_date->gte(today()) && $schedule->quota > 0);
            $graph[] = [
                '@type' => 'Product',
                '@id' => $canonical.'#product',
                'name' => $package->name,
                'description' => $description,
                'image' => [$image],
                'sku' => $package->slug,
                'category' => 'Paket Umrah',
                'brand' => ['@id' => $this->routeUrl('home').'#organization'],
                'offers' => [
                    '@type' => 'Offer',
                    'url' => $canonical,
                    'priceCurrency' => 'IDR',
                    'price' => (string) $package->price,
                    'availability' => 'https://schema.org/'.($available ? 'InStock' : 'OutOfStock'),
                    'seller' => ['@id' => $this->routeUrl('home').'#organization'],
                ],
            ];
        }

        return $this->makeData(
            title: $title,
            description: $description,
            robots: $package->is_indexable ? 'index,follow' : 'noindex,follow',
            canonical: $canonical,
            image: $image,
            imageAlt: $package->name,
            type: 'product',
            siteName: $siteName,
            settings: $settings,
            graph: $graph,
        );
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function forBookingPackage(array $context): SeoData
    {
        /** @var UmrahPackage $package */
        $package = $context['package'];
        $settings = $this->settings($context);
        $siteName = $this->setting($settings, 'seo_site_name', 'PT Amara Al Medina Travel');
        $title = Str::limit('Booking '.$package->name.' - '.$siteName, 70, '');

        return $this->makeData(
            title: $title,
            description: Str::limit('Ajukan booking untuk '.$package->name.' secara online.', 170, ''),
            robots: 'noindex,follow',
            canonical: null,
            image: $this->packageImage($package, $settings),
            imageAlt: $package->name,
            type: 'website',
            siteName: $siteName,
            settings: $settings,
            graph: [],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function forBookingDetail(array $context): SeoData
    {
        $settings = $this->settings($context);
        $siteName = $this->setting($settings, 'seo_site_name', 'PT Amara Al Medina Travel');

        return $this->makeData(
            title: 'Status Booking - '.$siteName,
            description: 'Halaman privat untuk melihat status booking umrah.',
            robots: 'noindex,nofollow,nosnippet',
            canonical: null,
            image: $this->pageImage('booking', $settings),
            imageAlt: $siteName,
            type: 'website',
            siteName: $siteName,
            settings: $settings,
            graph: [],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<int, array<string, mixed>>
     */
    private function baseGraph(array $context, string $siteName): array
    {
        /** @var CompanyProfile|null $profile */
        $profile = $context['profile'] ?? null;
        /** @var Contact|null $contact */
        $contact = $context['contact'] ?? null;
        $settings = $this->settings($context);
        $home = $this->routeUrl('home');
        $organizationId = $home.'#organization';
        $organization = [
            '@type' => 'TravelAgency',
            '@id' => $organizationId,
            'name' => $this->clean($profile?->company_name) ?: $siteName,
            'url' => $home,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $this->publicAsset($this->setting($settings, 'brand_logo_path', 'images/site/logo.png')),
            ],
            'description' => $this->clean($profile?->about),
            'email' => $this->clean($contact?->email),
            'telephone' => $this->internationalPhone($contact?->whatsapp),
            'address' => $contact?->address ? [
                '@type' => 'PostalAddress',
                'streetAddress' => $contact->address,
                'addressCountry' => 'ID',
            ] : null,
            'geo' => $contact && $contact->latitude !== null && $contact->longitude !== null ? [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $contact->latitude,
                'longitude' => (float) $contact->longitude,
            ] : null,
            'sameAs' => $this->instagramUrl($contact?->instagram) ? [$this->instagramUrl($contact?->instagram)] : null,
        ];

        return [
            $this->withoutEmpty($organization),
            [
                '@type' => 'WebSite',
                '@id' => $home.'#website',
                'url' => $home,
                'name' => $siteName,
                'publisher' => ['@id' => $organizationId],
                'inLanguage' => 'id-ID',
            ],
        ];
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    private function breadcrumb(array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)
                ->values()
                ->map(fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ])
                ->all(),
        ];
    }

    /**
     * @param  Collection<string, mixed>  $settings
     * @param  array<int, array<string, mixed>>  $graph
     */
    private function makeData(
        string $title,
        string $description,
        string $robots,
        ?string $canonical,
        string $image,
        string $imageAlt,
        string $type,
        string $siteName,
        Collection $settings,
        array $graph,
    ): SeoData {
        return new SeoData(
            title: Str::limit($this->clean($title), 70, ''),
            description: Str::limit($this->clean($description), 170, ''),
            robots: $robots,
            canonical: $canonical,
            image: $image,
            imageAlt: $this->clean($imageAlt),
            type: $type,
            siteName: $siteName,
            googleSiteVerification: $this->clean($settings->get('google_site_verification')) ?: null,
            structuredData: $graph === [] ? [] : [
                '@context' => 'https://schema.org',
                '@graph' => $graph,
            ],
        );
    }

    /** @param Collection<string, mixed> $settings */
    private function pageImage(string $page, Collection $settings): string
    {
        $path = $this->setting($settings, "seo_{$page}_image_path", '');
        $path = $path ?: $this->setting($settings, 'seo_default_image_path', '');
        $path = $path ?: $this->setting($settings, 'hero_image_path', 'images/site/beranda-img.jpg');

        return $this->publicAsset($path);
    }

    /** @param Collection<string, mixed> $settings */
    private function packageImage(UmrahPackage $package, Collection $settings): string
    {
        $path = $this->clean($package->seo_image_path) ?: $this->clean($package->image_path);

        return $path
            ? $this->publicAsset('storage/'.ltrim($path, '/'))
            : $this->pageImage('packages', $settings);
    }

    /** @return Collection<string, mixed> */
    private function settings(array $context): Collection
    {
        return collect($context['settings'] ?? []);
    }

    /** @param Collection<string, mixed> $settings */
    private function setting(Collection $settings, string $key, string $default): string
    {
        return $this->clean($settings->get($key)) ?: $default;
    }

    private function publicAsset(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    private function clean(mixed $value): string
    {
        return trim(strip_tags((string) $value));
    }

    private function internationalPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?: '';

        if ($digits === '') {
            return null;
        }

        return '+'.(str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits);
    }

    private function instagramUrl(?string $instagram): ?string
    {
        $instagram = $this->clean($instagram);

        if ($instagram === '') {
            return null;
        }

        if (Str::startsWith($instagram, ['http://', 'https://'])) {
            return $instagram;
        }

        return 'https://www.instagram.com/'.ltrim($instagram, '@').'/';
    }

    /** @return array<string, mixed> */
    private function withoutEmpty(array $values): array
    {
        return array_filter($values, fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
