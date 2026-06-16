<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Booking;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ringkasan Booking')
                ->columns(3)
                ->schema([
                    TextEntry::make('booking_number')->label('Nomor Booking')->copyable(),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => Booking::STATUSES[$state] ?? $state)
                        ->color(fn (string $state): string => match ($state) {
                            Booking::STATUS_APPROVED => 'success',
                            Booking::STATUS_REJECTED => 'danger',
                            Booking::STATUS_CANCELLED => 'gray',
                            default => 'warning',
                        }),
                    TextEntry::make('created_at')->label('Diajukan')->dateTime('d F Y, H:i'),
                    TextEntry::make('umrahPackage.name')->label('Paket')->columnSpan(2),
                    TextEntry::make('schedule.departure_date')->label('Keberangkatan')->date('d F Y'),
                    TextEntry::make('customer_name')->label('Nama Pemesan'),
                    TextEntry::make('whatsapp')->label('WhatsApp')->copyable(),
                    TextEntry::make('email')->label('Email')->placeholder('-'),
                    TextEntry::make('pilgrims_count')->label('Jumlah Jamaah')->suffix(' orang'),
                ]),
            Section::make('Review Admin')
                ->columns(2)
                ->schema([
                    TextEntry::make('reviewer.name')->label('Direview Oleh')->placeholder('-'),
                    TextEntry::make('reviewed_at')->label('Waktu Review')->dateTime('d F Y, H:i')->placeholder('-'),
                    TextEntry::make('notes')->label('Catatan Pemesan')->placeholder('-')->columnSpanFull(),
                    TextEntry::make('admin_notes')->label('Catatan Admin')->placeholder('-')->columnSpanFull(),
                    TextEntry::make('rejection_reason')->label('Alasan Penolakan')->placeholder('-')->columnSpanFull(),
                ]),
        ]);
    }
}
