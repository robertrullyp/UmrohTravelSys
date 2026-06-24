<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactForm
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
                        Section::make('Informasi Kontak')
                            ->description('Data yang tampil di halaman Kontak, footer, dan tombol WhatsApp publik.')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->schema([
                                Textarea::make('address')
                                    ->label('Alamat')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),
                                TextInput::make('whatsapp')
                                    ->label('No WhatsApp')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('instagram')
                                    ->label('Instagram')
                                    ->maxLength(255),
                                TextInput::make('map_embed_url')
                                    ->label('URL Embed Map')
                                    ->url(),
                            ])
                            ->columnSpan([
                                'default' => 'full',
                                'lg' => 8,
                            ]),
                        Section::make('Tampilan Publik')
                            ->description('Atur kontak yang muncul dan posisi peta.')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->schema([
                                TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->numeric(),
                                TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->numeric(),
                                Toggle::make('is_active')
                                    ->label('Tampilkan')
                                    ->default(true)
                                    ->columnSpanFull(),
                                Toggle::make('is_primary')
                                    ->label('Utama')
                                    ->helperText('Kontak utama dipakai untuk footer, tombol WhatsApp, dan peta utama.')
                                    ->default(false)
                                    ->columnSpanFull(),
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
