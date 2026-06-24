@extends('layouts.public')

@section('title', 'Jadwal Keberangkatan - PT Amara Al Medina Travel')

@section('content')
<section class="page-hero compact">
    <div class="container">
        <h1>Jadwal Keberangkatan</h1>
        <p>Daftar jadwal keberangkatan paket umrah.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="table-card schedule-table">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Keberangkatan</th>
                        <th>Paket</th>
                        <th>Kuota</th>
                        <th>Tersedia</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($schedules as $schedule)
                        @php
                            $package = $schedule->umrahPackage;
                            $canBook = $package
                                && $package->is_active
                                && $schedule->quota > 0
                                && ! $schedule->departure_date->lt(today());
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $schedule->departure_date->translatedFormat('d F Y') }}</td>
                            <td>{{ $schedule->umrahPackage?->name }}</td>
                            <td>{{ $schedule->capacity }}</td>
                            <td>{{ $schedule->quota }}</td>
                            <td><span class="status">{{ $schedule->status }}</span></td>
                            <td>
                                @if ($canBook)
                                    <a class="table-link" href="{{ route('bookings.package', $package) }}">Booking</a>
                                @else
                                    <span class="muted-text">Tidak tersedia</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('public.partials.schedule-cards', [
            'schedules' => $schedules,
            'showPackage' => true,
            'showStatus' => true,
            'showAction' => true,
        ])
    </div>
</section>
@endsection
