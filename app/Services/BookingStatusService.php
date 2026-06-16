<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingStatusService
{
    public function approve(Booking $booking, User $reviewer, ?string $adminNotes = null): Booking
    {
        return DB::transaction(function () use ($booking, $reviewer, $adminNotes): Booking {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->getKey());

            if ($lockedBooking->status !== Booking::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya booking berstatus menunggu review yang dapat disetujui.',
                ]);
            }

            $schedule = Schedule::query()->lockForUpdate()->findOrFail($lockedBooking->schedule_id);

            if (
                ! $schedule->is_active
                || $schedule->departure_date->lt(today())
                || $schedule->umrah_package_id !== $lockedBooking->umrah_package_id
            ) {
                throw ValidationException::withMessages([
                    'schedule_id' => 'Jadwal booking sudah tidak valid atau tidak aktif.',
                ]);
            }

            if ($schedule->quota < $lockedBooking->pilgrims_count) {
                throw ValidationException::withMessages([
                    'pilgrims_count' => 'Kuota jadwal tidak mencukupi untuk jumlah jamaah booking ini.',
                ]);
            }

            $remainingQuota = $schedule->quota - $lockedBooking->pilgrims_count;

            $schedule->update([
                'quota' => $remainingQuota,
                'status' => Schedule::statusForQuota($remainingQuota),
            ]);

            $lockedBooking->update([
                'status' => Booking::STATUS_APPROVED,
                'admin_notes' => $adminNotes,
                'rejection_reason' => null,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'approved_at' => now(),
                'quota_deducted_at' => now(),
                'quota_restored_at' => null,
            ]);

            return $lockedBooking->refresh();
        }, 3);
    }

    public function reject(Booking $booking, User $reviewer, string $reason, ?string $adminNotes = null): Booking
    {
        return DB::transaction(function () use ($booking, $reviewer, $reason, $adminNotes): Booking {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->getKey());

            if ($lockedBooking->status !== Booking::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya booking berstatus menunggu review yang dapat ditolak.',
                ]);
            }

            $lockedBooking->update([
                'status' => Booking::STATUS_REJECTED,
                'admin_notes' => $adminNotes,
                'rejection_reason' => $reason,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
            ]);

            return $lockedBooking->refresh();
        }, 3);
    }

    public function cancel(Booking $booking, User $reviewer, ?string $adminNotes = null): Booking
    {
        return DB::transaction(function () use ($booking, $reviewer, $adminNotes): Booking {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->getKey());

            if (! in_array($lockedBooking->status, [Booking::STATUS_PENDING, Booking::STATUS_APPROVED], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Booking ini tidak dapat dibatalkan.',
                ]);
            }

            if (
                $lockedBooking->status === Booking::STATUS_APPROVED
                && $lockedBooking->quota_deducted_at !== null
                && $lockedBooking->quota_restored_at === null
            ) {
                $schedule = Schedule::query()->lockForUpdate()->findOrFail($lockedBooking->schedule_id);
                $restoredQuota = $schedule->quota + $lockedBooking->pilgrims_count;

                $schedule->update([
                    'quota' => $restoredQuota,
                    'status' => Schedule::statusForQuota($restoredQuota),
                ]);

                $lockedBooking->quota_restored_at = now();
            }

            $lockedBooking->fill([
                'status' => Booking::STATUS_CANCELLED,
                'admin_notes' => $adminNotes,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
            ])->save();

            return $lockedBooking->refresh();
        }, 3);
    }
}
