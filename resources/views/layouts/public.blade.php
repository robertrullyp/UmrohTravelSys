@php
    $logoUrl = asset($settings->get('brand_logo_path', 'images/site/logo.png') ?: 'images/site/logo.png');
    $faviconUrl = asset($settings->get('favicon_path', 'images/site/logo.png') ?: 'images/site/logo.png');
    $whatsappRaw = $contact?->whatsapp ?? $settings->get('cta_whatsapp', '');
    $whatsapp = preg_replace('/\D+/', '', $whatsappRaw);
    $whatsapp = str_starts_with($whatsapp, '0') ? '62' . substr($whatsapp, 1) : $whatsapp;
    $phoneRaw = $contact?->whatsapp ?? '';
    $phoneDigits = preg_replace('/\D+/', '', $phoneRaw);
    $phoneInternational = str_starts_with($phoneDigits, '0') ? '62' . substr($phoneDigits, 1) : $phoneDigits;
    $telUrl = $phoneInternational ? 'tel:+' . $phoneInternational : null;
    $mailUrl = $contact?->email ? 'mailto:' . $contact->email : null;
    $hasCoordinates = $contact && $contact->latitude !== null && $contact->longitude !== null;
    $mapQuery = $hasCoordinates
        ? $contact->latitude . ',' . $contact->longitude
        : ($contact?->address ?? null);
    $mapUrl = $mapQuery
        ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapQuery)
        : ($contact?->map_embed_url ?? null);
    $navItems = [
        ['label' => 'Beranda', 'route' => 'home'],
        ['label' => 'Profil', 'route' => 'profile'],
        ['label' => 'Paket Umrah', 'route' => 'packages'],
        ['label' => 'Jadwal', 'route' => 'schedules'],
        ['label' => 'Booking', 'route' => 'bookings.create'],
        ['label' => 'Galeri', 'route' => 'galleries'],
        ['label' => 'Kontak', 'route' => 'contact'],
    ];
    $pageTitle = isset($seo) ? $seo->title : trim($__env->yieldContent('title', 'PT Amara Al Medina Travel'));
    $pageDescription = isset($seo)
        ? $seo->description
        : trim($__env->yieldContent('description', 'Informasi paket umrah, jadwal keberangkatan, galeri, profil, dan kontak PT Amara Al Medina Travel.'));
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if (isset($seo))
        <meta name="robots" content="{{ $seo->robots }}">
        @if ($seo->canonical)
            <link rel="canonical" href="{{ $seo->canonical }}">
        @endif
        @if ($seo->googleSiteVerification)
            <meta name="google-site-verification" content="{{ $seo->googleSiteVerification }}">
        @endif
        <meta property="og:locale" content="id_ID">
        <meta property="og:type" content="{{ $seo->type }}">
        <meta property="og:site_name" content="{{ $seo->siteName }}">
        <meta property="og:title" content="{{ $seo->title }}">
        <meta property="og:description" content="{{ $seo->description }}">
        @if ($seo->canonical)
            <meta property="og:url" content="{{ $seo->canonical }}">
        @endif
        <meta property="og:image" content="{{ $seo->image }}">
        <meta property="og:image:alt" content="{{ $seo->imageAlt }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seo->title }}">
        <meta name="twitter:description" content="{{ $seo->description }}">
        <meta name="twitter:image" content="{{ $seo->image }}">
        <meta name="twitter:image:alt" content="{{ $seo->imageAlt }}">
        @if ($seo->structuredData !== [])
            <script type="application/ld+json">{!! json_encode($seo->structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        @endif
    @endif
    <link rel="icon" href="{{ $faviconUrl }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="container header-inner" data-public-nav>
            <a class="brand" href="{{ route('home') }}" aria-label="PT Amara Al Medina Travel">
                <img src="{{ $logoUrl }}" alt="PT Amara Al Medina Travel" width="2048" height="2048" decoding="async">
            </a>

            <nav class="site-nav" id="public-navigation" aria-label="Navigasi utama" data-public-nav-menu>
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" @class(['active' => request()->routeIs($item['route']) || ($item['route'] === 'packages' && request()->routeIs('packages.*')) || ($item['route'] === 'bookings.create' && request()->routeIs('bookings.*'))])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="header-actions">
                <a class="btn btn-pink header-cta" href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">
                    <x-heroicon-o-chat-bubble-left-right class="header-cta-icon" aria-hidden="true" />
                    <span class="header-cta-label">Hubungi Kami</span>
                </a>
                <button
                    class="nav-toggle"
                    type="button"
                    aria-controls="public-navigation"
                    aria-expanded="false"
                    aria-label="Buka menu navigasi"
                    data-public-nav-toggle
                >
                    <x-heroicon-o-bars-3 class="nav-toggle-open" aria-hidden="true" />
                    <x-heroicon-o-x-mark class="nav-toggle-close" aria-hidden="true" />
                </button>
            </div>
        </div>
    </header>

    <main data-motion-scope>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-compact">
            <div class="footer-brand">
                <img class="footer-logo" src="{{ $logoUrl }}" alt="PT Amara Al Medina Travel" width="2048" height="2048" loading="lazy" decoding="async">
                <p>Perjalanan ibadah umrah yang amanah, nyaman, dan terpercaya bersama tim profesional.</p>
            </div>
            @if ($contact?->address)
                <div class="footer-address">
                    <x-heroicon-o-map-pin class="footer-contact-icon" aria-hidden="true" />
                    <a class="footer-link" href="{{ $mapUrl }}" target="_blank" rel="noopener">{{ $contact->address }}</a>
                </div>
            @endif
            <div class="footer-direct">
                @if ($contact?->email)
                    <p class="footer-contact-item">
                        <x-heroicon-o-envelope class="footer-contact-icon" aria-hidden="true" />
                        <a class="footer-link" href="{{ $mailUrl }}">{{ $contact->email }}</a>
                    </p>
                @endif
                @if ($contact?->whatsapp)
                    <p class="footer-contact-item">
                        <x-heroicon-o-phone class="footer-contact-icon" aria-hidden="true" />
                        <a class="footer-link" href="{{ $telUrl }}">{{ $contact->whatsapp }}</a>
                    </p>
                @endif
            </div>
        </div>
        <div class="footer-bottom">
            © {{ date('Y') }} PT Amara Al Medina Travel
        </div>
    </footer>
</body>
</html>
