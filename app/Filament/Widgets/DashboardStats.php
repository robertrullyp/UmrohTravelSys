<?php

namespace App\Filament\Widgets;

use App\Models\Contact;
use App\Models\Gallery;
use App\Models\Schedule;
use App\Models\UmrahPackage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Paket Umrah', UmrahPackage::query()->count())
                ->description('Total paket')
                ->icon('heroicon-o-rectangle-stack')
                ->color('primary'),
            Stat::make('Jadwal', Schedule::query()->count())
                ->description('Total jadwal')
                ->icon('heroicon-o-calendar-days')
                ->color('success'),
            Stat::make('Galeri', Gallery::query()->count())
                ->description('Total foto')
                ->icon('heroicon-o-photo')
                ->color('info'),
            Stat::make('Kontak', Contact::query()->count())
                ->description('Informasi kontak')
                ->icon('heroicon-o-phone')
                ->color('warning'),
        ];
    }
}
