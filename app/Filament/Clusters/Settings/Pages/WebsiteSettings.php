<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Models\SiteSetting;
use App\Services\SiteImageOptimizer;
use App\Services\WhatsAppGatewayService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Throwable;

class WebsiteSettings extends Page
{
    protected static ?string $cluster = Settings::class;

    protected static ?string $slug = 'website';

    protected static ?string $title = 'Pengaturan Website';

    protected static ?string $navigationLabel = 'Pengaturan Website';

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
                Section::make('Logo & Ikon')
                    ->description('Identitas visual yang terlihat di website publik dan panel admin.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('brand_logo_path')
                            ->label('Logo Brand')
                            ->helperText('Tampil di header/footer publik, login, dan sidebar admin. Gunakan logo jelas dengan latar transparan bila ada.')
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
                            ->helperText('Ikon kecil pada tab browser. Gunakan gambar persegi agar tidak gepeng.')
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
                Section::make('Hero Beranda')
                    ->description('Bagian paling atas yang pertama dilihat pengunjung di halaman beranda.')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('hero_image_path')
                            ->label('Gambar Hero Beranda')
                            ->helperText('Tampil sebagai gambar besar di bagian atas beranda. Pakai foto lebar dan terang.')
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
                            ->columnSpan(['default' => 'full', 'lg' => 1]),
                        Grid::make(1)
                            ->schema([
                                TextInput::make('hero_title_highlight')
                                    ->label('Highlight Judul')
                                    ->helperText('Teks kecil berwarna pink sebelum judul utama.')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('hero_title')
                                    ->label('Judul Utama')
                                    ->helperText('Judul besar pada hero beranda.')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columnSpan(['default' => 'full', 'lg' => 1]),
                        Textarea::make('hero_subtitle')
                            ->label('Deskripsi Hero')
                            ->helperText('Kalimat pendek di bawah judul. Usahakan jelas dalam 1-2 baris.')
                            ->required()
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
                Section::make('Tombol Hubungi Kami')
                    ->description('Nomor tujuan untuk tombol WhatsApp utama di website publik.')
                    ->schema([
                        TextInput::make('cta_whatsapp')
                            ->label('Nomor WhatsApp CTA')
                            ->helperText('Boleh diawali 08 atau 62. Sistem akan membuat link WhatsApp otomatis.')
                            ->required()
                            ->maxLength(32)
                            ->regex('/^[0-9+()\s-]{8,32}$/'),
                    ]),
                Section::make('WhatsApp Gateway & OTP Admin')
                    ->description('Koneksi gateway untuk OTP login admin dan notifikasi booking baru.')
                    ->collapsed()
                    ->headerActions([
                        Action::make('testWhatsAppGateway')
                            ->label('Test Kirim WhatsApp')
                            ->icon(Heroicon::OutlinedPaperAirplane)
                            ->color('success')
                            ->visible(fn (): bool => auth()->user()?->can('settings.update') ?? false)
                            ->modalHeading('Test Kirim WhatsApp')
                            ->modalDescription('Gunakan setelah pengaturan gateway disimpan.')
                            ->modalSubmitActionLabel('Kirim Test')
                            ->schema([
                                TextInput::make('test_whatsapp')
                                    ->label('Nomor WhatsApp Tujuan')
                                    ->helperText('Boleh diawali 08 atau 62. Pesan test dikirim memakai konfigurasi gateway yang sudah tersimpan.')
                                    ->placeholder('082252239507')
                                    ->tel()
                                    ->required()
                                    ->maxLength(32)
                                    ->regex('/^[0-9+()\s-]{8,32}$/'),
                            ])
                            ->action(fn (array $data): null => $this->sendWhatsAppGatewayTest($data)),
                    ])
                    ->columns(2)
                    ->schema([
                        Toggle::make('wa_gateway_enabled')
                            ->label('Aktifkan Gateway WhatsApp')
                            ->helperText('Aktifkan setelah URL endpoint dan auth gateway benar.')
                            ->inline(false),
                        Toggle::make('admin_otp_enabled')
                            ->label('Wajibkan OTP Login Admin')
                            ->helperText('Jika aktif, admin harus punya nomor telepon dan menerima OTP WhatsApp sebelum masuk.')
                            ->inline(false),
                        TextInput::make('wa_gateway_post_url')
                            ->label('URL POST Send WhatsApp')
                            ->helperText('URL dari DRNet Gateway, contoh: https://host/ext/secret/wa. Kosongkan jika tidak ingin mengganti nilai yang tersimpan.')
                            ->placeholder(fn (): string => SiteSetting::hasEncryptedValue('wa_gateway_post_url') ? 'URL gateway sudah tersimpan' : 'https://gateway.example/ext/secret/wa')
                            ->password()
                            ->revealable()
                            ->required(fn (Get $get): bool => (bool) $get('wa_gateway_enabled') && ! SiteSetting::hasEncryptedValue('wa_gateway_post_url'))
                            ->maxLength(2048)
                            ->columnSpanFull(),
                        Select::make('wa_gateway_auth_mode')
                            ->label('Mode Auth Gateway')
                            ->options([
                                'none' => 'None',
                                'basic' => 'Basic Auth',
                                'header' => 'Custom Header',
                                'bearer' => 'Bearer Token',
                                'jwt_static' => 'JWT Static Token',
                            ])
                            ->default('none')
                            ->native(false)
                            ->required(),
                        TextInput::make('wa_gateway_basic_username')
                            ->label('Basic Username')
                            ->helperText('Dipakai hanya bila mode auth Basic.')
                            ->maxLength(255),
                        TextInput::make('wa_gateway_basic_password')
                            ->label('Basic Password')
                            ->helperText('Kosongkan jika tidak ingin mengganti password tersimpan.')
                            ->placeholder(fn (): string => SiteSetting::hasEncryptedValue('wa_gateway_basic_password') ? 'Password sudah tersimpan' : '')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        TextInput::make('wa_gateway_header_name')
                            ->label('Nama Header')
                            ->helperText('Dipakai hanya bila mode auth Custom Header.')
                            ->placeholder('X-API-Key')
                            ->maxLength(255),
                        TextInput::make('wa_gateway_header_value')
                            ->label('Nilai Header / API Key')
                            ->helperText('Kosongkan jika tidak ingin mengganti API key tersimpan.')
                            ->placeholder(fn (): string => SiteSetting::hasEncryptedValue('wa_gateway_header_value') ? 'API key sudah tersimpan' : '')
                            ->password()
                            ->revealable()
                            ->maxLength(1024),
                        TextInput::make('wa_gateway_bearer_token')
                            ->label('Bearer / JWT Static Token')
                            ->helperText('Dipakai hanya bila mode auth Bearer atau JWT Static.')
                            ->placeholder(fn (): string => SiteSetting::hasEncryptedValue('wa_gateway_bearer_token') ? 'Token sudah tersimpan' : '')
                            ->password()
                            ->revealable()
                            ->maxLength(2048)
                            ->columnSpanFull(),
                        TextInput::make('admin_otp_expires_minutes')
                            ->label('Masa Berlaku OTP')
                            ->helperText('Dalam menit. Default 5 menit.')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->required(),
                        TextInput::make('admin_otp_resend_interval_seconds')
                            ->label('Jeda Kirim Ulang OTP')
                            ->helperText('Dalam detik. Default 60 detik.')
                            ->numeric()
                            ->minValue(30)
                            ->maxValue(600)
                            ->required(),
                        TextInput::make('booking_followup_link_expires_minutes')
                            ->label('Masa Berlaku Link Tindak Lanjut')
                            ->helperText('Dalam menit. Default 1440 menit atau 24 jam.')
                            ->numeric()
                            ->minValue(15)
                            ->maxValue(10080)
                            ->required(),
                        TextInput::make('booking_followup_otp_expires_minutes')
                            ->label('Masa Berlaku OTP Tindak Lanjut')
                            ->helperText('Dalam menit. Default 60 menit. Kode ini wajib saat admin submit aksi dari link WhatsApp.')
                            ->numeric()
                            ->minValue(5)
                            ->maxValue(1440)
                            ->required(),
                    ]),
                Section::make('SEO Default')
                    ->description('Cadangan untuk judul Google dan preview link bila halaman belum punya pengaturan khusus.')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextInput::make('seo_site_name')
                            ->label('Nama Situs')
                            ->helperText('Nama brand yang muncul pada metadata website.')
                            ->required()
                            ->maxLength(70),
                        TextInput::make('seo_default_title')
                            ->label('Judul Google Default')
                            ->helperText('Judul cadangan untuk Google. Maksimal 70 karakter.')
                            ->required()
                            ->maxLength(70),
                        Textarea::make('seo_default_description')
                            ->label('Deskripsi Google Default')
                            ->helperText('Ringkasan cadangan untuk Google. Maksimal 170 karakter.')
                            ->required()
                            ->rows(2)
                            ->maxLength(170)
                            ->columnSpanFull(),
                        FileUpload::make('seo_default_image_path')
                            ->label('Gambar Preview Link Default')
                            ->helperText('Untuk preview link WhatsApp/media sosial. Tidak mengganti gambar hero atau gambar paket.')
                            ->disk('site_public')
                            ->directory('images/site/uploads')
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('1200:630')
                            ->orientImagesFromExif()
                            ->automaticallyResizeImagesMode('cover')
                            ->automaticallyResizeImagesToWidth('1200')
                            ->automaticallyResizeImagesToHeight('630')
                            ->imageResizeUpscale(false)
                            ->maxSize(4096)
                            ->columnSpan(['default' => 'full', 'lg' => 1]),
                        TextInput::make('google_site_verification')
                            ->label('Token Verifikasi Google')
                            ->helperText('Isi kode dari Google Search Console saja, bukan seluruh tag HTML.')
                            ->maxLength(255)
                            ->regex('/^[A-Za-z0-9_-]+$/')
                            ->columnSpan(['default' => 'full', 'lg' => 1]),
                    ]),
                ...$this->seoPageSections(),
                Section::make('Info Teknis')
                    ->description('Ringkasan nilai internal. Hanya untuk pengecekan oleh super-admin.')
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
            $value = $this->normalizeSettingValue($data[$key] ?? null, $key);

            if ($this->isEncryptedSetting($key)) {
                if ($value !== '') {
                    SiteSetting::setEncryptedValue($key, $value);
                }

                continue;
            }

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
     * @param  array<string, mixed>  $data
     */
    public function sendWhatsAppGatewayTest(array $data): null
    {
        abort_unless(auth()->user()?->can('settings.update'), 403);

        $number = trim((string) ($data['test_whatsapp'] ?? ''));

        validator(
            ['test_whatsapp' => $number],
            ['test_whatsapp' => ['required', 'string', 'max:32', 'regex:/^[0-9+()\s-]{8,32}$/']],
            ['test_whatsapp.regex' => 'Format nomor WhatsApp tidak valid.'],
        )->validate();

        try {
            app(WhatsAppGatewayService::class)->sendText(
                $number,
                'Tes WhatsApp Gateway PT Amara Al Medina Travel berhasil dikirim pada '.now()->translatedFormat('d F Y H:i').'.',
                'wa-gateway-test-'.auth()->id().'-'.now()->timestamp,
            );

            Notification::make()
                ->success()
                ->title('Pesan test berhasil dikirim.')
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->danger()
                ->title('Pesan test gagal dikirim.')
                ->body($this->whatsAppGatewayTestErrorMessage($exception))
                ->send();
        }

        return null;
    }

    private function whatsAppGatewayTestErrorMessage(Throwable $exception): string
    {
        $safeMessages = [
            'Gateway WhatsApp belum diaktifkan.',
            'URL gateway WhatsApp belum diisi.',
            'Nomor WhatsApp tujuan tidak valid.',
            'Gateway WhatsApp menolak request.',
            'Gateway WhatsApp gagal mengirim pesan.',
        ];

        if (in_array($exception->getMessage(), $safeMessages, true)) {
            return $exception->getMessage();
        }

        return 'Koneksi gateway gagal. Periksa status gateway, URL, dan auth yang tersimpan.';
    }

    /**
     * @return array<string, string>
     */
    private function settingsState(): array
    {
        $state = [
            'brand_logo_path' => SiteSetting::getValue('brand_logo_path', 'images/site/logo.png'),
            'favicon_path' => SiteSetting::getValue('favicon_path', 'images/site/logo.png'),
            'hero_image_path' => SiteSetting::getValue('hero_image_path', 'images/site/beranda-img.jpg'),
            'hero_title_highlight' => SiteSetting::getValue('hero_title_highlight', 'Perjalanan Ibadah Umrah'),
            'hero_title' => SiteSetting::getValue('hero_title', 'Nyaman, Aman & Terpercaya'),
            'hero_subtitle' => SiteSetting::getValue('hero_subtitle', 'PT. Amara Al Medina Travel siap menjadi mitra perjalanan ibadah terbaik Anda dengan pelayanan profesional dan amanah.'),
            'cta_whatsapp' => SiteSetting::getValue('cta_whatsapp', '082252239507'),
        ];

        foreach ($this->seoSettingDefaults() as $key => $default) {
            $state[$key] = SiteSetting::getValue($key, $default);
        }

        foreach ($this->whatsappGatewaySettingDefaults() as $key => $default) {
            $state[$key] = SiteSetting::getValue($key, $default);
        }

        foreach ($this->encryptedSettings() as $key) {
            $state[$key] = '';
        }

        return $state;
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
            ...array_keys($this->seoSettingDefaults()),
            ...array_keys($this->whatsappGatewaySettingDefaults()),
            ...$this->encryptedSettings(),
        ];
    }

    private function normalizeSettingValue(mixed $value, ?string $key = null): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        $value = (string) $value;

        if ($key === 'google_site_verification' || ($key !== null && str_starts_with($key, 'seo_') && ! str_ends_with($key, '_path'))) {
            $value = strip_tags($value);
        }

        return trim($value);
    }

    private function optimizeUploadedAsset(string $key, string $value): void
    {
        $profile = match ($key) {
            'brand_logo_path' => 'logo',
            'favicon_path' => 'favicon',
            'seo_default_image_path' => 'social',
            default => str_starts_with($key, 'seo_') && str_ends_with($key, '_image_path') ? 'social' : null,
        };

        if (! $profile || $value === '') {
            return;
        }

        app(SiteImageOptimizer::class)->optimizePublicUpload($value, $profile);
    }

    private function displaySettingValue(string $key): string
    {
        return $this->normalizeSettingValue(data_get($this->data, $key), $key);
    }

    /**
     * @return array<string, string>
     */
    private function whatsappGatewaySettingDefaults(): array
    {
        return [
            'wa_gateway_enabled' => '0',
            'wa_gateway_auth_mode' => 'none',
            'wa_gateway_basic_username' => '',
            'wa_gateway_header_name' => 'X-API-Key',
            'admin_otp_enabled' => '0',
            'admin_otp_expires_minutes' => '5',
            'admin_otp_resend_interval_seconds' => '60',
            'booking_followup_link_expires_minutes' => '1440',
            'booking_followup_otp_expires_minutes' => '60',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function encryptedSettings(): array
    {
        return [
            'wa_gateway_post_url',
            'wa_gateway_basic_password',
            'wa_gateway_header_value',
            'wa_gateway_bearer_token',
        ];
    }

    private function isEncryptedSetting(string $key): bool
    {
        return in_array($key, $this->encryptedSettings(), true);
    }

    /**
     * @return array<string, string>
     */
    private function seoSettingDefaults(): array
    {
        $defaults = [
            'seo_site_name' => 'PT Amara Al Medina Travel',
            'seo_default_title' => 'PT Amara Al Medina Travel - Travel Umrah Terpercaya',
            'seo_default_description' => 'Informasi paket umrah, jadwal keberangkatan, galeri, profil, dan kontak PT Amara Al Medina Travel.',
            'seo_default_image_path' => 'images/site/beranda-img.jpg',
            'google_site_verification' => '',
        ];

        foreach ($this->seoPageDefaults() as $page => $pageDefaults) {
            $defaults["seo_{$page}_title"] = $pageDefaults['title'];
            $defaults["seo_{$page}_description"] = $pageDefaults['description'];
            $defaults["seo_{$page}_image_path"] = '';
        }

        return $defaults;
    }

    /**
     * @return array<string, array{title: string, description: string}>
     */
    private function seoPageDefaults(): array
    {
        return [
            'home' => ['title' => 'PT Amara Al Medina Travel - Travel Umrah Terpercaya', 'description' => 'Paket dan jadwal umrah bersama PT Amara Al Medina Travel dengan pelayanan profesional dan amanah.'],
            'profile' => ['title' => 'Profil - PT Amara Al Medina Travel', 'description' => 'Profil, visi, misi, dan komitmen pelayanan PT Amara Al Medina Travel.'],
            'packages' => ['title' => 'Paket Umrah - PT Amara Al Medina Travel', 'description' => 'Pilihan paket umrah dengan fasilitas, harga, dan jadwal keberangkatan yang jelas.'],
            'schedules' => ['title' => 'Jadwal Keberangkatan - PT Amara Al Medina Travel', 'description' => 'Jadwal keberangkatan dan ketersediaan kuota paket umrah PT Amara Al Medina Travel.'],
            'galleries' => ['title' => 'Galeri Kegiatan - PT Amara Al Medina Travel', 'description' => 'Dokumentasi kegiatan jamaah bersama PT Amara Al Medina Travel.'],
            'contact' => ['title' => 'Kontak - PT Amara Al Medina Travel', 'description' => 'Alamat, WhatsApp, email, dan lokasi PT Amara Al Medina Travel.'],
            'booking' => ['title' => 'Booking Umrah - PT Amara Al Medina Travel', 'description' => 'Ajukan booking paket umrah PT Amara Al Medina Travel secara online.'],
        ];
    }

    /**
     * @return array<int, Section>
     */
    private function seoPageSections(): array
    {
        return collect(SiteSetting::SEO_PAGES)
            ->map(function (string $label, string $page): Section {
                return Section::make('SEO Halaman - '.$label)
                    ->description('Judul Google, deskripsi, dan gambar preview khusus halaman '.strtolower($label).'.')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        TextInput::make("seo_{$page}_title")
                            ->label('Judul Google')
                            ->helperText('Maksimal 70 karakter dan tanpa HTML.')
                            ->required()
                            ->maxLength(70),
                        Textarea::make("seo_{$page}_description")
                            ->label('Deskripsi Google')
                            ->helperText('Ringkasan halaman untuk Google. Maksimal 170 karakter.')
                            ->required()
                            ->rows(2)
                            ->maxLength(170),
                        FileUpload::make("seo_{$page}_image_path")
                            ->label('Gambar Preview Link')
                            ->helperText('Opsional untuk preview link halaman ini. Jika kosong, sistem memakai gambar default.')
                            ->disk('site_public')
                            ->directory('images/site/uploads')
                            ->image()
                            ->imageEditor()
                            ->imageCropAspectRatio('1200:630')
                            ->orientImagesFromExif()
                            ->automaticallyResizeImagesMode('cover')
                            ->automaticallyResizeImagesToWidth('1200')
                            ->automaticallyResizeImagesToHeight('630')
                            ->imageResizeUpscale(false)
                            ->maxSize(4096)
                            ->columnSpanFull(),
                    ]);
            })
            ->values()
            ->all();
    }
}
