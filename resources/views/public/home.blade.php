@extends('layouts.public')

@section('title', 'Beranda - PT Amara Al Medina Travel')

@section('content')
<section class="hero">
    <div class="container hero-frame">
        <div class="hero-copy">
            <p class="eyebrow">{{ $settings->get('hero_title_highlight', 'Perjalanan Ibadah Umrah') }}</p>
            <h1>{{ $settings->get('hero_title', 'Nyaman, Aman & Terpercaya') }}</h1>
            <p>{{ $settings->get('hero_subtitle', 'PT Amara Al Medina Travel siap menjadi mitra perjalanan ibadah terbaik Anda dengan pelayanan profesional dan amanah.') }}</p>
            <div class="hero-actions">
                <a class="btn btn-pink" href="{{ route('packages') }}">Lihat Paket Umrah</a>
                <a class="btn btn-green" href="{{ route('schedules') }}">Lihat Jadwal</a>
            </div>
        </div>
        <div class="hero-visual">
            <img
                src="{{ asset($settings->get('hero_image_path', 'images/site/beranda-img.jpg') ?: 'images/site/beranda-img.jpg') }}"
                alt="Ka'bah dan jamaah PT Amara Al Medina Travel"
                width="1280"
                height="720"
                fetchpriority="high"
                loading="eager"
                data-hero-parallax
            >
        </div>
    </div>
</section>

<section class="trust-strip">
    <div class="container trust-grid">
        <div class="trust-card">
            <x-heroicon-o-shield-check class="trust-icon" />
            <div><strong>Amanah</strong><span>Pelayanan sesuai syariah dan amanah</span></div>
        </div>
        <div class="trust-card">
            <x-heroicon-o-users class="trust-icon" />
            <div><strong>Profesional</strong><span>Tim berpengalaman dan responsif</span></div>
        </div>
        <div class="trust-card">
            <x-heroicon-o-home-modern class="trust-icon" />
            <div><strong>Nyaman</strong><span>Fasilitas terbaik untuk jamaah</span></div>
        </div>
        <div class="trust-card">
            <x-heroicon-o-hand-thumb-up class="trust-icon" />
            <div><strong>Terpercaya</strong><span>Izin resmi dan terpercaya</span></div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container section-heading">
        <h2>Paket Umrah</h2>
        <p>Pilihan paket terbaik untuk perjalanan ibadah Anda.</p>
    </div>
    <div class="container package-grid">
        @foreach ($packages as $package)
            <a class="package-card" href="{{ route('packages.show', $package) }}">
                <img src="{{ $package->image_path ? asset('storage/' . $package->image_path) : asset('images/seed/package-plus-tarim.jpeg') }}" alt="{{ $package->name }}" width="1600" height="1100" loading="lazy" decoding="async">
                <div>
                    <h3>{{ $package->name }}</h3>
                    <span class="price">Rp {{ number_format((float) $package->price, 0, ',', '.') }} / Orang</span>
                    <p>{{ $package->duration_days }} Hari · {{ $package->airline }} · {{ $package->departure_month }}</p>
                </div>
            </a>
        @endforeach
    </div>
    <div class="section-action">
        <a class="btn btn-pink" href="{{ route('packages') }}">Lihat Semua Paket</a>
    </div>
</section>

<section class="section section-muted">
    <div class="container section-muted-frame compact-schedule">
        <div class="section-heading">
            <h2>Jadwal Keberangkatan</h2>
            <p>Informasi jadwal dapat berubah sesuai ketersediaan dan konfirmasi admin.</p>
        </div>
        <div class="table-card schedule-table">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Paket</th>
                        <th>Kuota</th>
                        <th>Tersedia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->departure_date->translatedFormat('d F Y') }}</td>
                            <td>{{ $schedule->umrahPackage?->name }}</td>
                            <td>{{ $schedule->capacity }}</td>
                            <td>{{ $schedule->quota }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('public.partials.schedule-cards', [
            'schedules' => $schedules,
            'showPackage' => true,
            'showStatus' => false,
            'showAction' => false,
        ])
        <div class="section-action">
            <a class="btn btn-green" href="{{ route('schedules') }}">Lihat Semua Jadwal</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container section-heading">
        <h2>Galeri Kegiatan</h2>
        <p>Dokumentasi kegiatan jamaah bersama PT. Amara Al Medina Travel.</p>
    </div>
    <div class="container gallery-grid">
        @foreach ($galleries as $gallery)
            @if ($gallery->cover_image_path)
                <a href="{{ route('galleries') }}" class="gallery-tile" aria-label="Lihat album {{ $gallery->title }}">
                    <img src="{{ asset('storage/' . $gallery->cover_image_path) }}" alt="{{ $gallery->title }}" width="1200" height="900" loading="lazy" decoding="async">
                </a>
            @endif
        @endforeach
    </div>
    <div class="section-action">
        <a class="btn btn-pink" href="{{ route('galleries') }}">Lihat Semua Galeri</a>
    </div>
</section>
@endsection
