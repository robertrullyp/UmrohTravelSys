<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class BookingFollowUpOtpService
{
    public function generate(Booking $booking, User $admin): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(
            $this->cacheKey($booking, $admin),
            Hash::make($code),
            now()->addMinutes($this->expiryMinutes()),
        );

        RateLimiter::clear($this->verifyRateLimitKey($booking, $admin));

        return $code;
    }

    public function verify(Booking $booking, User $admin, string $code): bool
    {
        $rateLimitKey = $this->verifyRateLimitKey($booking, $admin);

        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 5)) {
            return false;
        }

        RateLimiter::hit($rateLimitKey, 300);

        $hash = Cache::get($this->cacheKey($booking, $admin));

        return filled($hash) && Hash::check($code, (string) $hash);
    }

    public function consume(Booking $booking, User $admin): void
    {
        Cache::forget($this->cacheKey($booking, $admin));
        RateLimiter::clear($this->verifyRateLimitKey($booking, $admin));
    }

    public function expiryMinutes(): int
    {
        return SiteSetting::getInteger('booking_followup_otp_expires_minutes', 60, 5, 1440);
    }

    private function cacheKey(Booking $booking, User $admin): string
    {
        return 'booking-follow-up-otp:'.$booking->getKey().':'.$admin->getKey();
    }

    private function verifyRateLimitKey(Booking $booking, User $admin): string
    {
        return 'booking-follow-up-otp-verify:'.$booking->getKey().':'.$admin->getKey();
    }
}
