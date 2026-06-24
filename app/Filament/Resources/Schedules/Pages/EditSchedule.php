<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Pages\Concerns\RedirectsToViewOrIndexAfterSave;
use App\Filament\Resources\Schedules\ScheduleResource;
use App\Models\Schedule;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSchedule extends EditRecord
{
    use RedirectsToViewOrIndexAfterSave;

    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $capacity = max(0, (int) ($data['capacity'] ?? 0));
        $available = max(0, (int) ($data['quota'] ?? 0));

        $data['capacity'] = $capacity;
        $data['quota'] = min($available, $capacity);
        $data['status'] = Schedule::statusForQuota((int) $data['quota']);

        return $data;
    }
}
