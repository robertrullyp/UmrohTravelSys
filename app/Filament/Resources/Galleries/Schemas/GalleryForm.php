<?php

namespace App\Filament\Resources\Galleries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GalleryForm
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
                        Section::make('Foto Galeri')
                            ->description('Foto utama yang tampil di grid galeri publik.')
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Foto')
                                    ->helperText('Tampil di halaman Galeri. Thumbnail publik memakai rasio 4:3, sedangkan preview/lightbox menampilkan foto lebih besar.')
                                    ->image()
                                    ->disk('public')
                                    ->directory('galleries')
                                    ->imageEditor()
                                    ->orientImagesFromExif()
                                    ->imagePreviewHeight('180')
                                    ->maxSize(4096)
                                    ->previewable()
                                    ->openable()
                                    ->downloadable()
                                    ->required(),
                            ])
                            ->columnSpan([
                                'default' => 'full',
                                'lg' => 7,
                            ]),
                        Section::make('Detail Foto')
                            ->description('Judul, tanggal, dan urutan tampil di halaman publik.')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                DatePicker::make('taken_at')
                                    ->label('Tanggal'),
                                TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->default(0),
                                Toggle::make('is_active')
                                    ->label('Tampilkan')
                                    ->default(true)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan([
                                'default' => 'full',
                                'lg' => 5,
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
