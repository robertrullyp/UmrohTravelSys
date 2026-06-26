@extends('layouts.public')

@section('title', 'Tindak Lanjut Booking - PT Amara Al Medina Travel')

@section('content')
<section class="page-hero compact">
    <div class="container">
        <h1>Tindak Lanjut Booking</h1>
        <p>Halaman aman dari link WhatsApp admin. Link ini punya batas waktu.</p>
    </div>
</section>

<section class="section">
    <div class="container booking-status-shell">
        <article class="booking-status-card">
            @if (session('follow_up_status'))
                <div class="booking-success">
                    {{ session('follow_up_status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="booking-review-note is-danger">
                    <strong>Data belum bisa diproses</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="booking-status-heading">
                <div>
                    <span>Nomor Booking</span>
                    <h2>{{ $booking->booking_number }}</h2>
                </div>
                <span @class([
                    'booking-status-badge',
                    'is-pending' => $booking->status === \App\Models\Booking::STATUS_PENDING,
                    'is-approved' => $booking->status === \App\Models\Booking::STATUS_APPROVED,
                    'is-rejected' => $booking->status === \App\Models\Booking::STATUS_REJECTED,
                    'is-cancelled' => $booking->status === \App\Models\Booking::STATUS_CANCELLED,
                ])>
                    {{ \App\Models\Booking::STATUSES[$booking->status] ?? ucfirst($booking->status) }}
                </span>
            </div>

            <dl class="booking-summary">
                <div><dt>Paket</dt><dd>{{ $booking->umrahPackage->name }}</dd></div>
                <div><dt>Keberangkatan</dt><dd>{{ $booking->schedule->departure_date->translatedFormat('d F Y') }}</dd></div>
                <div><dt>Nama Pemesan</dt><dd>{{ $booking->customer_name }}</dd></div>
                <div><dt>WhatsApp</dt><dd>{{ $booking->whatsapp }}</dd></div>
                @if ($booking->email)
                    <div><dt>Email</dt><dd>{{ $booking->email }}</dd></div>
                @endif
                <div><dt>Jumlah Jamaah</dt><dd>{{ $booking->pilgrims_count }} orang</dd></div>
                <div><dt>Diajukan</dt><dd>{{ $booking->created_at->translatedFormat('d F Y, H:i') }}</dd></div>
                <div><dt>Admin Link</dt><dd>{{ $admin->name }}</dd></div>
            </dl>

            @if ($booking->notes)
                <div class="booking-review-note">
                    <strong>Catatan Pemesan</strong>
                    <p>{{ $booking->notes }}</p>
                </div>
            @endif

            @if ($booking->status !== \App\Models\Booking::STATUS_PENDING)
                <div class="booking-review-note">
                    <strong>Booking sudah diproses</strong>
                    <p>Status saat ini: {{ \App\Models\Booking::STATUSES[$booking->status] ?? ucfirst($booking->status) }}.</p>
                </div>
            @else
                <div class="booking-review-note">
                    <strong>OTP WhatsApp wajib diisi</strong>
                    <p>Salin kode OTP dari pesan WhatsApp booking baru, lalu masukkan pada aksi yang ingin dijalankan.</p>
                </div>
                <div class="booking-follow-up-actions">
                    @if ($admin->can('bookings.approve'))
                        <form method="post" action="{{ $actionUrls['approve'] }}" class="booking-follow-up-form">
                            @csrf
                            <h3>Setujui Booking</h3>
                            <label for="approve_follow_up_otp">Kode OTP WhatsApp</label>
                            <input id="approve_follow_up_otp" name="follow_up_otp" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" value="{{ old('follow_up_otp') }}">
                            <label for="approve_admin_notes">Catatan Admin</label>
                            <textarea id="approve_admin_notes" name="admin_notes" rows="3" placeholder="Opsional">{{ old('admin_notes') }}</textarea>
                            <button class="btn btn-green" type="submit">Setujui dan Kurangi Kuota</button>
                        </form>
                    @endif

                    @if ($admin->can('bookings.reject'))
                        <form method="post" action="{{ $actionUrls['reject'] }}" class="booking-follow-up-form">
                            @csrf
                            <h3>Tolak Booking</h3>
                            <label for="reject_follow_up_otp">Kode OTP WhatsApp</label>
                            <input id="reject_follow_up_otp" name="follow_up_otp" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" value="{{ old('follow_up_otp') }}">
                            <label for="rejection_reason">Alasan Penolakan</label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="3" required>{{ old('rejection_reason') }}</textarea>
                            <label for="reject_admin_notes">Catatan Internal</label>
                            <textarea id="reject_admin_notes" name="admin_notes" rows="3" placeholder="Opsional">{{ old('admin_notes') }}</textarea>
                            <button class="btn btn-pink" type="submit">Tolak Booking</button>
                        </form>
                    @endif

                    @if ($admin->can('bookings.cancel'))
                        <form method="post" action="{{ $actionUrls['cancel'] }}" class="booking-follow-up-form">
                            @csrf
                            <h3>Batalkan Booking</h3>
                            <label for="cancel_follow_up_otp">Kode OTP WhatsApp</label>
                            <input id="cancel_follow_up_otp" name="follow_up_otp" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" value="{{ old('follow_up_otp') }}">
                            <label for="cancel_admin_notes">Catatan Pembatalan</label>
                            <textarea id="cancel_admin_notes" name="admin_notes" rows="3" placeholder="Opsional">{{ old('admin_notes') }}</textarea>
                            <button class="btn btn-pink" type="submit">Batalkan Booking</button>
                        </form>
                    @endif
                </div>
            @endif
        </article>
    </div>
</section>
@endsection
