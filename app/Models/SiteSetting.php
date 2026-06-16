<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class SiteSetting extends Model
{
    use HasFactory;

    public const DEFINITIONS = [
        'brand_logo_path' => [
            'label' => 'Logo Brand',
            'group' => 'Branding',
            'helper' => 'Path logo utama. Default aktif: images/site/logo.png.',
            'placeholder' => 'images/site/logo.png',
            'rows' => 2,
        ],
        'favicon_path' => [
            'label' => 'Favicon',
            'group' => 'Branding',
            'helper' => 'Path favicon website dan admin. Gunakan images/site/logo.png agar konsisten.',
            'placeholder' => 'images/site/logo.png',
            'rows' => 2,
        ],
        'hero_image_path' => [
            'label' => 'Gambar Hero Beranda',
            'group' => 'Beranda',
            'helper' => 'Path gambar hero beranda. Default aktif: images/site/beranda-img.jpg.',
            'placeholder' => 'images/site/beranda-img.jpg',
            'rows' => 2,
        ],
        'hero_title_highlight' => [
            'label' => 'Highlight Judul Hero',
            'group' => 'Beranda',
            'helper' => 'Teks pink di baris pertama hero beranda.',
            'placeholder' => 'Perjalanan Ibadah Umrah',
            'rows' => 2,
        ],
        'hero_title' => [
            'label' => 'Judul Hero',
            'group' => 'Beranda',
            'helper' => 'Judul utama hero beranda.',
            'placeholder' => 'Nyaman, Aman & Terpercaya',
            'rows' => 2,
        ],
        'hero_subtitle' => [
            'label' => 'Deskripsi Hero',
            'group' => 'Beranda',
            'helper' => 'Deskripsi singkat di bawah judul hero.',
            'placeholder' => 'PT. Amara Al Medina Travel siap menjadi mitra perjalanan ibadah terbaik Anda.',
            'rows' => 4,
        ],
        'cta_whatsapp' => [
            'label' => 'Nomor CTA WhatsApp',
            'group' => 'Kontak',
            'helper' => 'Nomor WhatsApp untuk tombol Hubungi Kami. Format boleh 08... atau 62....',
            'placeholder' => '082252239507',
            'rows' => 2,
        ],
    ];

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        try {
            return static::query()->where('key', $key)->value('value') ?? $default;
        } catch (Throwable) {
            return $default;
        }
    }

    public static function assetPath(string $key, string $default): string
    {
        return static::getValue($key, $default) ?: $default;
    }

    public static function assetUrl(string $key, string $default): string
    {
        return asset(static::assetPath($key, $default));
    }

    /**
     * @return array<string, string>
     */
    public static function optionLabels(): array
    {
        return collect(self::DEFINITIONS)
            ->mapWithKeys(fn (array $definition, string $key): array => [
                $key => $definition['group'] . ' - ' . $definition['label'],
            ])
            ->all();
    }

    /**
     * @return array{label: string, group: string, helper: string, placeholder: string, rows: int}
     */
    public static function definitionFor(?string $key): array
    {
        return self::DEFINITIONS[$key] ?? [
            'label' => $key ?: 'Custom Setting',
            'group' => 'Advanced',
            'helper' => 'Parameter custom untuk kebutuhan lanjutan. Pastikan key dipakai oleh kode sebelum mengubahnya.',
            'placeholder' => '',
            'rows' => 4,
        ];
    }

    public static function labelFor(?string $key): string
    {
        return self::definitionFor($key)['label'];
    }

    public static function groupFor(?string $key): string
    {
        return self::definitionFor($key)['group'];
    }

    public static function helperFor(?string $key): string
    {
        return self::definitionFor($key)['helper'];
    }

    public static function isSystemKey(?string $key): bool
    {
        return array_key_exists((string) $key, self::DEFINITIONS);
    }
}
