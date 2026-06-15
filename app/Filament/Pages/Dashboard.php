<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminInfo;
use App\Filament\Widgets\ContentChart;
use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\VisitorChart;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    public function getTitle(): string|Htmlable
    {
        return 'Dashboard';
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 2,
        ];
    }

    /**
     * @return array<class-string|WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            DashboardStats::class,
            VisitorChart::make([
                'columnSpan' => [
                    'default' => 1,
                    'lg' => 2,
                ],
            ]),
            ContentChart::make([
                'columnSpan' => [
                    'default' => 1,
                    'lg' => 1,
                ],
            ]),
            AdminInfo::make([
                'columnSpan' => [
                    'default' => 1,
                    'lg' => 1,
                ],
            ]),
        ];
    }
}
