<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class SiteSetting extends Model
{
    use HasFactory;

    public const SEO_PAGES = [
        'home' => 'Beranda',
        'profile' => 'Profil',
        'packages' => 'Paket Umrah',
        'schedules' => 'Jadwal',
        'galleries' => 'Galeri',
        'contact' => 'Kontak',
        'booking' => 'Booking',
    ];

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
        'seo_site_name' => [
            'label' => 'Nama Situs',
            'group' => 'SEO Global',
            'helper' => 'Nama brand yang dipakai pada metadata dan structured data.',
            'placeholder' => 'PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_default_title' => [
            'label' => 'Judul Default',
            'group' => 'SEO Global',
            'helper' => 'Fallback judul saat metadata halaman tidak tersedia.',
            'placeholder' => 'PT Amara Al Medina Travel - Travel Umrah Terpercaya',
            'rows' => 2,
        ],
        'seo_default_description' => [
            'label' => 'Deskripsi Default',
            'group' => 'SEO Global',
            'helper' => 'Fallback meta description untuk halaman publik.',
            'placeholder' => 'Informasi paket umrah, jadwal keberangkatan, galeri, profil, dan kontak PT Amara Al Medina Travel.',
            'rows' => 3,
        ],
        'seo_default_image_path' => [
            'label' => 'Social Image Default',
            'group' => 'SEO Global',
            'helper' => 'Gambar fallback untuk Open Graph dan Twitter Card.',
            'placeholder' => 'images/site/beranda-img.jpg',
            'rows' => 2,
        ],
        'google_site_verification' => [
            'label' => 'Google Site Verification',
            'group' => 'SEO Global',
            'helper' => 'Token content dari meta verification Google Search Console.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_home_title' => [
            'label' => 'Judul Beranda',
            'group' => 'SEO Halaman',
            'helper' => 'SEO title halaman beranda.',
            'placeholder' => 'PT Amara Al Medina Travel - Travel Umrah Terpercaya',
            'rows' => 2,
        ],
        'seo_home_description' => [
            'label' => 'Deskripsi Beranda',
            'group' => 'SEO Halaman',
            'helper' => 'Meta description halaman beranda.',
            'placeholder' => 'Paket dan jadwal umrah bersama PT Amara Al Medina Travel.',
            'rows' => 3,
        ],
        'seo_home_image_path' => [
            'label' => 'Social Image Beranda',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar social preview khusus halaman beranda.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_profile_title' => [
            'label' => 'Judul Profil',
            'group' => 'SEO Halaman',
            'helper' => 'SEO title halaman profil.',
            'placeholder' => 'Profil - PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_profile_description' => [
            'label' => 'Deskripsi Profil',
            'group' => 'SEO Halaman',
            'helper' => 'Meta description halaman profil.',
            'placeholder' => 'Profil, visi, misi, dan komitmen PT Amara Al Medina Travel.',
            'rows' => 3,
        ],
        'seo_profile_image_path' => [
            'label' => 'Social Image Profil',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar social preview khusus halaman profil.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_packages_title' => [
            'label' => 'Judul Paket',
            'group' => 'SEO Halaman',
            'helper' => 'SEO title halaman daftar paket.',
            'placeholder' => 'Paket Umrah - PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_packages_description' => [
            'label' => 'Deskripsi Paket',
            'group' => 'SEO Halaman',
            'helper' => 'Meta description halaman daftar paket.',
            'placeholder' => 'Pilihan paket umrah dengan fasilitas dan jadwal keberangkatan yang jelas.',
            'rows' => 3,
        ],
        'seo_packages_image_path' => [
            'label' => 'Social Image Paket',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar social preview khusus halaman daftar paket.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_schedules_title' => [
            'label' => 'Judul Jadwal',
            'group' => 'SEO Halaman',
            'helper' => 'SEO title halaman jadwal.',
            'placeholder' => 'Jadwal Keberangkatan - PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_schedules_description' => [
            'label' => 'Deskripsi Jadwal',
            'group' => 'SEO Halaman',
            'helper' => 'Meta description halaman jadwal.',
            'placeholder' => 'Jadwal keberangkatan dan ketersediaan kuota paket umrah.',
            'rows' => 3,
        ],
        'seo_schedules_image_path' => [
            'label' => 'Social Image Jadwal',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar social preview khusus halaman jadwal.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_galleries_title' => [
            'label' => 'Judul Galeri',
            'group' => 'SEO Halaman',
            'helper' => 'SEO title halaman galeri.',
            'placeholder' => 'Galeri Kegiatan - PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_galleries_description' => [
            'label' => 'Deskripsi Galeri',
            'group' => 'SEO Halaman',
            'helper' => 'Meta description halaman galeri.',
            'placeholder' => 'Dokumentasi kegiatan jamaah PT Amara Al Medina Travel.',
            'rows' => 3,
        ],
        'seo_galleries_image_path' => [
            'label' => 'Social Image Galeri',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar social preview khusus halaman galeri.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_contact_title' => [
            'label' => 'Judul Kontak',
            'group' => 'SEO Halaman',
            'helper' => 'SEO title halaman kontak.',
            'placeholder' => 'Kontak - PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_contact_description' => [
            'label' => 'Deskripsi Kontak',
            'group' => 'SEO Halaman',
            'helper' => 'Meta description halaman kontak.',
            'placeholder' => 'Alamat, WhatsApp, email, dan lokasi PT Amara Al Medina Travel.',
            'rows' => 3,
        ],
        'seo_contact_image_path' => [
            'label' => 'Social Image Kontak',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar social preview khusus halaman kontak.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_booking_title' => [
            'label' => 'Judul Booking',
            'group' => 'SEO Halaman',
            'helper' => 'SEO title halaman booking utama.',
            'placeholder' => 'Booking Umrah - PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_booking_description' => [
            'label' => 'Deskripsi Booking',
            'group' => 'SEO Halaman',
            'helper' => 'Meta description halaman booking utama.',
            'placeholder' => 'Ajukan booking paket umrah secara online.',
            'rows' => 3,
        ],
        'seo_booking_image_path' => [
            'label' => 'Social Image Booking',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar social preview khusus halaman booking.',
            'placeholder' => '',
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
                $key => $definition['group'].' - '.$definition['label'],
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
