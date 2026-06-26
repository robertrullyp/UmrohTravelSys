# PT Amara Al Medina Travel

Aplikasi website publik, booking umrah, dan panel administrasi untuk PT Amara Al Medina Travel. Sistem ini dipakai untuk menampilkan profil travel, paket umrah, jadwal keberangkatan, galeri, kontak, menerima pengajuan booking, memproses review admin, mengatur SEO teknis, dan menjalankan pembaruan aplikasi dari repository resmi.

**Versi aplikasi:** `v2026.06.19`  
**Framework utama:** Laravel 13, Filament 5, Livewire, Tailwind CSS, Vite  
**Panel admin:** `/admin`  
**Sitemap:** `/sitemap.xml`

## Gambaran Sistem

Aplikasi terdiri dari tiga area besar:

1. **Website publik** untuk calon jamaah.
2. **Alur booking** untuk pengajuan pemesanan dan pengecekan status.
3. **Panel admin** untuk mengelola konten, booking, user, role, SEO, dan update sistem.

Halaman publik dirender server-side memakai Blade, sehingga konten, metadata SEO, sitemap, dan structured data dapat dibaca crawler. Panel admin dibangun dengan Filament dan dilindungi RBAC berbasis permission.

## Fitur Utama

### Website Publik

- Beranda, Profil, Paket Umrah, Detail Paket, Jadwal, Galeri, Kontak, dan Booking.
- Hanya data aktif yang tampil ke publik.
- Kontak utama dipakai untuk tombol WhatsApp, footer, telepon, email, peta, dan structured data.
- Gambar hero memakai `loading="eager"` dan `fetchpriority="high"`.
- Gambar di bawah fold memakai `loading="lazy"` dan `decoding="async"`.
- Jadwal publik memakai tabel di desktop dan kartu ringkas di mobile agar tidak terpotong.
- Galeri memakai album; setiap album bisa berisi banyak foto dan lightbox menampilkan thumbnail foto di album tersebut.

### Booking dan Kuota

- Pengunjung memilih paket dan jadwal yang aktif, mendatang, serta masih memiliki kuota.
- Submit booking divalidasi ulang di server dan dilindungi throttle.
- Booking baru berstatus `pending`, mendapat nomor booking, dan public token acak.
- Status booking dapat dicari memakai nomor booking dan nomor WhatsApp.
- Halaman status booking bersifat privat: data kontak dimask, tidak boleh diindeks, dan memakai token publik.
- Approval admin mengurangi kuota dalam transaksi database.
- Reject tidak mengurangi kuota.
- Cancel untuk booking yang sudah approved mengembalikan kuota satu kali.
- Status jadwal dihitung dari sisa kuota: `Tersedia`, `Hampir Penuh`, atau `Penuh`.

### Panel Admin

- Dashboard statistik konten, booking, dan pengunjung.
- Kelola Paket Umrah, Jadwal, Album Galeri, Profil Perusahaan, Kontak, dan Booking.
- Review booking: Detail, Edit, Setujui, Tolak, Batalkan, catatan admin, dan audit reviewer/waktu.
- Pengaturan Website untuk logo, favicon, gambar hero, CTA WhatsApp, SEO global, SEO halaman, dan verifikasi Google.
- SEO paket dapat diatur per paket: title, description, social image, dan pilihan tampil di Google.
- Akun Saya untuk avatar, nama, email, nomor telepon, password, mode tampilan, dan logout.
- Pengguna dan Role / Hak Akses untuk RBAC. Permission teknis tetap ada di database, tetapi tidak ditampilkan sebagai menu harian agar admin awam tidak salah mengubah izin internal.
- Log untuk melihat riwayat kunjungan publik secara anonim. Log lama dapat dibersihkan manual mulai dari yang lebih dari 1 hari, dan sistem menjadwalkan penghapusan otomatis untuk log yang lebih dari 90 hari.
- Pembaruan Sistem untuk cek update, mengatur akses GitHub, dan menjalankan update.
- Setelah berhasil menyimpan edit data, panel akan kembali ke halaman detail record bila tersedia. Jika resource belum punya halaman detail, panel kembali ke halaman daftar.

### SEO dan Google Search

- Metadata server-rendered: title, description, robots, canonical, Open Graph, dan Twitter Card.
- Canonical memakai `APP_URL`, HTTPS, tanpa query string, dan tanpa trailing slash selain `/`.
- JSON-LD:
  - `TravelAgency`
  - `WebSite`
  - `BreadcrumbList`
  - `Product` dan `Offer` pada detail paket
- Sitemap dinamis di `/sitemap.xml`.
- `robots.txt` menunjuk sitemap produksi.
- Halaman publik utama dan `/booking` indexable.
- `/booking/paket/{slug}` memakai `noindex,follow`.
- `/booking/{public_token}` dan seluruh `/admin/*` memakai `noindex,nofollow,nosnippet` dan `X-Robots-Tag`.
- Token Google Search Console dapat diisi dari Pengaturan Website.

### Keamanan dan Privasi

- Admin wajib login dan memiliki `panel.access`.
- Akses resource dikontrol dengan permission.
- Booking status lookup memakai kombinasi nomor booking + WhatsApp.
- IP dan user-agent pengunjung disimpan sebagai hash HMAC, bukan nilai mentah.
- Response admin dan status booking dilarang diindeks crawler.
- Security header aplikasi: `X-Content-Type-Options`, `X-Frame-Options`, dan `Referrer-Policy`.
- Password admin awal tidak punya fallback bawaan; seeder wajib menerima email dan password dari `.env`.

## Role dan Permission

Role bawaan:

- `super-admin`: akses penuh ke semua modul, pengaturan, user, role, permission, dan update sistem.
- `admin`: akses operasional konten dan booking; tidak mendapat akses user/role/permission/update.

Permission utama:

- `panel.access`
- `{resource}.view`
- `{resource}.create`
- `{resource}.update`
- `{resource}.delete`
- `bookings.approve`
- `bookings.reject`
- `bookings.cancel`
- `updates.view`
- `updates.run`
- `logs.view`
- `logs.delete`

Daftar permission didefinisikan di `app/Support/AdminPermissions.php` dan dibuat oleh seeder. Untuk operasional harian, admin cukup memberi akses melalui checklist per modul di **Role / Hak Akses**.

## Rute Penting

Publik:

- `/`
- `/profil`
- `/paket-umrah`
- `/paket-umrah/{slug}`
- `/jadwal`
- `/galeri`
- `/kontak`
- `/booking`
- `/booking/paket/{slug}`
- `/booking/{public_token}`
- `/sitemap.xml`
- `/robots.txt`
- `/up`

Admin:

- `/admin/login`
- `/admin`
- `/admin/bookings`
- `/admin/umrah-packages`
- `/admin/schedules`
- `/admin/galleries`
- `/admin/company-profiles`
- `/admin/contacts`
- `/admin/settings/website`
- `/admin/settings/users`
- `/admin/settings/roles`
- `/admin/settings/logs`
- `/admin/settings/permissions` untuk permission teknis internal; route ada tetapi tidak menjadi menu harian dan umumnya tidak perlu dibuka.
- `/admin/settings/system-update`
- `/admin/profile`

Route lama seperti `/admin/users`, `/admin/roles`, `/admin/permissions`, dan `/admin/site-settings` diarahkan ke menu Pengaturan yang baru. Permission teknis tetap dikelola lewat seeder/source code agar izin internal tidak berubah tanpa review developer.

## Struktur Folder

```text
app/Filament/              panel admin, resource, page, widget, dan form Filament
app/Http/Controllers/      controller publik, booking, dan sitemap
app/Http/Middleware/       canonical redirect, noindex, security header, tracking
app/Http/Requests/         validasi form booking dan lookup status
app/Models/                model database utama
app/Services/              booking, SEO, optimizer gambar, update sistem
app/Support/               DTO SEO dan daftar permission admin
config/                    konfigurasi Laravel, admin, SEO, filesystem
database/migrations/       skema database
database/seeders/          data awal, role, permission, admin awal
resources/css/             CSS publik dan tema admin
resources/js/              JavaScript publik
resources/views/public/    halaman publik Blade
resources/views/filament/  partial dan view khusus admin
routes/web.php             route publik, booking, sitemap, dan redirect lama
tests/Feature/             test workflow utama sistem
docs/                      audit sistem dan contoh konfigurasi web server
```

## Kebutuhan Server

Minimal:

- PHP `^8.3`
- Composer 2.x
- Node.js `^20.19.0` atau `>=22.12.0`
- npm
- MySQL 8 atau MariaDB setara untuk produksi
- SQLite untuk test lokal
- Git dan unzip

Extension PHP yang dibutuhkan:

```text
ctype curl dom fileinfo gd intl json mbstring openssl pdo_mysql tokenizer xml xmlreader xmlwriter zip
```

Di server produksi ini terdapat lebih dari satu binary PHP. Gunakan PHP 8.3 untuk command operasional:

```bash
php8.3 -v
php8.3 -m
php8.3 /usr/local/bin/composer check-platform-reqs --no-dev
node -v
npm -v
```

Jika server hanya menyediakan `/usr/bin/php8.3`, gunakan path tersebut sebagai pengganti `php8.3`.

## Setup Lokal dari Nol

Langkah ini untuk developer baru yang ingin menjalankan aplikasi di komputer lokal.

1. Clone repository.

```bash
git clone <url-repository>
cd <folder-project>
```

2. Salin file environment.

```bash
cp .env.example .env
```

3. Install dependency PHP.

```bash
php8.3 /usr/local/bin/composer install
```

Jika Composer tersedia sebagai command biasa:

```bash
php8.3 $(which composer) install
```

4. Install dependency frontend.

```bash
npm ci
```

5. Generate app key.

```bash
php8.3 artisan key:generate
```

6. Isi admin awal di `.env`.

```env
ADMIN_INITIAL_NAME=Admin
ADMIN_INITIAL_EMAIL=admin@example.test
ADMIN_INITIAL_PASSWORD=ganti-dengan-password-kuat
```

Seeder akan gagal jika `ADMIN_INITIAL_EMAIL` atau `ADMIN_INITIAL_PASSWORD` kosong. Ini disengaja agar tidak ada password admin bawaan.

7. Pilih database lokal.

Opsi paling mudah adalah SQLite:

```env
DB_CONNECTION=sqlite
```

Pastikan file SQLite ada:

```bash
touch database/database.sqlite
```

Untuk MySQL/MariaDB, isi:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=<password_database>
```

8. Jalankan migration dan seeder.

```bash
php8.3 artisan migrate --seed
```

9. Buat symlink storage.

```bash
php8.3 artisan storage:link
```

10. Build asset.

```bash
npm run build
```

11. Jalankan aplikasi lokal.

```bash
php8.3 artisan serve
```

Buka:

- `http://127.0.0.1:8000`
- `http://127.0.0.1:8000/admin`

Login memakai `ADMIN_INITIAL_EMAIL` dan `ADMIN_INITIAL_PASSWORD` yang sudah diisi.

## Environment Penting

Contoh production:

```env
APP_NAME="PT Amara Al Medina Travel"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lulu.kapul.my.id
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID
APP_VERSION=v2026.06.19

SEO_CANONICAL_REDIRECT=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=<password_database>

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

FILESYSTEM_PUBLIC_URL=/storage
SITE_PUBLIC_URL=

APP_UPDATE_REPOSITORY=https://github.com/robertrullyp/UmrohTravelSys.git
APP_UPDATE_BRANCH=main

ADMIN_INITIAL_NAME=Admin
ADMIN_INITIAL_EMAIL=admin@example.com
ADMIN_INITIAL_PASSWORD=ganti-dengan-password-kuat
```

Catatan:

- `APP_URL` menjadi sumber canonical, sitemap, redirect HTTPS, dan URL social image.
- `APP_VERSION` harus sama dengan file `VERSION` dan rilis terbaru di `CHANGELOG.md`.
- `FILESYSTEM_PUBLIC_URL=/storage` dipakai untuk upload paket, galeri, dan avatar.
- `SITE_PUBLIC_URL=` dipakai untuk asset website di `public/images/site/uploads`.
- Jangan commit `.env`, credential database, password admin, token GitHub, atau secret lain.

## Cara Kerja Admin Sehari-hari

1. Login ke `/admin`.
2. Cek dashboard untuk melihat booking pending dan ringkasan konten.
3. Kelola paket di **Paket Umrah**.
4. Kelola jadwal dan kuota di **Jadwal**.
5. Proses pengajuan di **Booking**:
   - buka detail booking,
   - periksa data,
   - setujui jika valid,
   - tolak jika tidak valid,
   - batalkan jika booking approved harus dibatalkan.
6. Kelola konten pendukung:
   - **Galeri**
   - **Profil**
   - **Kontak**
7. Ubah logo, hero, CTA, dan SEO di **Pengaturan Website**.
8. Kelola admin lain di **Pengguna** dan **Role / Hak Akses** jika memiliki akses.
9. Jalankan cek update di **Pembaruan Sistem** hanya saat diperlukan.
10. Cek **Log** bila perlu melihat riwayat kunjungan publik. Gunakan **Bersihkan Log Lama** untuk menghapus log yang sudah melewati usia tertentu, misalnya lebih dari 1 hari, 7 hari, atau 90 hari.

### Dampak Edit Admin ke Halaman Publik

Gunakan panduan singkat ini saat mengubah konten:

- **Paket Umrah**: gambar utama tampil di kartu beranda, daftar paket, dan detail paket. Poster/flyer tampil utuh di detail, sedangkan thumbnail dapat crop ringan agar grid rapi.
- **Jadwal**: jadwal aktif, mendatang, dan masih punya kuota akan tampil di Beranda, `/jadwal`, dan detail paket.
- **Galeri**: album aktif tampil di halaman Galeri; foto pertama menjadi sampul, dan seluruh foto album tampil di lightbox publik.
- **Profil**: data perusahaan dan foto profil tampil di halaman Profil.
- **Kontak**: kontak aktif tampil di halaman Kontak; kontak utama dipakai footer, tombol telepon/email/WhatsApp, peta, dan structured data.
- **Pengaturan Website**: logo, favicon, hero beranda, tombol WhatsApp, SEO default, SEO per halaman, dan verifikasi Google berasal dari menu ini.
- **Role / Hak Akses**: setiap modul memakai checklist. Centang hanya akses yang memang dibutuhkan user tersebut.
- **Log**: menampilkan kunjungan publik secara anonim. `Lihat` menampilkan menu Log, sedangkan `Hapus log lama` mengizinkan pembersihan manual.

### Setelah Menyimpan Data

Perilaku panel admin dibuat konsisten:

- Edit **Booking** kembali ke detail booking agar admin langsung melihat status dan ringkasan terbaru.
- Edit Paket, Jadwal, Galeri, Kontak, Pengguna, Role / Hak Akses, dan pengaturan custom kembali ke daftar masing-masing karena resource tersebut belum memiliki halaman detail khusus.
- Profil Perusahaan tetap berupa satu halaman edit langsung, karena hanya ada satu record profil.
- Jika validasi gagal, panel tetap di form dan menampilkan pesan error pada field terkait.

## Test dan Build

Jalankan sebelum commit atau deploy:

```bash
php8.3 artisan test
npm run build
```

Audit tambahan:

```bash
php8.3 vendor/bin/pint --test
php8.3 /usr/local/bin/composer validate --no-check-publish
php8.3 /usr/local/bin/composer audit --locked
npm audit --audit-level=low
```

Jika ingin memperbaiki format otomatis:

```bash
php8.3 vendor/bin/pint
```

## Deployment Production

Document root web server harus mengarah ke folder `public/`, bukan root project.

Contoh konfigurasi:

- `docs/webserver/nginx.conf.example`
- `docs/webserver/apache-vhost.conf.example`
- `docs/webserver/apache-root-fallback.md`

Checklist deploy:

1. Pull kode terbaru dari repository resmi.
2. Pastikan `.env` production benar.
3. Pastikan folder `storage/` dan `bootstrap/cache/` writable oleh user web server.
4. Install dependency production.

```bash
php8.3 /usr/local/bin/composer install --no-dev --optimize-autoloader
```

5. Install dependency frontend dan build asset.

```bash
npm ci
npm run build
```

6. Jalankan migration.

```bash
php8.3 artisan migrate --force
```

7. Pastikan symlink storage ada.

```bash
php8.3 artisan storage:link
```

8. Optimalkan cache.

```bash
php8.3 artisan optimize
php8.3 artisan filament:optimize
```

9. Pasang cron Laravel scheduler agar tugas otomatis berjalan, termasuk penghapusan log kunjungan yang lebih dari 90 hari.

```cron
* * * * * php /www/wwwroot/lulu.kapul.my.id/artisan schedule:run >> /dev/null 2>&1
```

10. Smoke check:

```bash
curl -I https://lulu.kapul.my.id/
curl -I https://lulu.kapul.my.id/admin/login
curl -I https://lulu.kapul.my.id/sitemap.xml
curl -I https://lulu.kapul.my.id/robots.txt
curl -I https://lulu.kapul.my.id/up
```

Yang harus benar:

- `/` merespons `200`.
- HTTP redirect `301` ke HTTPS.
- `/sitemap.xml` bertipe XML dan tidak mengirim session cookie.
- `/admin/login` mengirim `X-Robots-Tag: noindex, nofollow, nosnippet`.
- `.env`, `composer.json`, dan file root project tidak terbuka dari web.

## Google Search

Kode aplikasi sudah menyiapkan sisi teknis Google Search, tetapi indexing tetap bergantung pada Google.

Langkah operasional:

1. Pastikan `APP_URL` benar dan memakai HTTPS.
2. Isi token verifikasi di **Pengaturan Website** jika memakai meta verification.
3. Buka Google Search Console.
4. Verifikasi property domain atau URL prefix.
5. Submit sitemap:

```text
https://lulu.kapul.my.id/sitemap.xml
```

6. Pantau Coverage, Page Indexing, dan Core Web Vitals.
7. Uji halaman penting dengan Rich Results Test bila structured data berubah.

## Pembaruan Sistem

Halaman `/admin/settings/system-update` hanya tersedia untuk user dengan permission:

- `updates.view`
- `updates.run`

Untuk repository privat, simpan Fine-grained GitHub token dengan akses minimal `Contents: Read-only`. Token disimpan terenkripsi, dipakai melalui temporary `GIT_ASKPASS`, output disensor, lalu file sementara dihapus.

Urutan update otomatis:

1. `git fetch origin <branch>`
2. `git reset --hard origin/<branch>`
3. Composer install production lewat PHP 8.3
4. `npm ci`
5. `npm run build`
6. `php artisan migrate --force`
7. `php artisan optimize`
8. `php artisan filament:optimize`

Peringatan:

- Update memakai `git reset --hard`.
- Commit dan push semua perubahan sebelum menjalankan update.
- Backup database dan upload sebelum update besar.
- Belum ada rollback otomatis; pemulihan dilakukan manual dari Git dan backup.
- Jangan menjalankan update dari branch yang belum diuji.

## Troubleshooting Pemula

### Halaman blank atau error setelah deploy

Jalankan:

```bash
php8.3 artisan optimize:clear
php8.3 artisan optimize
php8.3 artisan filament:optimize
```

Lalu cek log:

```bash
tail -n 100 storage/logs/laravel.log
```

### Composer gagal karena extension hilang

Cek extension:

```bash
php8.3 -m
php8.3 /usr/local/bin/composer check-platform-reqs --no-dev
```

Pastikan CLI dan PHP-FPM memakai PHP 8.3 dengan extension yang sama.

### Gambar upload tidak tampil

Pastikan symlink ada:

```bash
php8.3 artisan storage:link
```

Pastikan permission folder benar:

```bash
chmod -R ug+rw storage bootstrap/cache
```

### Asset CSS/JS tidak berubah

Build ulang:

```bash
npm ci
npm run build
php8.3 artisan optimize:clear
php8.3 artisan optimize
```

### Login admin awal tidak bisa dibuat

Pastikan `.env` berisi:

```env
ADMIN_INITIAL_EMAIL=admin@example.com
ADMIN_INITIAL_PASSWORD=password-kuat
```

Lalu jalankan:

```bash
php8.3 artisan migrate --seed
```

### Sitemap atau canonical salah domain

Cek:

```env
APP_URL=https://domain-yang-benar
```

Lalu:

```bash
php8.3 artisan optimize:clear
php8.3 artisan optimize
```

### Update sistem gagal

1. Jangan langsung retry tanpa membaca pesan error.
2. Cek apakah worktree bersih:

```bash
git status
```

3. Cek PHP/Composer:

```bash
php8.3 /usr/local/bin/composer check-platform-reqs --no-dev
```

4. Cek log Laravel.
5. Jika gagal setelah `git reset`, pulihkan dari Git dan backup database/upload.

## Audit Sistem

Catatan audit terbaru ada di:

```text
docs/SYSTEM_AUDIT.md
```

Audit mencakup:

- arsitektur,
- booking dan kuota,
- RBAC,
- SEO,
- security header,
- dependency,
- build,
- deployment,
- system update,
- risiko operasional tersisa.

## Versioning dan Rilis

Format versi:

```text
vYYYY.MM.DD
```

Saat membuat rilis:

1. Jalankan test, audit dependency, dan build.
2. Perbarui file `VERSION`.
3. Perbarui fallback versi di `config/admin.php`.
4. Perbarui `APP_VERSION` di `.env.example` dan production `.env`.
5. Tambahkan section baru di `CHANGELOG.md`.
6. Commit semua perubahan.
7. Buat tag Git sesuai versi.
8. Push commit dan tag ke repository resmi.
9. Deploy.
10. Smoke check halaman publik, booking, sitemap, robots, security header, dan admin.

## Catatan Penting

- Jangan commit secret apa pun.
- Jangan mengubah permission internal tanpa memperbarui seeder dan test.
- Jangan menjalankan update sistem dari worktree kotor.
- Jangan menghapus migration lama.
- Perubahan timezone dari UTC ke zona lokal harus direncanakan karena berpengaruh pada timestamp lama.
