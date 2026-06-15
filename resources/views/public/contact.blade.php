@extends('layouts.public')

@section('title', 'Kontak - PT Amara Al Medina Travel')

@section('content')
@php
    $whatsapp = preg_replace('/\D+/', '', $contact?->whatsapp ?? '');
    $whatsapp = str_starts_with($whatsapp, '0') ? '62' . substr($whatsapp, 1) : $whatsapp;
@endphp

<section class="page-hero compact">
    <div class="container">
        <h1>Informasi Kontak</h1>
        <p>Hubungi admin untuk konsultasi paket, jadwal, dan keberangkatan.</p>
    </div>
</section>

<section class="section contact-section">
    <div class="container contact-layout">
        <div class="contact-panel">
            <div>
                <h2>Alamat</h2>
                <p>{{ $contact?->address }}</p>
            </div>
            <div>
                <h2>No WhatsApp</h2>
                <p>{{ $contact?->whatsapp }}</p>
            </div>
            <div>
                <h2>Email</h2>
                <p>{{ $contact?->email }}</p>
            </div>
            <div>
                <h2>Instagram</h2>
                <p>{{ $contact?->instagram }}</p>
            </div>
            <a class="btn btn-green" href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">Hubungi via WhatsApp</a>
        </div>

        <div class="map-panel">
            @if ($contact?->map_embed_url)
                <iframe src="{{ $contact->map_embed_url }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta PT Amara Al Medina Travel"></iframe>
            @else
                <img src="{{ asset('images/seed/map-preview.jpeg') }}" alt="Peta lokasi PT Amara Al Medina Travel">
            @endif
        </div>
    </div>
</section>
@endsection
