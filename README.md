# PT Amara Al Medina Travel

Sistem website publik, pemesanan umrah, dan panel administrasi PT Amara Al Medina Travel. Aplikasi menangani konten publik, paket dan jadwal, alur booking dengan kontrol kuota, galeri, kontak, SEO teknis, analitik pengunjung, RBAC, serta pembaruan aplikasi dari repository resmi.

**Versi saat ini:** `v2026.06.19`

## Teknologi

- PHP 8.3 dan Laravel 13.15
- Filament 5.6 dengan Livewire 4.3 untuk panel `/admin`
- Spatie Laravel Permission 8 untuk role dan permission
- MySQL/MariaDB untuk produksi; SQLite in-memory untuk test
- Tailwind CSS 4.3 dan Vite 8 untuk asset frontend
- PHPUnit 12 untuk automated test

## Fitur Sistem

### Website publik

- Beranda, profil perusahaan, paket umrah, detail paket, jadwal, galeri, kontak, dan booking.
- Konten hanya mengambil record aktif dan mengikuti urutan yang dikelola admin.
- Kontak utama dipakai untuk CTA, footer, WhatsApp, telepon, email, peta, dan structured data.
- Gambar hero diprioritaskan untuk LCP; gambar di bawah fold memakai lazy loading dan dimensi stabil.
- Tracking pengunjung mencatat tanggal, path, route, serta hash IP/user-agent. IP dan user-agent mentah tidak disimpan.

### Booking dan kuota

- Pengunjung memilih paket serta jadwal aktif, mendatang, dan masih memiliki kuota.
- Input divalidasi ulang di server; submit dan lookup dilindungi rate limit.
- Booking baru berstatus `pending` dan memperoleh nomor booking serta public token acak 48 karakter.
- Status dapat dicari dengan nomor booking dan WhatsApp. Pesan gagal dibuat generik untuk mengurangi enumerasi data.
- Halaman status mem-mask nomor WhatsApp/email dan tidak boleh diindeks mesin pencari.
- Approval mengunci booking dan jadwal dalam transaksi database sebelum mengurangi kuota.
- Reject tidak mengurangi kuota. Cancel booking approved mengembalikan kuota tepat satu kali.
- Status jadwal otomatis menjadi `Tersedia`, `Hampir Penuh`, atau `Penuh` sesuai sisa kuota.

### Panel admin

- Dashboard ringkas, statistik konten, booking menunggu review, dan grafik pengunjung.
- CRUD paket, jadwal, galeri, profil perusahaan, kontak, booking, user, role, dan permission.
- Review booking: approve, reject, cancel, catatan admin, alasan penolakan, dan audit reviewer/waktu.
- Website Settings untuk logo, favicon, hero, CTA WhatsApp, metadata SEO global, serta metadata setiap halaman.
- Metadata SEO paket dapat diatur per paket, termasuk social image dan pilihan indexable.
- My Account untuk nama, email, avatar, password, mode tampilan, dan logout terkonfirmasi.
- Pembaruan Sistem untuk cek versi, mengatur akses GitHub, dan menjalankan update terkontrol.

### SEO dan Google Search

- Server-rendered title, description, canonical, robots, Open Graph, dan Twitter Card.
- JSON-LD `TravelAgency`, `WebSite`, `BreadcrumbList`, serta `Product`/`Offer` pada detail paket.
- Sitemap dinamis di `/sitemap.xml`; `robots.txt` menunjuk sitemap produksi.
- HTTP, host, dan trailing slash dinormalisasi ke URL pada `APP_URL` saat production.
- Halaman publik utama dan `/booking` indexable.
- Form booking paket memakai `noindex,follow`.
- Status booking dan seluruh admin memakai `noindex,nofollow,nosnippet` serta `X-Robots-Tag`.
- Verifikasi Search Console dapat disimpan dari Website Settings. Aktivasi property dan submit sitemap tetap dilakukan di Google Search Console.

## Role dan Permission

- `super-admin`: seluruh resource, account management, permission, dan system update.
- `admin`: operasional konten dan booking; tidak memperoleh akses user/role/permission/system update.
- User lain hanya dapat membuka panel jika memiliki `panel.access`.
- Resource memakai permission `{resource}.{view|create|update|delete}`.
- Aksi khusus booking: `bookings.approve`, `bookings.reject`, `bookings.cancel`.
- Aksi update: `updates.view`, `updates.run`.

Daftar permission didefinisikan di `app/Support/AdminPermissions.php` dan disinkronkan oleh seeder.

## Struktur Penting

```text
app/Filament/              panel admin, resource, page, dan widget
app/Http/Controllers/      halaman publik, booking, dan sitemap
app/Http/Middleware/       canonical redirect, security header, noindex, tracking
app/Services/              booking status, SEO, image optimizer, system update
app/Support/               daftar permission dan data SEO
database/migrations/       schema dan perubahan database
resources/views/public/    halaman publik Blade
resources/views/filament/  view custom panel admin
tests/Feature/             coverage workflow publik, admin, RBAC, SEO, dan update
docs/webserver/            contoh konfigurasi Apache/Nginx
```

## Kebutuhan Server

- PHP `^8.3` untuk CLI dan PHP-FPM.
- Composer 2.x.
- Node.js `^20.19.0` atau `>=22.12.0` dan npm yang kompatibel.
- MySQL 8/MariaDB setara.
- Git dan unzip.
- Extension PHP: `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `xmlreader`, `xmlwriter`, dan `zip`.

Server ini memiliki lebih dari satu binary PHP. Gunakan `/usr/bin/php8.3` untuk Artisan, Composer, Pint, dan PHPUnit karena binary `php` default tidak memuat semua extension yang diperlukan.

Pemeriksaan platform:

```bash
/usr/bin/php8.3 -m
/usr/bin/php8.3 /usr/local/bin/composer check-platform-reqs --no-dev
node -v
npm -v
```

## Setup

```bash
cp .env.example .env
/usr/bin/php8.3 /usr/local/bin/composer install
npm ci
/usr/bin/php8.3 artisan key:generate
/usr/bin/php8.3 artisan migrate --seed
/usr/bin/php8.3 artisan storage:link
npm run build
/usr/bin/php8.3 artisan optimize
/usr/bin/php8.3 artisan filament:optimize
```

Sebelum `migrate --seed`, isi credential admin awal. Seeder akan berhenti jika salah satunya kosong:

```env
ADMIN_INITIAL_NAME=Admin
ADMIN_INITIAL_EMAIL=admin@example.com
ADMIN_INITIAL_PASSWORD=gunakan-password-kuat
```

Jangan commit `.env`, credential database, password admin, token GitHub, atau secret lainnya.

## Environment Penting

```env
APP_NAME="PT Amara Al Medina Travel"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lulu.kapul.my.id
APP_LOCALE=id
APP_VERSION=v2026.06.19

SEO_CANONICAL_REDIRECT=true
APP_UPDATE_REPOSITORY=https://github.com/robertrullyp/UmrohTravelSys.git
APP_UPDATE_BRANCH=main

FILESYSTEM_PUBLIC_URL=/storage
SITE_PUBLIC_URL=
```

- `APP_URL` adalah sumber canonical, sitemap, social image, dan redirect HTTPS.
- `APP_VERSION` harus sama dengan file `VERSION` dan versi terbaru di `CHANGELOG.md`.
- Upload konten memakai disk `public` dan symlink `/storage`.
- Upload branding/SEO dari Website Settings memakai disk `site_public` di `public/images/site/uploads`.

## Deployment

Document root harus diarahkan ke direktori `public/`. Contoh tersedia di:

- `docs/webserver/nginx.conf.example`
- `docs/webserver/apache-vhost.conf.example`
- `docs/webserver/apache-root-fallback.md`

Checklist deployment:

1. Set `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL` HTTPS.
2. Pastikan PHP-FPM serta CLI memakai PHP 8.3 dengan extension yang sama.
3. Pastikan `storage/` dan `bootstrap/cache/` writable oleh user web server.
4. Jalankan Composer menggunakan `/usr/bin/php8.3`.
5. Jalankan migration, build asset, `storage:link`, `optimize`, dan `filament:optimize`.
6. Pastikan `/`, `/admin/login`, `/sitemap.xml`, `/robots.txt`, dan `/up` dapat diakses sesuai fungsinya.
7. Pastikan HTTP merespons `301` ke HTTPS dan file seperti `.env`/`composer.json` tidak dapat diakses publik.

Security header `X-Content-Type-Options`, `X-Frame-Options`, dan `Referrer-Policy` dipasang oleh aplikasi. HSTS tetap dikonfigurasi di web server TLS.

Queue memakai driver database, tetapi saat ini tidak ada workflow wajib yang bergantung pada worker queue. Tidak ada scheduled task yang terdaftar.

## System Update

Halaman `/admin/settings/system-update` hanya tersedia untuk user dengan `updates.view`; eksekusi update membutuhkan `updates.run`.

Untuk repository privat, simpan Fine-grained GitHub token dengan akses minimal `Contents: Read-only`. Token dienkripsi di database, diteruskan melalui temporary `GIT_ASKPASS`, disensor dari output, lalu file sementara dihapus.

Urutan update:

1. `git fetch origin <branch>`
2. `git reset --hard origin/<branch>`
3. Composer install production melalui PHP 8.3
4. `npm ci` dan build asset
5. Migration database
6. Cache Laravel dan Filament

Peringatan operasional:

- Update melakukan hard reset. Commit dan push seluruh perubahan sebelum menjalankannya.
- Buat backup database dan file upload sebelum update besar.
- Update berhenti pada command pertama yang gagal dan belum memiliki rollback otomatis.
- Jalankan hanya dari repository/branch resmi dengan worktree bersih.

## Test, Format, dan Audit

```bash
/usr/bin/php8.3 artisan test
/usr/bin/php8.3 vendor/bin/pint --test
/usr/bin/php8.3 /usr/local/bin/composer validate --no-check-publish
/usr/bin/php8.3 /usr/local/bin/composer audit --locked
npm audit --audit-level=low
npm run build
```

Audit sistem terbaru tersedia di `docs/SYSTEM_AUDIT.md`.

## Versioning dan Rilis

Versi memakai format `vYYYY.MM.DD`.

Untuk membuat rilis:

1. Pastikan test, audit dependency, dan build lulus.
2. Perbarui `VERSION`, fallback `config/admin.php`, `.env.example`, dan `APP_VERSION` produksi.
3. Tambahkan section paling atas di `CHANGELOG.md` dengan bahasa yang dapat dipahami admin.
4. Commit seluruh perubahan, buat tag yang sama dengan versi, lalu push ke repository resmi.
5. Setelah deploy, periksa halaman publik, booking, sitemap, security header, dan panel admin.
