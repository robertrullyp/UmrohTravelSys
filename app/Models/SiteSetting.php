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
            'group' => 'Logo & Ikon',
            'helper' => 'Path logo yang tampil di header/footer publik, login, dan sidebar admin.',
            'placeholder' => 'images/site/logo.png',
            'rows' => 2,
        ],
        'favicon_path' => [
            'label' => 'Favicon',
            'group' => 'Logo & Ikon',
            'helper' => 'Path ikon kecil pada tab browser.',
            'placeholder' => 'images/site/logo.png',
            'rows' => 2,
        ],
        'hero_image_path' => [
            'label' => 'Gambar Hero Beranda',
            'group' => 'Hero Beranda',
            'helper' => 'Path gambar besar pada bagian atas halaman beranda.',
            'placeholder' => 'images/site/beranda-img.jpg',
            'rows' => 2,
        ],
        'hero_title_highlight' => [
            'label' => 'Highlight Judul Hero',
            'group' => 'Hero Beranda',
            'helper' => 'Teks pink di baris pertama hero beranda.',
            'placeholder' => 'Perjalanan Ibadah Umrah',
            'rows' => 2,
        ],
        'hero_title' => [
            'label' => 'Judul Hero',
            'group' => 'Hero Beranda',
            'helper' => 'Judul utama hero beranda.',
            'placeholder' => 'Nyaman, Aman & Terpercaya',
            'rows' => 2,
        ],
        'hero_subtitle' => [
            'label' => 'Deskripsi Hero',
            'group' => 'Hero Beranda',
            'helper' => 'Kalimat pendek di bawah judul hero beranda.',
            'placeholder' => 'PT. Amara Al Medina Travel siap menjadi mitra perjalanan ibadah terbaik Anda.',
            'rows' => 4,
        ],
        'cta_whatsapp' => [
            'label' => 'Nomor CTA WhatsApp',
            'group' => 'Tombol Hubungi Kami',
            'helper' => 'Nomor WhatsApp untuk tombol Hubungi Kami. Format boleh 08 atau 62.',
            'placeholder' => '082252239507',
            'rows' => 2,
        ],
        'seo_site_name' => [
            'label' => 'Nama Situs',
            'group' => 'SEO Default',
            'helper' => 'Nama brand yang dipakai pada metadata website.',
            'placeholder' => 'PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_default_title' => [
            'label' => 'Judul Google Default',
            'group' => 'SEO Default',
            'helper' => 'Judul cadangan untuk Google saat halaman belum punya judul khusus.',
            'placeholder' => 'PT Amara Al Medina Travel - Travel Umrah Terpercaya',
            'rows' => 2,
        ],
        'seo_default_description' => [
            'label' => 'Deskripsi Google Default',
            'group' => 'SEO Default',
            'helper' => 'Ringkasan cadangan untuk Google saat halaman belum punya deskripsi khusus.',
            'placeholder' => 'Informasi paket umrah, jadwal keberangkatan, galeri, profil, dan kontak PT Amara Al Medina Travel.',
            'rows' => 3,
        ],
        'seo_default_image_path' => [
            'label' => 'Gambar Preview Link Default',
            'group' => 'SEO Default',
            'helper' => 'Gambar cadangan untuk preview link WhatsApp/media sosial.',
            'placeholder' => 'images/site/beranda-img.jpg',
            'rows' => 2,
        ],
        'google_site_verification' => [
            'label' => 'Token Verifikasi Google',
            'group' => 'SEO Default',
            'helper' => 'Kode verifikasi dari Google Search Console, bukan seluruh tag HTML.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_home_title' => [
            'label' => 'Judul Google Beranda',
            'group' => 'SEO Halaman',
            'helper' => 'Judul halaman beranda yang tampil di Google.',
            'placeholder' => 'PT Amara Al Medina Travel - Travel Umrah Terpercaya',
            'rows' => 2,
        ],
        'seo_home_description' => [
            'label' => 'Deskripsi Google Beranda',
            'group' => 'SEO Halaman',
            'helper' => 'Ringkasan halaman beranda untuk Google.',
            'placeholder' => 'Paket dan jadwal umrah bersama PT Amara Al Medina Travel.',
            'rows' => 3,
        ],
        'seo_home_image_path' => [
            'label' => 'Gambar Preview Link Beranda',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar preview link khusus halaman beranda.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_profile_title' => [
            'label' => 'Judul Google Profil',
            'group' => 'SEO Halaman',
            'helper' => 'Judul halaman profil yang tampil di Google.',
            'placeholder' => 'Profil - PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_profile_description' => [
            'label' => 'Deskripsi Google Profil',
            'group' => 'SEO Halaman',
            'helper' => 'Ringkasan halaman profil untuk Google.',
            'placeholder' => 'Profil, visi, misi, dan komitmen PT Amara Al Medina Travel.',
            'rows' => 3,
        ],
        'seo_profile_image_path' => [
            'label' => 'Gambar Preview Link Profil',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar preview link khusus halaman profil.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_packages_title' => [
            'label' => 'Judul Google Paket',
            'group' => 'SEO Halaman',
            'helper' => 'Judul halaman daftar paket yang tampil di Google.',
            'placeholder' => 'Paket Umrah - PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_packages_description' => [
            'label' => 'Deskripsi Google Paket',
            'group' => 'SEO Halaman',
            'helper' => 'Ringkasan halaman daftar paket untuk Google.',
            'placeholder' => 'Pilihan paket umrah dengan fasilitas dan jadwal keberangkatan yang jelas.',
            'rows' => 3,
        ],
        'seo_packages_image_path' => [
            'label' => 'Gambar Preview Link Paket',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar preview link khusus halaman daftar paket.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_schedules_title' => [
            'label' => 'Judul Google Jadwal',
            'group' => 'SEO Halaman',
            'helper' => 'Judul halaman jadwal yang tampil di Google.',
            'placeholder' => 'Jadwal Keberangkatan - PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_schedules_description' => [
            'label' => 'Deskripsi Google Jadwal',
            'group' => 'SEO Halaman',
            'helper' => 'Ringkasan halaman jadwal untuk Google.',
            'placeholder' => 'Jadwal keberangkatan dan ketersediaan kuota paket umrah.',
            'rows' => 3,
        ],
        'seo_schedules_image_path' => [
            'label' => 'Gambar Preview Link Jadwal',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar preview link khusus halaman jadwal.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_galleries_title' => [
            'label' => 'Judul Google Galeri',
            'group' => 'SEO Halaman',
            'helper' => 'Judul halaman galeri yang tampil di Google.',
            'placeholder' => 'Galeri Kegiatan - PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_galleries_description' => [
            'label' => 'Deskripsi Google Galeri',
            'group' => 'SEO Halaman',
            'helper' => 'Ringkasan halaman galeri untuk Google.',
            'placeholder' => 'Dokumentasi kegiatan jamaah PT Amara Al Medina Travel.',
            'rows' => 3,
        ],
        'seo_galleries_image_path' => [
            'label' => 'Gambar Preview Link Galeri',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar preview link khusus halaman galeri.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_contact_title' => [
            'label' => 'Judul Google Kontak',
            'group' => 'SEO Halaman',
            'helper' => 'Judul halaman kontak yang tampil di Google.',
            'placeholder' => 'Kontak - PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_contact_description' => [
            'label' => 'Deskripsi Google Kontak',
            'group' => 'SEO Halaman',
            'helper' => 'Ringkasan halaman kontak untuk Google.',
            'placeholder' => 'Alamat, WhatsApp, email, dan lokasi PT Amara Al Medina Travel.',
            'rows' => 3,
        ],
        'seo_contact_image_path' => [
            'label' => 'Gambar Preview Link Kontak',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar preview link khusus halaman kontak.',
            'placeholder' => '',
            'rows' => 2,
        ],
        'seo_booking_title' => [
            'label' => 'Judul Google Booking',
            'group' => 'SEO Halaman',
            'helper' => 'Judul halaman booking utama yang tampil di Google.',
            'placeholder' => 'Booking Umrah - PT Amara Al Medina Travel',
            'rows' => 2,
        ],
        'seo_booking_description' => [
            'label' => 'Deskripsi Google Booking',
            'group' => 'SEO Halaman',
            'helper' => 'Ringkasan halaman booking utama untuk Google.',
            'placeholder' => 'Ajukan booking paket umrah secara online.',
            'rows' => 3,
        ],
        'seo_booking_image_path' => [
            'label' => 'Gambar Preview Link Booking',
            'group' => 'SEO Halaman',
            'helper' => 'Gambar preview link khusus halaman booking.',
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
            'group' => 'Custom',
            'helper' => 'Pengaturan custom untuk kebutuhan teknis. Pastikan key memang dipakai oleh kode sebelum diubah.',
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
