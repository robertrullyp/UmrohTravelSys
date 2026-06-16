<?php

namespace App\Http\Controllers;

use App\Http\Requests\LookupBookingStatusRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Schedule;
use App\Models\UmrahPackage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class BookingController extends PublicPageController
{
    public function create(?UmrahPackage $package = null): View
    {
        if ($package !== null) {
            abort_unless($package->is_active, 404);
        }

        $packages = UmrahPackage::query()
            ->where('is_active', true)
            ->whereHas('schedules', fn ($query) => $query
                ->where('is_active', true)
                ->whereDate('departure_date', '>=', today())
                ->where('quota', '>', 0))
            ->with(['schedules' => fn ($query) => $query
                ->where('is_active', true)
                ->whereDate('departure_date', '>=', today())
                ->where('quota', '>', 0)
                ->orderBy('departure_date')])
            ->orderBy('sort_order')
            ->get();

        return view('public.booking-form', [
            ...$this->sharedData(),
            'packages' => $packages,
            'selectedPackage' => $package,
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = DB::transaction(function () use ($request): Booking {
            $schedule = Schedule::query()->lockForUpdate()->findOrFail($request->integer('schedule_id'));

            if (
                ! $schedule->is_active
                || $schedule->departure_date->lt(today())
                || $schedule->umrah_package_id !== $request->integer('umrah_package_id')
                || $schedule->quota < $request->integer('pilgrims_count')
            ) {
                abort(422, 'Kuota jadwal berubah dan tidak lagi mencukupi.');
            }

            return Booking::query()->create([
                ...$request->safe()->only([
                    'umrah_package_id',
                    'schedule_id',
                    'customer_name',
                    'whatsapp',
                    'email',
                    'pilgrims_count',
                    'notes',
                ]),
                'booking_number' => $this->generateBookingNumber(),
                'public_token' => Str::random(48),
                'status' => Booking::STATUS_PENDING,
            ]);
        }, 3);

        return redirect()
            ->route('bookings.show', $booking->public_token)
            ->with('booking_submitted', true);
    }

    public function lookup(LookupBookingStatusRequest $request): RedirectResponse
    {
        $booking = Booking::query()
            ->where('booking_number', Str::upper(trim((string) $request->input('lookup_booking_number'))))
            ->first();

        if (
            $booking === null
            || $this->normalizeWhatsapp($booking->whatsapp) !== $this->normalizeWhatsapp((string) $request->input('lookup_whatsapp'))
        ) {
            return Redirect::back()
                ->withInput($request->safe()->only(['lookup_booking_number', 'lookup_whatsapp']))
                ->withErrors([
                    'lookup' => 'Data booking tidak ditemukan atau nomor WhatsApp tidak sesuai.',
                ], 'bookingLookup');
        }

        return redirect()->route('bookings.show', $booking->public_token);
    }

    public function show(Booking $booking): View
    {
        return view('public.booking-detail', [
            ...$this->sharedData(),
            'booking' => $booking->load(['umrahPackage', 'schedule']),
        ]);
    }

    private function generateBookingNumber(): string
    {
        do {
            $number = 'AMA-' . now()->format('ymd') . '-' . Str::upper(Str::random(6));
        } while (Booking::query()->where('booking_number', $number)->exists());

        return $number;
    }

    private function normalizeWhatsapp(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        return $digits;
    }
}
