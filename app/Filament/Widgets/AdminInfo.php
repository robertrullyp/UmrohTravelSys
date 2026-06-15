<?php

namespace App\Filament\Widgets;

use App\Models\Contact;
use App\Models\Schedule;
use Filament\Widgets\Widget;

class AdminInfo extends Widget
{
    protected string $view = 'filament.widgets.admin-info';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 4;

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'contact' => Contact::query()->where('is_active', true)->first(),
            'nextSchedule' => Schedule::query()
                ->with('umrahPackage')
                ->where('is_active', true)
                ->orderBy('departure_date')
                ->first(),
        ];
    }
}
