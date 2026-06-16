@extends('layouts.public')

@section('title', 'Status Booking ' . $booking->booking_number)

@php
    $adminWhatsappRaw = $contact?->whatsapp ?? $settings->get('cta_whatsapp', '');
    $adminWhatsapp = preg_replace('/\D+/', '', $adminWhatsappRaw);
    $adminWhatsapp = str_starts_with($adminWhatsapp, '0') ? '62' . substr($adminWhatsapp, 1) : $adminWhatsapp;
    $bookingStatus = \App\Models\Booking::STATUSES[$booking->status] ?? ucfirst($booking->status);
    $bookingDetailUrl = route('bookings.show', $booking->public_token);
    $adminMessage = implode("\n", [
        'Assalamu alaikum admin PT Amara Al Medina Travel.',
        'Saya ingin bertanya tentang booking berikut:',
        'Nomor Booking: ' . $booking->booking_number,
        'Nama Pemesan: ' . $booking->customer_name,
        'Paket: ' . $booking->umrahPackage->name,
        'Keberangkatan: ' . $booking->schedule->departure_date->translatedFormat('d F Y'),
        'Jumlah Jamaah: ' . $booking->pilgrims_count . ' orang',
        'Status: ' . $bookingStatus,
        'Link Detail: ' . $bookingDetailUrl,
        'Mohon bantuan dan konfirmasinya. Terima kasih.',
    ]);
    $adminContactUrl = $adminWhatsapp
        ? 'https://wa.me/' . $adminWhatsapp . '?text=' . rawurlencode($adminMessage)
        : route('contact');
@endphp

@section('content')
<section class="page-hero compact">
    <div class="container">
        <h1>Status Booking</h1>
        <p>Simpan halaman ini untuk memantau hasil review booking Anda.</p>
    </div>
</section>

<section class="section">
    <div class="container booking-status-shell">
        <article class="booking-status-card">
            @if (session('booking_submitted'))
                <div class="booking-success">
                    Booking berhasil dikirim. Admin akan melakukan review sebelum kuota dikonfirmasi.
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
                <div><dt>WhatsApp</dt><dd>{{ $booking->masked_whatsapp }}</dd></div>
                @if ($booking->masked_email)
                    <div><dt>Email</dt><dd>{{ $booking->masked_email }}</dd></div>
                @endif
                <div><dt>Jumlah Jamaah</dt><dd>{{ $booking->pilgrims_count }} orang</dd></div>
                <div><dt>Diajukan</dt><dd>{{ $booking->created_at->translatedFormat('d F Y, H:i') }}</dd></div>
            </dl>

            @if ($booking->status === \App\Models\Booking::STATUS_REJECTED && $booking->rejection_reason)
                <div class="booking-review-note is-danger">
                    <strong>Alasan penolakan</strong>
                    <p>{{ $booking->rejection_reason }}</p>
                </div>
            @elseif ($booking->status === \App\Models\Booking::STATUS_APPROVED)
                <div class="booking-review-note is-success">
                    <strong>Booking telah disetujui</strong>
                    <p>Kuota untuk {{ $booking->pilgrims_count }} jamaah telah dikonfirmasi. Admin akan menghubungi Anda untuk proses berikutnya.</p>
                </div>
            @elseif ($booking->status === \App\Models\Booking::STATUS_CANCELLED)
                <div class="booking-review-note">
                    <strong>Booking dibatalkan</strong>
                    <p>Hubungi admin bila Anda memerlukan informasi atau ingin mengajukan booking baru.</p>
                </div>
            @endif

            <div class="booking-status-actions">
                <a class="btn btn-green" href="{{ route('bookings.create') }}">Buat Booking Baru</a>
                <a
                    class="btn btn-pink"
                    href="{{ $adminContactUrl }}"
                    @if ($adminWhatsapp) target="_blank" rel="noopener" @endif
                >
                    Hubungi Admin
                </a>
            </div>
        </article>
    </div>
</section>
@endsection
