<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\BookingFollowUpOtpService;
use App\Services\WhatsAppGatewayService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\URL;
use Throwable;

class SendBookingFollowUpWhatsApp implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $bookingId) {}

    public function handle(WhatsAppGatewayService $gateway, BookingFollowUpOtpService $otpService): void
    {
        if (! $gateway->isEnabled()) {
            return;
        }

        $booking = Booking::query()
            ->with(['umrahPackage', 'schedule'])
            ->find($this->bookingId);

        if (! $booking instanceof Booking || $booking->status !== Booking::STATUS_PENDING) {
            return;
        }

        $this->recipients()->each(function (User $admin) use ($booking, $gateway, $otpService): void {
            try {
                $otp = $otpService->generate($booking, $admin);

                $gateway->sendInteractive(
                    $admin->phone,
                    $this->message($booking, $otpService->expiryMinutes()),
                    [
                        [
                            'type' => 'url',
                            'text' => 'Tindaklanjuti Booking',
                            'url' => $this->followUpUrl($booking, $admin),
                        ],
                        [
                            'type' => 'copy',
                            'text' => 'Copy OTP',
                            'copyCode' => $otp,
                        ],
                    ],
                    'Booking Baru',
                    'booking-follow-up-'.$booking->getKey().'-'.$admin->getKey(),
                );
            } catch (Throwable $exception) {
                report($exception);
            }
        });
    }

    private function recipients()
    {
        return User::query()
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get()
            ->filter(fn (User $user): bool => ($user->hasRole('super-admin') || $user->can('panel.access'))
                && (
                    $user->can('bookings.approve')
                    || $user->can('bookings.reject')
                    || $user->can('bookings.cancel')
                ));
    }

    private function followUpUrl(Booking $booking, User $admin): string
    {
        return URL::temporarySignedRoute(
            'booking-follow-up.show',
            now()->addMinutes(SiteSetting::getInteger('booking_followup_link_expires_minutes', 1440, 15, 10080)),
            [
                'booking' => $booking,
                'admin' => $admin->getKey(),
            ],
        );
    }

    private function message(Booking $booking, int $otpExpiryMinutes): string
    {
        $scheduleDate = $booking->schedule?->departure_date?->translatedFormat('d F Y') ?? '-';
        $notes = filled($booking->notes) ? $booking->notes : '-';

        return implode("\n", [
            'Booking baru perlu ditindaklanjuti.',
            '',
            'Nomor: '.$booking->booking_number,
            'Nama: '.$booking->customer_name,
            'WhatsApp: '.$booking->whatsapp,
            'Paket: '.($booking->umrahPackage?->name ?? '-'),
            'Keberangkatan: '.$scheduleDate,
            'Jumlah jamaah: '.$booking->pilgrims_count.' orang',
            'Catatan: '.$notes,
            'Status: '.(Booking::STATUSES[$booking->status] ?? $booking->status),
            '',
            'Kode OTP wajib diisi saat submit aksi dari link tindak lanjut.',
            'OTP berlaku '.$otpExpiryMinutes.' menit.',
        ]);
    }
}
