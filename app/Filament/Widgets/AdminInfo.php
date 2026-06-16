<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
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
            'pendingCount' => Booking::query()->where('status', Booking::STATUS_PENDING)->count(),
            'approvedTodayCount' => Booking::query()
                ->where('status', Booking::STATUS_APPROVED)
                ->whereDate('approved_at', today())
                ->count(),
            'latestBooking' => Booking::query()
                ->with(['umrahPackage', 'schedule'])
                ->latest()
                ->first(),
            'lowQuotaSchedules' => Schedule::query()
                ->with('umrahPackage')
                ->where('is_active', true)
                ->orderBy('departure_date')
                ->whereDate('departure_date', '>=', today())
                ->where('quota', '<=', 5)
                ->limit(4)
                ->get(),
            'bookingIndexUrl' => BookingResource::getUrl('index'),
        ];
    }
}
