<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kontak')
                    ->columns(2)
                    ->schema([
                        Textarea::make('address')
                            ->label('Alamat')
                            ->required()
                            ->rows(4)
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
                            ->url()
                            ->columnSpanFull(),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric(),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric(),
                        Toggle::make('is_active')
                            ->label('Tampilkan')
                            ->default(true),
                    ]),
            ]);
    }
}
