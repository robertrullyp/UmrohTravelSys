@extends('layouts.public')

@section('title', 'Kontak - PT Amara Al Medina Travel')

@section('content')
@php
    $contacts = collect($contacts ?? [])->values();
    $mapContact = $contact ?? $contacts->first();
    $formatWhatsapp = function (?string $value): string {
        $digits = preg_replace('/\D+/', '', $value ?? '');

        return str_starts_with($digits, '0') ? '62' . substr($digits, 1) : $digits;
    };
@endphp

<section class="page-hero compact">
    <div class="container">
        <h1>Informasi Kontak</h1>
        <p>Hubungi admin untuk konsultasi paket, jadwal, dan keberangkatan.</p>
    </div>
</section>

<section class="section contact-section">
    <div class="container contact-layout">
        <div class="contact-panel contact-list">
            @forelse ($contacts as $contactItem)
                @php
                    $whatsapp = $formatWhatsapp($contactItem->whatsapp);
                    $hasCoordinates = $contactItem->latitude !== null && $contactItem->longitude !== null;
                    $mapQuery = $hasCoordinates
                        ? $contactItem->latitude . ',' . $contactItem->longitude
                        : $contactItem->address;
                    $mapUrl = $mapQuery
                        ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapQuery)
                        : $contactItem->map_embed_url;
                @endphp

                <article class="contact-public-card">
                    <div class="contact-public-card-header">
                        <h2>{{ $contactItem->is_primary ? 'Kontak Utama' : 'Kontak Lainnya' }}</h2>
                        @if ($contactItem->is_primary)
                            <span>Utama</span>
                        @endif
                    </div>

                    <dl class="contact-public-list">
                        <div>
                            <dt>Alamat</dt>
                            <dd>
                                @if ($mapUrl)
                                    <a href="{{ $mapUrl }}" target="_blank" rel="noopener">{{ $contactItem->address }}</a>
                                @else
                                    {{ $contactItem->address }}
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt>No WhatsApp</dt>
                            <dd>
                                @if ($whatsapp)
                                    <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">{{ $contactItem->whatsapp }}</a>
                                @else
                                    {{ $contactItem->whatsapp }}
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd><a href="mailto:{{ $contactItem->email }}">{{ $contactItem->email }}</a></dd>
                        </div>
                        @if ($contactItem->instagram)
                            <div>
                                <dt>Instagram</dt>
                                <dd>{{ $contactItem->instagram }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($whatsapp)
                        <a class="btn btn-green" href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">Hubungi via WhatsApp</a>
                    @endif
                </article>
            @empty
                <article class="contact-public-card">
                    <h2>Kontak belum tersedia</h2>
                    <p>Silakan kembali lagi nanti atau gunakan tombol Hubungi Kami di header jika tersedia.</p>
                </article>
            @endforelse
        </div>

        <div class="map-panel">
            @if ($mapContact?->map_embed_url)
                <iframe src="{{ $mapContact->map_embed_url }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta PT Amara Al Medina Travel"></iframe>
            @else
                <img src="{{ asset('images/seed/map-preview.jpeg') }}" alt="Peta lokasi PT Amara Al Medina Travel">
            @endif
        </div>
    </div>
</section>
@endsection
