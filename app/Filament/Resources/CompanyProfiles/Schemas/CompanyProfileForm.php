<?php

namespace App\Filament\Resources\CompanyProfiles\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil Perusahaan')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Nama Perusahaan')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        Toggle::make('is_active')
                            ->label('Tampilkan')
                            ->default(true),
                        Textarea::make('about')
                            ->label('Tentang Perusahaan')
                            ->required()
                            ->rows(3)
                            ->columnSpan(2),
                        FileUpload::make('photo_path')
                            ->label('Foto')
                            ->image()
                            ->imagePreviewHeight('120')
                            ->disk('public')
                            ->directory('profiles'),
                        Textarea::make('vision')
                            ->label('Visi')
                            ->required()
                            ->rows(2),
                        TagsInput::make('missions')
                            ->label('Misi')
                            ->placeholder('Tambah misi')
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
