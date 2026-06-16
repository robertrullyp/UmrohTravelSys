@extends('layouts.public')

@section('title', 'Booking Umrah - PT Amara Al Medina Travel')
@section('description', 'Ajukan booking paket umrah PT Amara Al Medina Travel secara online.')

@section('content')
<section class="page-hero compact">
    <div class="container">
        <h1>Booking Umrah</h1>
        <p>Isi form singkat untuk mengajukan pemesanan kursi. Admin akan mengecek data dan menghubungi Anda melalui WhatsApp.</p>
    </div>
</section>

<section class="section">
    <div class="container booking-layout">
        <div class="booking-intro">
            <span class="booking-step-label">Form Pemesanan</span>
            <h2>Ajukan Pemesanan Kursi Umrah</h2>
            <p>Form ini adalah pengajuan awal. Kuota belum berkurang sebelum admin memeriksa data, memastikan jadwal masih tersedia, lalu menyetujui booking Anda.</p>
            <ol class="booking-steps">
                <li>Pilih paket dan jadwal keberangkatan yang tersedia.</li>
                <li>Isi nama, nomor WhatsApp, dan jumlah jamaah.</li>
                <li>Kirim form, lalu simpan nomor booking yang muncul.</li>
                <li>Cek status memakai nomor booking dan nomor WhatsApp di bagian Cek Status Booking, atau tunggu admin menghubungi Anda.</li>
            </ol>

            <div class="booking-status-lookup">
                <h3>Sudah punya nomor booking?</h3>
                <p>Cek hasil review dengan nomor booking dan nomor WhatsApp pemesan.</p>

                @if ($errors->bookingLookup->any())
                    <div class="form-alert booking-lookup-alert" role="alert">
                        {{ $errors->bookingLookup->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('bookings.status.lookup') }}">
                    @csrf
                    <label class="form-field">
                        <span>Nomor Booking</span>
                        <input
                            type="text"
                            name="lookup_booking_number"
                            value="{{ old('lookup_booking_number') }}"
                            maxlength="32"
                            placeholder="Contoh: AMA-260616-Y6DAOI"
                            autocomplete="off"
                            required
                        >
                    </label>
                    <label class="form-field">
                        <span>Nomor WhatsApp</span>
                        <input
                            type="tel"
                            name="lookup_whatsapp"
                            value="{{ old('lookup_whatsapp') }}"
                            maxlength="32"
                            placeholder="Contoh: 082252239507"
                            autocomplete="tel"
                            required
                        >
                    </label>
                    <button class="btn btn-pink" type="submit">Cek Status Booking</button>
                </form>
            </div>
        </div>

        <form class="booking-form" method="POST" action="{{ route('bookings.store') }}" data-booking-form>
            @csrf

            @if ($errors->any())
                <div class="form-alert" role="alert">
                    <strong>Periksa kembali data booking.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($packages->isEmpty())
                <div class="form-alert">
                    Belum ada paket dengan jadwal dan kuota yang tersedia. Silakan hubungi admin untuk informasi berikutnya.
                </div>
            @else
                <div class="form-grid">
                    <label class="form-field">
                        <span>Paket Umrah</span>
                        <select name="umrah_package_id" required data-booking-package>
                            <option value="">Pilih paket</option>
                            @foreach ($packages as $packageOption)
                                <option
                                    value="{{ $packageOption->id }}"
                                    @selected((string) old('umrah_package_id', $selectedPackage?->id) === (string) $packageOption->id)
                                >
                                    {{ $packageOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('umrah_package_id') <small>{{ $message }}</small> @enderror
                    </label>

                    <label class="form-field">
                        <span>Jadwal Keberangkatan</span>
                        <select name="schedule_id" required data-booking-schedule>
                            <option value="">Pilih jadwal</option>
                            @foreach ($packages as $packageOption)
                                @foreach ($packageOption->schedules as $schedule)
                                    <option
                                        value="{{ $schedule->id }}"
                                        data-package="{{ $packageOption->id }}"
                                        data-quota="{{ $schedule->quota }}"
                                        @selected((string) old('schedule_id') === (string) $schedule->id)
                                    >
                                        {{ $packageOption->name }} - {{ $schedule->departure_date->translatedFormat('d F Y') }} ({{ $schedule->quota }} kursi)
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                        <small class="field-hint" data-booking-quota>Pilih jadwal untuk melihat sisa kuota.</small>
                        @error('schedule_id') <small>{{ $message }}</small> @enderror
                    </label>

                    <label class="form-field">
                        <span>Nama Pemesan</span>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" maxlength="255" autocomplete="name" required>
                        @error('customer_name') <small>{{ $message }}</small> @enderror
                    </label>

                    <label class="form-field">
                        <span>Nomor WhatsApp</span>
                        <input type="tel" name="whatsapp" value="{{ old('whatsapp') }}" maxlength="32" autocomplete="tel" placeholder="Contoh: 082252239507" required>
                        @error('whatsapp') <small>{{ $message }}</small> @enderror
                    </label>

                    <label class="form-field">
                        <span>Email <em>(opsional)</em></span>
                        <input type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email">
                        @error('email') <small>{{ $message }}</small> @enderror
                    </label>

                    <label class="form-field">
                        <span>Jumlah Jamaah</span>
                        <input type="number" name="pilgrims_count" value="{{ old('pilgrims_count', 1) }}" min="1" max="100" inputmode="numeric" required data-booking-pilgrims>
                        @error('pilgrims_count') <small>{{ $message }}</small> @enderror
                    </label>

                    <label class="form-field form-field-full">
                        <span>Catatan <em>(opsional)</em></span>
                        <textarea name="notes" rows="3" maxlength="2000" placeholder="Kebutuhan khusus atau informasi tambahan">{{ old('notes') }}</textarea>
                        @error('notes') <small>{{ $message }}</small> @enderror
                    </label>
                </div>

                <div class="booking-submit">
                    <p>Dengan mengirim form, Anda menyetujui admin menghubungi nomor WhatsApp yang dicantumkan.</p>
                    <button class="btn btn-green" type="submit">Kirim Booking</button>
                </div>
            @endif
        </form>
    </div>
</section>
@endsection
