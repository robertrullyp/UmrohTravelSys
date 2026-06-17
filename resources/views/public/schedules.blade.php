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
    <div class="container table-card">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal Keberangkatan</th>
                    <th>Paket</th>
                    <th>Kuota</th>
                    <th>Tersedia</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($schedules as $schedule)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $schedule->departure_date->translatedFormat('d F Y') }}</td>
                        <td>{{ $schedule->umrahPackage?->name }}</td>
                        <td>{{ $schedule->capacity }}</td>
                        <td>{{ $schedule->quota }}</td>
                        <td><span class="status">{{ $schedule->status }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
