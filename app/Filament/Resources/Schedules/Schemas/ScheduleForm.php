<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Jadwal Keberangkatan')
                    ->columns(2)
                    ->schema([
                        Select::make('umrah_package_id')
                            ->label('Paket')
                            ->relationship('umrahPackage', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('departure_date')
                            ->label('Tanggal Keberangkatan')
                            ->required(),
                        TextInput::make('quota')
                            ->label('Kuota')
                            ->numeric()
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Tersedia' => 'Tersedia',
                                'Hampir Penuh' => 'Hampir Penuh',
                                'Penuh' => 'Penuh',
                            ])
                            ->default('Tersedia')
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Tampilkan')
                            ->default(true),
                    ]),
            ]);
    }
}
