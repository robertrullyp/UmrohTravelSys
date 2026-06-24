<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Pages\Concerns\RedirectsToViewOrIndexAfterSave;
use App\Models\Schedule;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditBooking extends EditRecord
{
    use RedirectsToViewOrIndexAfterSave;

    protected static string $resource = BookingResource::class;

    protected function beforeSave(): void
    {
        $packageId = (int) ($this->data['umrah_package_id'] ?? 0);
        $scheduleId = (int) ($this->data['schedule_id'] ?? 0);
        $pilgrims = (int) ($this->data['pilgrims_count'] ?? 0);
        $schedule = Schedule::query()->find($scheduleId);

        if (
            $schedule === null
            || $schedule->umrah_package_id !== $packageId
            || ! $schedule->is_active
            || $schedule->departure_date->lt(today())
            || $pilgrims < 1
            || $pilgrims > $schedule->quota
        ) {
            throw ValidationException::withMessages([
                'schedule_id' => 'Jadwal harus aktif, sesuai paket, belum lewat, dan memiliki kuota yang cukup.',
            ]);
        }
    }
}
