<?php

namespace App\Filament\Resources\Galleries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
                        Section::make('Detail Album')
                            ->description('Judul, tanggal, dan urutan album di halaman publik.')
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
                                'lg' => 4,
                            ]),
                        Section::make('Foto Album')
                            ->description('Foto pertama menjadi sampul album di beranda dan halaman Galeri.')
                            ->schema([
                                Repeater::make('photos')
                                    ->label('Foto Album')
                                    ->relationship()
                                    ->schema([
                                        FileUpload::make('image_path')
                                            ->label('Foto')
                                            ->helperText('Thumbnail publik memakai rasio 4:3. Preview popup menampilkan foto lebih besar.')
                                            ->image()
                                            ->disk('public')
                                            ->directory('galleries')
                                            ->imageEditor()
                                            ->orientImagesFromExif()
                                            ->imagePreviewHeight('150')
                                            ->maxSize(4096)
                                            ->previewable()
                                            ->openable()
                                            ->downloadable()
                                            ->required()
                                            ->columnSpan([
                                                'default' => 'full',
                                                'md' => 7,
                                            ]),
                                        TextInput::make('caption')
                                            ->label('Keterangan Foto')
                                            ->helperText('Opsional. Jika kosong, judul album dipakai di popup.')
                                            ->maxLength(255)
                                            ->columnSpan([
                                                'default' => 'full',
                                                'md' => 5,
                                            ]),
                                    ])
                                    ->columns([
                                        'default' => 1,
                                        'md' => 12,
                                    ])
                                    ->minItems(1)
                                    ->defaultItems(1)
                                    ->addActionLabel('Tambah Foto')
                                    ->itemLabel(fn (array $state): string => filled($state['caption'] ?? null) ? $state['caption'] : 'Foto Album')
                                    ->orderColumn('sort_order')
                                    ->reorderableWithButtons()
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan([
                                'default' => 'full',
                                'lg' => 8,
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
