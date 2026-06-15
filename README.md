# PT Amara Al Medina Travel

Website profil dan admin konten untuk PT Amara Al Medina Travel. Aplikasi ini menggantikan static placeholder di vhost `lulu.kapul.my.id` dan berjalan sebagai Laravel + Filament dengan database MySQL/MariaDB.

## Stack

- PHP 8.3
- Laravel 13
- Filament 5 untuk panel admin `/admin`
- MySQL/MariaDB
- Tailwind/Vite untuk asset CSS dan JavaScript

## Akses Lokal

Fase ini memakai vhost lokal. Validasi DNS publik `lulu.kapul.my.id` diabaikan karena domain publik masih resolve ke IP lain.

```bash
curl -H 'Host: lulu.kapul.my.id' http://127.0.0.1/
curl -H 'Host: lulu.kapul.my.id' http://127.0.0.1/admin/login
```

Untuk browser lokal, pastikan domain mengarah ke `127.0.0.1` di file hosts atau akses melalui vhost server yang sudah memetakan Host header tersebut.

## Struktur Asset

- `public/images/site/` berisi asset aktif: `logo.png` dan `beranda-img.jpg`.
- `public/images/seed/` berisi gambar fallback dan referensi seed.
- Upload dari admin disimpan di disk `public` Laravel dan diakses melalui symlink `public/storage`.
- Folder `_legacy_static_*` adalah arsip static lama dan tidak perlu masuk version control.

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

## Deployment Vhost

- Document root harus diarahkan ke `public/`.
- Aktifkan rewrite Laravel ke `public/index.php`.
- Gunakan PHP-FPM 8.3.
- Pastikan `storage/` dan `bootstrap/cache/` writable oleh user web server.
- Jalankan `storage:link` setelah deploy.
- Pastikan route asset Livewire bisa diakses, misalnya:

```bash
curl -H 'Host: lulu.kapul.my.id' http://127.0.0.1/livewire-a185f6a1/livewire.min.js
```

## Admin

Panel admin tersedia di:

```text
/admin/login
```

Akses panel dibatasi untuk user dengan flag admin. Setelah login berhasil, user diarahkan ke dashboard Filament untuk mengelola paket umrah, jadwal, galeri, profil, kontak, dan pengaturan website.

Dashboard juga menampilkan grafik pengunjung 14 hari terakhir. Tracking hanya berjalan untuk route publik dan menyimpan hash IP/user-agent, bukan IP mentah.

## Test dan Build

```bash
/usr/bin/php8.3 artisan test
npm run build
/usr/bin/php8.3 artisan optimize
/usr/bin/php8.3 artisan filament:optimize
```

Smoke check lokal:

```bash
curl -H 'Host: lulu.kapul.my.id' http://127.0.0.1/
curl -H 'Host: lulu.kapul.my.id' http://127.0.0.1/admin/login
curl -H 'Host: lulu.kapul.my.id' http://127.0.0.1/galeri
curl -H 'Host: lulu.kapul.my.id' http://127.0.0.1/kontak
```
