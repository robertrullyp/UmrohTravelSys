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
                            ->label('Gambar Utama Paket')
                            ->helperText('Muncul di kartu beranda, daftar paket, dan detail paket. Poster/flyer akan tampil utuh di detail paket; thumbnail dapat ter-crop ringan agar grid tetap rapi.')
                            ->image()
                            ->disk('public')
                            ->directory('packages')
                            ->imageEditor()
                            ->orientImagesFromExif()
                            ->imagePreviewHeight('220')
                            ->maxSize(4096)
                            ->previewable()
                            ->openable()
                            ->downloadable()
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
                Section::make('SEO & Google Search')
                    ->description('Kosongkan metadata untuk memakai nama, deskripsi, dan gambar paket sebagai fallback.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('SEO Title')
                            ->maxLength(70)
                            ->helperText('Maksimal 70 karakter dan tanpa HTML.'),
                        Toggle::make('is_indexable')
                            ->label('Izinkan Google mengindeks paket')
                            ->default(true)
                            ->helperText('Paket tetap dapat dibuka saat nonaktif, tetapi dikeluarkan dari sitemap dan diberi noindex.'),
                        Textarea::make('seo_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->maxLength(170)
                            ->helperText('Maksimal 170 karakter dan harus merangkum isi paket.')
                            ->columnSpanFull(),
                        FileUpload::make('seo_image_path')
                            ->label('Social Preview Image')
                            ->helperText('Khusus preview link WhatsApp/media sosial ukuran 1200x630. Ini bukan gambar utama yang tampil di halaman paket.')
                            ->image()
                            ->disk('public')
                            ->directory('packages/seo')
                            ->imageEditor()
                            ->imageCropAspectRatio('1200:630')
                            ->orientImagesFromExif()
                            ->automaticallyResizeImagesMode('cover')
                            ->automaticallyResizeImagesToWidth('1200')
                            ->automaticallyResizeImagesToHeight('630')
                            ->imageResizeUpscale(false)
                            ->maxSize(4096)
                            ->previewable()
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
