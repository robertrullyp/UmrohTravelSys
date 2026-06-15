@extends('layouts.public')

@section('title', 'Paket Umrah - PT Amara Al Medina Travel')

@section('content')
<section class="page-hero compact">
    <div class="container">
        <h1>Paket Umrah</h1>
        <p>Pilihan paket terbaik untuk perjalanan ibadah Anda.</p>
    </div>
</section>

<section class="section">
    <div class="container package-list">
        @foreach ($packages as $package)
            <article class="package-wide">
                <img src="{{ $package->image_path ? asset('storage/' . $package->image_path) : asset('images/seed/package-plus-tarim.jpeg') }}" alt="{{ $package->name }}">
                <div class="package-content">
                    <h2>{{ $package->name }}</h2>
                    <span class="price">Rp {{ number_format((float) $package->price, 0, ',', '.') }} / Orang</span>
                    <dl class="package-meta">
                        <div><dt>Durasi</dt><dd>{{ $package->duration_days }} Hari</dd></div>
                        <div><dt>Maskapai</dt><dd>{{ $package->airline }}</dd></div>
                        <div><dt>Hotel</dt><dd>{{ $package->makkah_hotel }} · {{ $package->madinah_hotel }}</dd></div>
                        <div><dt>Keberangkatan</dt><dd>{{ $package->departure_month }}</dd></div>
                    </dl>
                    <a class="btn btn-green" href="{{ route('packages.show', $package) }}">Lihat Detail Paket</a>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endsection
