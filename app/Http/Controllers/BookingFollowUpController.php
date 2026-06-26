<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\BookingFollowUpOtpService;
use App\Services\BookingStatusService;
use App\Support\SeoData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class BookingFollowUpController extends PublicPageController
{
    public function show(Request $request, Booking $booking): View
    {
        $admin = $this->adminFromRequest($request);
        $booking->load(['umrahPackage', 'schedule', 'reviewer']);
        $data = $this->publicViewData('booking-detail', [
            'booking' => $booking,
            'admin' => $admin,
            'actionUrls' => $this->actionUrls($booking, $admin),
        ]);

        $data['seo'] = new SeoData(
            title: 'Tindak Lanjut Booking - PT Amara Al Medina Travel',
            description: 'Halaman privat untuk tindak lanjut booking umrah.',
            robots: 'noindex,nofollow,nosnippet',
            canonical: null,
            image: $data['seo']->image,
            imageAlt: $data['seo']->imageAlt,
            type: 'website',
            siteName: $data['seo']->siteName,
            googleSiteVerification: null,
            structuredData: [],
        );

        return view('public.booking-follow-up', $data);
    }

    public function approve(Request $request, Booking $booking, BookingStatusService $service, BookingFollowUpOtpService $otpService): RedirectResponse
    {
        $admin = $this->adminFromRequest($request, 'bookings.approve');
        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'follow_up_otp' => ['required', 'string', 'size:6'],
        ]);

        $this->validateOtp($booking, $admin, $data['follow_up_otp'], $otpService);

        $service->approve($booking, $admin, $data['admin_notes'] ?? null);
        $otpService->consume($booking, $admin);

        return $this->redirectToShow($booking, $admin)
            ->with('follow_up_status', 'Booking disetujui dan kuota diperbarui.');
    }

    public function reject(Request $request, Booking $booking, BookingStatusService $service, BookingFollowUpOtpService $otpService): RedirectResponse
    {
        $admin = $this->adminFromRequest($request, 'bookings.reject');
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'follow_up_otp' => ['required', 'string', 'size:6'],
        ]);

        $this->validateOtp($booking, $admin, $data['follow_up_otp'], $otpService);

        $service->reject($booking, $admin, $data['rejection_reason'], $data['admin_notes'] ?? null);
        $otpService->consume($booking, $admin);

        return $this->redirectToShow($booking, $admin)
            ->with('follow_up_status', 'Booking ditolak.');
    }

    public function cancel(Request $request, Booking $booking, BookingStatusService $service, BookingFollowUpOtpService $otpService): RedirectResponse
    {
        $admin = $this->adminFromRequest($request, 'bookings.cancel');
        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'follow_up_otp' => ['required', 'string', 'size:6'],
        ]);

        $this->validateOtp($booking, $admin, $data['follow_up_otp'], $otpService);

        $service->cancel($booking, $admin, $data['admin_notes'] ?? null);
        $otpService->consume($booking, $admin);

        return $this->redirectToShow($booking, $admin)
            ->with('follow_up_status', 'Booking dibatalkan.');
    }

    private function adminFromRequest(Request $request, ?string $permission = null): User
    {
        $admin = User::query()->findOrFail($request->integer('admin'));

        abort_unless($admin->hasRole('super-admin') || $admin->can('panel.access'), 403);

        if ($permission !== null) {
            abort_unless($admin->can($permission), 403);
        } else {
            abort_unless(
                $admin->can('bookings.approve')
                || $admin->can('bookings.reject')
                || $admin->can('bookings.cancel'),
                403,
            );
        }

        return $admin;
    }

    private function validateOtp(Booking $booking, User $admin, string $otp, BookingFollowUpOtpService $otpService): void
    {
        if ($otpService->verify($booking, $admin, $otp)) {
            return;
        }

        throw ValidationException::withMessages([
            'follow_up_otp' => 'Kode OTP salah atau sudah kedaluwarsa. Minta notifikasi baru jika diperlukan.',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function actionUrls(Booking $booking, User $admin): array
    {
        return collect(['approve', 'reject', 'cancel'])
            ->mapWithKeys(fn (string $action): array => [
                $action => $this->signedUrl('booking-follow-up.'.$action, $booking, $admin),
            ])
            ->all();
    }

    private function redirectToShow(Booking $booking, User $admin): RedirectResponse
    {
        return redirect()->to($this->signedUrl('booking-follow-up.show', $booking, $admin));
    }

    private function signedUrl(string $route, Booking $booking, User $admin): string
    {
        return URL::temporarySignedRoute(
            $route,
            now()->addMinutes(SiteSetting::getInteger('booking_followup_link_expires_minutes', 1440, 15, 10080)),
            [
                'booking' => $booking,
                'admin' => $admin->getKey(),
            ],
        );
    }
}
