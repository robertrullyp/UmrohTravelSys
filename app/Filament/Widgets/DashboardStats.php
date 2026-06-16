<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Contacts\ContactResource;
use App\Filament\Resources\Galleries\GalleryResource;
use App\Filament\Resources\Schedules\ScheduleResource;
use App\Filament\Resources\UmrahPackages\UmrahPackageResource;
use App\Models\Booking;
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
                ->color('primary')
                ->url(UmrahPackageResource::getUrl('index')),
            Stat::make('Jadwal', Schedule::query()->count())
                ->description('Total jadwal')
                ->icon('heroicon-o-calendar-days')
                ->color('success')
                ->url(ScheduleResource::getUrl('index')),
            Stat::make('Booking', Booking::query()->where('status', Booking::STATUS_PENDING)->count())
                ->description('Menunggu review')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning')
                ->url(BookingResource::getUrl('index')),
            Stat::make('Galeri', Gallery::query()->count())
                ->description('Total foto')
                ->icon('heroicon-o-photo')
                ->color('info')
                ->url(GalleryResource::getUrl('index')),
            Stat::make('Kontak', Contact::query()->count())
                ->description('Informasi kontak')
                ->icon('heroicon-o-phone')
                ->color('warning')
                ->url(ContactResource::getUrl('index')),
        ];
    }
}
