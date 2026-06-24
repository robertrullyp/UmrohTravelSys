<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Models\Booking;
use App\Models\Schedule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Booking')
                ->columns(2)
                ->schema([
                    TextInput::make('booking_number')
                        ->label('Nomor Booking')
                        ->disabled()
                        ->dehydrated(false),
                    Select::make('status')
                        ->label('Status')
                        ->options(Booking::STATUSES)
                        ->disabled()
                        ->dehydrated(false),
                    Select::make('umrah_package_id')
                        ->label('Paket Umrah')
                        ->relationship('umrahPackage', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('schedule_id')
                        ->label('Jadwal')
                        ->relationship('schedule', 'departure_date')
                        ->getOptionLabelFromRecordUsing(
                            fn (Schedule $record): string => $record->departure_date->translatedFormat('d F Y')
                                ." ({$record->quota} kursi)",
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('customer_name')
                        ->label('Nama Pemesan')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('whatsapp')
                        ->label('WhatsApp')
                        ->required()
                        ->maxLength(32),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('pilgrims_count')
                        ->label('Jumlah Jamaah')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    Textarea::make('notes')
                        ->label('Catatan Pemesan')
                        ->rows(3)
                        ->columnSpanFull(),
                    Textarea::make('admin_notes')
                        ->label('Catatan Admin')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
