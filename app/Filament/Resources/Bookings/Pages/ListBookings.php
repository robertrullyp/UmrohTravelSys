<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua'),
            'pending' => Tab::make('Menunggu')
                ->badge(Booking::query()->where('status', Booking::STATUS_PENDING)->count())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', Booking::STATUS_PENDING)),
            'approved' => Tab::make('Disetujui')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', Booking::STATUS_APPROVED)),
            'rejected' => Tab::make('Ditolak')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', Booking::STATUS_REJECTED)),
            'cancelled' => Tab::make('Dibatalkan')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', Booking::STATUS_CANCELLED)),
        ];
    }
}
