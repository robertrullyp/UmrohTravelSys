<?php

namespace App\Filament\Resources\UmrahPackages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class UmrahPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Paket')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Paket')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state)))
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        FileUpload::make('image_path')
                            ->label('Gambar')
                            ->image()
                            ->disk('public')
                            ->directory('packages')
                            ->columnSpanFull(),
                        TextInput::make('duration_days')
                            ->label('Durasi Hari')
                            ->numeric()
                            ->required(),
                        TextInput::make('price')
                            ->label('Harga')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        TextInput::make('airline')
                            ->label('Maskapai')
                            ->maxLength(255),
                        TextInput::make('departure_month')
                            ->label('Keberangkatan')
                            ->maxLength(255),
                        TextInput::make('makkah_hotel')
                            ->label('Hotel Makkah')
                            ->maxLength(255),
                        TextInput::make('madinah_hotel')
                            ->label('Hotel Madinah')
                            ->maxLength(255),
                        TagsInput::make('includes')
                            ->label('Fasilitas')
                            ->placeholder('Tambah fasilitas')
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                        Toggle::make('is_featured')
                            ->label('Paket Utama'),
                        Toggle::make('is_active')
                            ->label('Tampilkan')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
