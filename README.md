# PT Amara Al Medina Travel

Website profil dan panel admin untuk PT Amara Al Medina Travel. Aplikasi ini merupakan sistem berbasis Laravel dengan panel admin Filament untuk mengelola konten (paket umrah, jadwal, galeri, profil perusahaan, dan kontak).

## Stack

- PHP 8.3
- Laravel 13
- Filament 5 untuk panel admin `/admin`
- MySQL/MariaDB
- Tailwind/Vite untuk asset CSS dan JavaScript

## Akses Lokal

Untuk pengujian lokal, jalankan aplikasi di server development (mis. Valet, Docker, atau built-in PHP server) atau gunakan vhost lokal yang memetakan host ke `127.0.0.1`.

Contoh pengecekan endpoint lokal menggunakan header Host (ganti `local.test` dengan host lokal Anda jika perlu):

```bash
curl -H 'Host: local.test' http://127.0.0.1/
curl -H 'Host: local.test' http://127.0.0.1/admin/login
```

Untuk akses melalui browser, tambahkan entri pada file `hosts` jika Anda memakai host custom, atau akses langsung pada alamat yang dikonfigurasi oleh environment Anda.

## Struktur Asset

- `public/images/site/` berisi asset aktif: `logo.png` dan `beranda-img.jpg`.
-- `public/images/seed/` berisi gambar fallback dan referensi seed.
-- Upload dari admin disimpan di disk `public` Laravel dan diakses melalui symlink `public/storage`.

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
```

Disk upload publik memakai URL relatif `/storage` secara default. Jika perlu override, gunakan `FILESYSTEM_PUBLIC_URL`.

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

Akses panel dibatasi untuk user dengan flag admin. Setelah login berhasil, user diarahkan ke dashboard Filament untuk mengelola paket umrah, jadwal, galeri, profil, kontak, dan pengaturan website.

Dashboard juga menampilkan grafik pengunjung 14 hari terakhir. Tracking hanya berjalan untuk route publik dan menyimpan hash IP/user-agent, bukan IP mentah.

## Test dan Build

Jalankan test dan build seperti biasa untuk proyek Laravel + frontend:

```bash
php artisan test
npm run build
php artisan optimize
php artisan filament:optimize
```

Untuk smoke-check lokal, panggil endpoint publik yang relevan menggunakan host atau alamat yang sesuai dengan konfigurasi lokal Anda.
