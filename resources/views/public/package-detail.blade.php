@extends('layouts.public')

@section('title', $package->name . ' - PT Amara Al Medina Travel')

@section('content')
<section class="page-hero compact">
    <div class="container">
        <h1>{{ $package->name }}</h1>
        <p>Home / Paket Umrah / {{ $package->name }}</p>
    </div>
</section>

<section class="section">
    <div class="container package-detail">
        <img class="detail-image" src="{{ $package->image_path ? asset('storage/' . $package->image_path) : asset('images/seed/package-plus-tarim.jpeg') }}" alt="{{ $package->name }}">
        <div class="detail-content">
            <span class="price">Rp {{ number_format((float) $package->price, 0, ',', '.') }} / Orang</span>
            <p>{{ $package->description }}</p>
            <dl class="package-meta">
                <div><dt>Durasi</dt><dd>{{ $package->duration_days }} Hari</dd></div>
                <div><dt>Maskapai</dt><dd>{{ $package->airline }}</dd></div>
                <div><dt>Hotel Makkah</dt><dd>{{ $package->makkah_hotel }}</dd></div>
                <div><dt>Hotel Madinah</dt><dd>{{ $package->madinah_hotel }}</dd></div>
                <div><dt>Keberangkatan</dt><dd>{{ $package->departure_month }}</dd></div>
            </dl>
            <h2>Fasilitas</h2>
            <ul class="check-list">
                @foreach (($package->includes ?? []) as $include)
                    <li>{{ is_array($include) ? ($include['item'] ?? '') : $include }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

<section class="section section-muted">
    <div class="container section-muted-frame">
        <div class="section-heading">
            <h2>Jadwal Paket Ini</h2>
            <p>Konfirmasi ketersediaan kuota melalui WhatsApp admin.</p>
        </div>
        <div class="table-card">
            <table>
                <thead><tr><th>Tanggal</th><th>Kuota</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->departure_date->translatedFormat('d F Y') }}</td>
                            <td>{{ $schedule->quota }}</td>
                            <td><span class="status">{{ $schedule->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3">Belum ada jadwal untuk paket ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
