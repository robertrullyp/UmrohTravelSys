# PT Amara Al Medina Travel

Website profil dan panel admin untuk PT Amara Al Medina Travel. Aplikasi ini merupakan sistem berbasis Laravel dengan panel admin Filament untuk mengelola konten (paket umrah, jadwal, galeri, profil perusahaan, dan kontak).

## Stack

- PHP 8.3
- Laravel 13
- Filament 5 untuk panel admin `/admin`
- MySQL/MariaDB
- Tailwind/Vite untuk asset CSS dan JavaScript

## Requirements

Minimal dan rekomendasi lingkungan untuk menjalankan proyek ini:

- **PHP**: ^8.3 (sesuaikan dengan `composer.json`).
- **Composer**: versi 2.x.
- **Node.js**: direkomendasikan Node 18+ (untuk Vite dan build frontend).
- **npm / pnpm / yarn**: gunakan versi yang sesuai dengan Node (npm 9+ direkomendasikan).
- **Database**: MySQL atau MariaDB (MySQL 5.7+/8.x atau MariaDB setara).

PHP extensions yang umumnya diperlukan:

- `ctype`
- `fileinfo`
- `json`
- `mbstring`
- `openssl`
- `pdo` dan driver database (`pdo_mysql` untuk MySQL/MariaDB)
- `tokenizer`
- `xml`
- `zip` (disarankan untuk installer dan beberapa paket Composer)

Opsional (bergantung fitur yang dipakai):

- `gd` untuk crop/resize/kompres upload logo dan favicon dari Website Settings
- `imagick` bila dibutuhkan oleh ekstensi lain, tetapi optimizer bawaan memakai GD
- `exif` jika aplikasi memproses metadata gambar
- `curl` jika ada panggilan HTTP menggunakan ekstensi ini

Perangkat lunak/system tools:

- `git` (untuk kontrol versi dan workflow deploy)
- `unzip` (dibutuhkan Composer pada beberapa lingkungan)
- PHP-FPM (untuk deployment) atau built-in PHP server untuk pengujian

Contoh cara memeriksa versi dasar:

```bash
php -v
composer --version
node -v
npm -v
mysql --version
```

## Akses Lokal

Untuk pengujian lokal, jalankan aplikasi di server development (mis. Valet, Docker, atau built-in PHP server) atau gunakan vhost lokal yang memetakan host ke `127.0.0.1`.

Contoh pengecekan endpoint lokal menggunakan header Host:

```bash
curl -H 'Host: lulu.kapul.my.id' http://127.0.0.1/
curl -H 'Host: lulu.kapul.my.id' http://127.0.0.1/admin/login
```

Untuk akses melalui browser, tambahkan entri pada file `hosts` jika Anda memakai host custom, atau akses langsung pada alamat yang dikonfigurasi oleh environment Anda.

## Struktur Asset

- `public/images/site/` berisi asset default aktif: `logo.png` dan `beranda-img.jpg`.
- `public/images/site/uploads/` berisi upload runtime dari Website Settings dan tidak masuk git.
- `public/images/seed/` berisi gambar fallback dan referensi seed.
- Upload konten lain dari admin disimpan di disk `public` Laravel dan diakses melalui symlink `public/storage`.

## Versioning dan Changelog

- Versi aplikasi aktif dicatat di `VERSION` dan dibaca juga dari `APP_VERSION` bila environment mengaturnya.
- Catatan perubahan dicatat di `CHANGELOG.md` dengan format per versi, misalnya `## [v2026.06.16] - 2026-06-16`.
- Halaman admin `Settings > System Update` menampilkan versi, commit lokal, status token update, dan ringkasan catatan rilis terbaru dari `CHANGELOG.md`.
- Saat membuat rilis baru, update `VERSION`, tambahkan section baru paling atas di `CHANGELOG.md`, commit, lalu tag versi rilis.

## Setup

```bash
composer install
npm install
cp .env.example .env
/usr/bin/php8.3 artisan key:generate
/usr/bin/php8.3 artisan migrate --seed
/usr/bin/php8.3 artisan storage:link
npm run build
/usr/bin/php8.3 artisan optimize
/usr/bin/php8.3 artisan filament:optimize
```

Seeder admin awal membaca environment berikut bila tersedia:

```env
ADMIN_INITIAL_EMAIL=
ADMIN_INITIAL_PASSWORD=
APP_VERSION=v2026.06.16
APP_UPDATE_REPOSITORY=https://github.com/robertrullyp/UmrohTravelSys.git
APP_UPDATE_BRANCH=main
```

Disk upload publik memakai URL relatif `/storage` secara default. Jika perlu override, gunakan `FILESYSTEM_PUBLIC_URL`.
Disk `site_public` untuk Website Settings memakai URL relatif secara default agar aman pada HTTP/HTTPS; jika perlu override, gunakan `SITE_PUBLIC_URL`.

Jangan simpan credential database, password admin, atau secret `.env` di repository.

## Deployment (vhost / server)

- Document root harus diarahkan ke `public/`.
- Aktifkan rewrite Laravel sehingga semua permintaan diarahkan ke `public/index.php`.
- Gunakan PHP-FPM 8.3 (atau versi yang kompatibel).
- Pastikan `storage/` dan `bootstrap/cache/` writable oleh user web server.
- Jalankan `php artisan storage:link` setelah deploy.
- Pastikan asset Livewire dan route publik dapat diakses dari server produksi.

## Admin

Panel admin tersedia di:

```text
/admin/login
```

Akses panel dibatasi oleh RBAC (`panel.access`) menggunakan `spatie/laravel-permission`. Seeder memberi `ADMIN_INITIAL_EMAIL` role `super-admin`, sehingga akun awal dapat mengelola konten, booking, Website Settings, Users, Roles, dan Permissions.

Dashboard menampilkan ringkasan booking dan grafik pengunjung dengan filter rentang waktu. Tracking hanya berjalan untuk route publik dan menyimpan hash IP/user-agent, bukan IP mentah.

Pengaturan website berada di:

```text
/admin/settings/website
```

Halaman ini menyediakan upload logo, favicon, gambar hero beranda, teks hero, dan nomor WhatsApp CTA. Route lama `/admin/site-settings` diarahkan ke halaman ini untuk kompatibilitas.

Logo dan favicon yang diupload melalui Website Settings otomatis dibatasi ukurannya dan dikompresi dengan GD sebelum path disimpan ke `site_settings`.

Update sistem tersedia untuk `super-admin` di:

```text
/admin/settings/system-update
```

Halaman ini menampilkan versi aplikasi, branch, commit, remote aktif, dan menyediakan pengecekan remote serta tombol update dari `APP_UPDATE_REPOSITORY`. Untuk repository private, simpan Fine-grained personal access token GitHub melalui tombol `Input Token FAT` di halaman ini. Permission minimal token: repository terpilih `robertrullyp/UmrohTravelSys` dengan `Contents: Read-only`. Token disimpan terenkripsi di database dan dipakai lewat Git askpass sementara, bukan disisipkan ke URL remote.

Tombol update menjalankan `git fetch`, reset ke branch update, install dependency, build frontend, migrasi, dan optimize cache. Gunakan hanya setelah repository server sudah mengarah ke remote resmi.

## Test dan Build

Jalankan test dan build seperti biasa untuk proyek Laravel + frontend:

```bash
php artisan test
npm run build
php artisan optimize
php artisan filament:optimize
```

Untuk smoke-check lokal, panggil endpoint publik yang relevan menggunakan host atau alamat yang sesuai dengan konfigurasi lokal Anda.
