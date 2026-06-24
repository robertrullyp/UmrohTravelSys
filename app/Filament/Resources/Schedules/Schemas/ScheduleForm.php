<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'lg' => 12,
                ])
                    ->schema([
                        Section::make('Jadwal Keberangkatan')
                            ->description('Tanggal, paket, dan kuota yang tampil di halaman jadwal publik.')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                                'xl' => 3,
                            ])
                            ->schema([
                                Select::make('umrah_package_id')
                                    ->label('Paket')
                                    ->relationship('umrahPackage', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull(),
                                DatePicker::make('departure_date')
                                    ->label('Tanggal Keberangkatan')
                                    ->required(),
                                TextInput::make('capacity')
                                    ->label('Kuota')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),
                                TextInput::make('quota')
                                    ->label('Tersedia')
                                    ->helperText('Saat tambah, kosongkan agar sama dengan Kuota.')
                                    ->numeric()
                                    ->minValue(0),
                                Textarea::make('notes')
                                    ->label('Catatan')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan([
                                'default' => 'full',
                                'lg' => 8,
                            ]),
                        Section::make('Status Publik')
                            ->description('Status dihitung otomatis dari sisa kuota.')
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'Tersedia' => 'Tersedia',
                                        'Hampir Penuh' => 'Hampir Penuh',
                                        'Penuh' => 'Penuh',
                                    ])
                                    ->default('Tersedia')
                                    ->disabled()
                                    ->dehydrated(false),
                                Toggle::make('is_active')
                                    ->label('Tampilkan')
                                    ->default(true),
                            ])
                            ->columnSpan([
                                'default' => 'full',
                                'lg' => 4,
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
