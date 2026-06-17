<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Models\Schedule;
use Filament\Resources\Pages\CreateRecord;

class CreateSchedule extends CreateRecord
{
    protected static string $resource = ScheduleResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $capacity = max(0, (int) ($data['capacity'] ?? 0));
        $available = $data['quota'] ?? null;
        $available = ($available === null || $available === '') ? $capacity : max(0, (int) $available);

        $data['capacity'] = $capacity;
        $data['quota'] = min($available, $capacity);
        $data['status'] = Schedule::statusForQuota((int) $data['quota']);

        return $data;
    }
}
