@extends('layouts.public')

@section('title', 'Galeri Kegiatan - PT Amara Al Medina Travel')

@section('content')
<section class="page-hero compact">
    <div class="container">
        <h1>Galeri Kegiatan</h1>
        <p>Dokumentasi kegiatan jamaah bersama PT. Amara Al Medina Travel.</p>
    </div>
</section>

<section class="section">
    <div class="container gallery-page-grid">
        @foreach ($galleries as $index => $gallery)
            <figure class="gallery-card">
                <button
                    class="gallery-preview-trigger"
                    type="button"
                    data-gallery-trigger
                    data-gallery-index="{{ $index }}"
                    data-gallery-src="{{ asset('storage/' . $gallery->image_path) }}"
                    data-gallery-title="{{ $gallery->title }}"
                    aria-label="Lihat foto {{ $gallery->title }}"
                >
                    <img src="{{ asset('storage/' . $gallery->image_path) }}" alt="{{ $gallery->title }}" width="1200" height="900" loading="lazy" decoding="async">
                </button>
                <figcaption>
                    <strong>{{ $gallery->title }}</strong>
                    @if ($gallery->taken_at)
                        <span>{{ $gallery->taken_at->translatedFormat('d F Y') }}</span>
                    @endif
                </figcaption>
            </figure>
        @endforeach
    </div>
</section>

<div class="gallery-lightbox" data-gallery-lightbox aria-hidden="true">
    <button class="lightbox-close" type="button" data-gallery-close aria-label="Tutup preview">
        <x-heroicon-o-x-mark class="lightbox-icon" aria-hidden="true" />
    </button>
    <button class="lightbox-nav lightbox-prev" type="button" data-gallery-prev aria-label="Foto sebelumnya">
        <x-heroicon-o-chevron-left class="lightbox-icon" aria-hidden="true" />
    </button>
    <figure class="lightbox-frame">
        <img
            data-gallery-image
            data-gallery-placeholder="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
            src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
            alt=""
        >
        <figcaption data-gallery-caption></figcaption>
    </figure>
    <button class="lightbox-nav lightbox-next" type="button" data-gallery-next aria-label="Foto berikutnya">
        <x-heroicon-o-chevron-right class="lightbox-icon" aria-hidden="true" />
    </button>
</div>
@endsection
