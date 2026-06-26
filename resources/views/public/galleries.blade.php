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
        @forelse ($galleryAlbums as $index => $album)
            <figure class="gallery-card">
                <button
                    class="gallery-preview-trigger"
                    type="button"
                    data-gallery-trigger
                    data-gallery-index="{{ $index }}"
                    aria-label="Lihat album {{ $album['title'] }}"
                >
                    <img src="{{ $album['cover'] }}" alt="{{ $album['coverAlt'] }}" width="1200" height="900" loading="lazy" decoding="async">
                </button>
                <figcaption>
                    <strong>{{ $album['title'] }}</strong>
                    @if ($album['date'])
                        <span>{{ $album['date'] }}</span>
                    @endif
                    <span>{{ $album['photoCount'] }} foto</span>
                </figcaption>
            </figure>
        @empty
            <p class="empty-state">Belum ada album galeri yang ditampilkan.</p>
        @endforelse
    </div>
</section>

<script type="application/json" data-gallery-albums>
    @json($galleryAlbums)
</script>

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
        <figcaption>
            <strong data-gallery-caption></strong>
            <span data-gallery-counter></span>
        </figcaption>
        <div class="lightbox-thumbnails" data-gallery-thumbnails></div>
    </figure>
    <button class="lightbox-nav lightbox-next" type="button" data-gallery-next aria-label="Foto berikutnya">
        <x-heroicon-o-chevron-right class="lightbox-icon" aria-hidden="true" />
    </button>
</div>
@endsection
