<?php

namespace App\Filament\Resources\Bookings\Tables;

use App\Filament\Resources\Bookings\BookingResource;
use App\Models\Booking;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_number')
                    ->label('Nomor')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('customer_name')->label('Pemesan')->searchable(),
                TextColumn::make('whatsapp')->label('WhatsApp')->searchable()->visibleFrom('lg')->toggleable(),
                TextColumn::make('umrahPackage.name')->label('Paket')->searchable()->limit(28)->visibleFrom('md'),
                TextColumn::make('schedule.departure_date')->label('Berangkat')->date('d M Y')->sortable(),
                TextColumn::make('pilgrims_count')->label('Jamaah')->suffix(' orang')->sortable()->visibleFrom('md'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Booking::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Booking::STATUS_APPROVED => 'success',
                        Booking::STATUS_REJECTED => 'danger',
                        Booking::STATUS_CANCELLED => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')->label('Diajukan')->dateTime('d M Y H:i')->sortable()->visibleFrom('xl')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Booking::STATUSES),
            ])
            ->recordActions(
                ActionGroup::make([
                    ViewAction::make()->label('Detail'),
                    EditAction::make()
                        ->label('Edit')
                        ->visible(fn (Booking $record): bool => BookingResource::canEdit($record)),
                    BookingResource::approveAction(),
                    BookingResource::rejectAction(),
                    BookingResource::cancelAction(),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->button()
                    ->color('gray'),
                position: RecordActionsPosition::BeforeColumns,
            )
            ->stackedOnMobile()
            ->defaultSort('created_at', 'desc');
    }
}
