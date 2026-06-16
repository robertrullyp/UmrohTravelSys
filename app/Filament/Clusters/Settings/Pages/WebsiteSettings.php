<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Models\SiteSetting;
use App\Services\SiteImageOptimizer;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class WebsiteSettings extends Page
{
    protected static ?string $cluster = Settings::class;
    protected static ?string $slug = 'website';
    protected static ?string $title = 'Website Settings';
    protected static ?string $navigationLabel = 'Website Settings';
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPaintBrush;
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.clusters.settings.pages.website-settings';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->settingsState());
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('settings.view') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Branding')
                    ->description('Atur identitas visual utama yang dipakai di website publik dan panel admin.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('brand_logo_path')
                            ->label('Logo Brand')
                            ->helperText('Digunakan untuk header, footer, login, dan sidebar admin. File otomatis dibatasi maks. 640x360 dan dikompresi.')
                            ->disk('site_public')
                            ->directory('images/site/uploads')
                            ->image()
                            ->imageEditor()
                            ->orientImagesFromExif()
                            ->automaticallyResizeImagesMode('contain')
                            ->automaticallyResizeImagesToWidth('640')
                            ->automaticallyResizeImagesToHeight('360')
                            ->imageResizeUpscale(false)
                            ->required()
                            ->maxSize(2048)
                            ->previewable()
                            ->openable()
                            ->downloadable()
                            ->columnSpan(1),
                        FileUpload::make('favicon_path')
                            ->label('Favicon')
                            ->helperText('Gambar akan dipotong persegi, dibatasi maks. 512x512, dan dikompresi.')
                            ->disk('site_public')
                            ->directory('images/site/uploads')
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->orientImagesFromExif()
                            ->automaticallyResizeImagesMode('cover')
                            ->automaticallyResizeImagesToWidth('512')
                            ->automaticallyResizeImagesToHeight('512')
                            ->imageResizeUpscale(false)
                            ->required()
                            ->maxSize(1024)
                            ->previewable()
                            ->openable()
                            ->downloadable()
                            ->columnSpan(1),
                    ]),
                Section::make('Beranda')
                    ->description('Konten utama hero pada halaman beranda.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('hero_image_path')
                            ->label('Gambar Hero Beranda')
                            ->helperText('Gambar lebar akan tampil paling baik untuk hero beranda.')
                            ->disk('site_public')
                            ->directory('images/site/uploads')
                            ->image()
                            ->imageEditor()
                            ->orientImagesFromExif()
                            ->required()
                            ->maxSize(4096)
                            ->previewable()
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull(),
                        TextInput::make('hero_title_highlight')
                            ->label('Highlight Judul')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('hero_title')
                            ->label('Judul Utama')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('hero_subtitle')
                            ->label('Deskripsi Hero')
                            ->required()
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
                Section::make('Kontak / CTA')
                    ->description('Nomor ini dipakai untuk tombol Hubungi Kami di header publik.')
                    ->schema([
                        TextInput::make('cta_whatsapp')
                            ->label('Nomor WhatsApp CTA')
                            ->helperText('Format boleh 08... atau 62..., sistem akan menyesuaikan link WhatsApp.')
                            ->required()
                            ->maxLength(32)
                            ->regex('/^[0-9+()\s-]{8,32}$/'),
                    ]),
                Section::make('Advanced')
                    ->description('Nilai teknis key-value. Hanya gunakan jika perlu troubleshooting.')
                    ->collapsed()
                    ->visible(fn (): bool => auth()->user()?->hasRole('super-admin') ?? false)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('advanced_brand_logo_path')
                                    ->label('brand_logo_path')
                                    ->content(fn (): string => $this->displaySettingValue('brand_logo_path')),
                                Placeholder::make('advanced_favicon_path')
                                    ->label('favicon_path')
                                    ->content(fn (): string => $this->displaySettingValue('favicon_path')),
                                Placeholder::make('advanced_hero_image_path')
                                    ->label('hero_image_path')
                                    ->content(fn (): string => $this->displaySettingValue('hero_image_path')),
                                Placeholder::make('advanced_cta_whatsapp')
                                    ->label('cta_whatsapp')
                                    ->content(fn (): string => $this->displaySettingValue('cta_whatsapp')),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('website-settings-form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Simpan Pengaturan')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('settings.update'), 403);

        $data = $this->form->getState();

        foreach ($this->settingsKeys() as $key) {
            $value = $this->normalizeSettingValue($data[$key] ?? null);

            if ($value === '' && $this->isAssetSetting($key)) {
                $value = SiteSetting::getValue($key, $this->settingDefaults()[$key]);
            }

            $this->optimizeUploadedAsset($key, $value);

            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }

        Notification::make()
            ->success()
            ->title('Pengaturan website disimpan.')
            ->send();

        $this->form->fill($this->settingsState());
    }

    /**
     * @return array<string, string>
     */
    private function settingsState(): array
    {
        return [
            'brand_logo_path' => SiteSetting::getValue('brand_logo_path', 'images/site/logo.png'),
            'favicon_path' => SiteSetting::getValue('favicon_path', 'images/site/logo.png'),
            'hero_image_path' => SiteSetting::getValue('hero_image_path', 'images/site/beranda-img.jpg'),
            'hero_title_highlight' => SiteSetting::getValue('hero_title_highlight', 'Perjalanan Ibadah Umrah'),
            'hero_title' => SiteSetting::getValue('hero_title', 'Nyaman, Aman & Terpercaya'),
            'hero_subtitle' => SiteSetting::getValue('hero_subtitle', 'PT. Amara Al Medina Travel siap menjadi mitra perjalanan ibadah terbaik Anda dengan pelayanan profesional dan amanah.'),
            'cta_whatsapp' => SiteSetting::getValue('cta_whatsapp', '082252239507'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function settingDefaults(): array
    {
        return [
            'brand_logo_path' => 'images/site/logo.png',
            'favicon_path' => 'images/site/logo.png',
            'hero_image_path' => 'images/site/beranda-img.jpg',
        ];
    }

    private function isAssetSetting(string $key): bool
    {
        return array_key_exists($key, $this->settingDefaults());
    }

    /**
     * @return array<int, string>
     */
    private function settingsKeys(): array
    {
        return [
            'brand_logo_path',
            'favicon_path',
            'hero_image_path',
            'hero_title_highlight',
            'hero_title',
            'hero_subtitle',
            'cta_whatsapp',
        ];
    }

    private function normalizeSettingValue(mixed $value): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        return trim((string) $value);
    }

    private function optimizeUploadedAsset(string $key, string $value): void
    {
        $profile = match ($key) {
            'brand_logo_path' => 'logo',
            'favicon_path' => 'favicon',
            default => null,
        };

        if (! $profile || $value === '') {
            return;
        }

        app(SiteImageOptimizer::class)->optimizePublicUpload($value, $profile);
    }

    private function displaySettingValue(string $key): string
    {
        return $this->normalizeSettingValue(data_get($this->data, $key));
    }
}
