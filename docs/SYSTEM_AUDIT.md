# Audit Sistem

Tanggal audit: 19 Juni 2026  
Target rilis: `v2026.06.19`  
Lingkungan: production, PHP 8.3, Laravel 13, Filament 5, MySQL, Nginx

## Ringkasan

Sistem telah diaudit pada lapisan arsitektur, alur bisnis booking, kontrol akses, keamanan aplikasi, SEO/indexing, dependency, database, build, deployment, dan system update. Kondisi saat audit dinilai layak operasional dengan automated test lengkap untuk workflow utama.

Validasi terakhir:

- PHPUnit: 51 test, 489 assertion, seluruhnya lulus.
- Composer manifest valid dan tidak memiliki advisory/abandoned package.
- npm audit: 0 vulnerability setelah lockfile diperbarui.
- Platform requirement production lulus melalui `/usr/bin/php8.3`.
- Vite production build berhasil.
- Seluruh migration production berstatus `Ran`.
- Tidak ada failed queue job dan tidak ada scheduled task aktif.

## Cakupan dan Hasil

| Area | Kondisi | Kontrol utama |
| --- | --- | --- |
| Halaman publik | Baik | Server-rendered, record aktif, validasi output Blade, responsive layout |
| Booking | Baik | Form Request, throttle, transaction, row lock, token publik acak |
| Kuota jadwal | Baik | Berkurang saat approval dan pulih satu kali saat cancel approved |
| Status booking | Baik | Lookup dua data, pesan gagal generik, data kontak di-mask, noindex |
| Admin/RBAC | Baik | Panel permission, role terpisah, action permission booking/update |
| Upload | Baik | Disk terpisah, image validation, resize/optimasi GD, public symlink |
| Visitor tracking | Baik | IP dan user-agent di-hash dengan HMAC; nilai mentah tidak disimpan |
| SEO | Baik | Canonical, sitemap, robots, social metadata, JSON-LD, index policy |
| Security header | Baik | nosniff, SAMEORIGIN, strict referrer policy, HSTS dari Nginx |
| System update | Terkontrol | Token terenkripsi, askpass sementara, output disensor, permission khusus |
| Dependency | Baik | Composer audit dan npm audit tanpa vulnerability |
| Test/build | Baik | Feature test lintas modul dan production asset build |

## Temuan yang Diselesaikan

### Dependency build kritis

`concurrently@9.2.1` membawa versi `shell-quote` yang terkena advisory command injection. Lockfile telah diperbarui ke `concurrently@9.2.3`; `npm audit` sekarang melaporkan 0 vulnerability.

### Perbedaan binary PHP

Binary `php` default server tidak memiliki `fileinfo`/`mbstring` lengkap, sedangkan `/usr/bin/php8.3` memenuhi platform requirement. System updater kini menjalankan Composer melalui PHP 8.3 yang sama dengan Artisan. Dokumentasi dan command operasional juga menggunakan binary eksplisit.

### Credential admin awal

Fallback password admin bawaan dihapus. Seeder sekarang mewajibkan `ADMIN_INITIAL_EMAIL` dan `ADMIN_INITIAL_PASSWORD`; PHPUnit memakai credential khusus environment testing.

### Header keamanan produksi

Respons aplikasi sebelumnya hanya mengandalkan sebagian header Nginx. Middleware aplikasi sekarang menambahkan `X-Content-Type-Options`, `X-Frame-Options`, dan `Referrer-Policy` secara konsisten.

### Kesiapan Google Search

Sitemap, canonical HTTPS, metadata per halaman/paket, social preview, structured data, Search Console verification, dan noindex halaman privat telah ditambahkan. HTTP dan trailing slash dinormalisasi dengan redirect permanen.

### Informasi system update

Halaman update disederhanakan untuk admin awam. Detail commit/remote/command dihapus dari UI, urutan tindakan dibuat jelas, dan tombol update hanya aktif setelah versi baru ditemukan.

## Risiko Operasional Tersisa

### Update belum memiliki rollback otomatis

Updater memakai `git reset --hard` dan berhenti pada command pertama yang gagal. Worktree harus bersih serta backup database/upload harus tersedia sebelum update. Pemulihan dilakukan manual dari Git dan backup.

### Google Search Console bersifat eksternal

Aplikasi sudah menyiapkan verification meta dan sitemap, tetapi verifikasi property, submit sitemap, serta monitoring indexing/Core Web Vitals tetap harus dilakukan oleh pemilik akun Search Console.

### Timezone aplikasi masih UTC

Konfigurasi Laravel saat ini memakai UTC. Ini konsisten untuk penyimpanan dan log, tetapi waktu yang ditampilkan admin perlu diperhatikan bila operasional mengharuskan zona waktu lokal Kalimantan (`Asia/Makassar`). Perubahan timezone harus direncanakan bersama audit data timestamp lama.

### Public response masih memakai session middleware

Halaman publik HTML masih mengirim session/XSRF cookie dan menggunakan cache private. Ini tidak menghambat indexing, tetapi response caching/CDN dapat dioptimalkan lebih lanjut jika trafik meningkat. Endpoint sitemap sudah stateless dan cacheable.

### Queue dan scheduler

Driver queue menggunakan database, tetapi belum ada workflow wajib yang diproses asynchronous. Scheduler juga kosong. Jika email, reminder, atau pekerjaan background ditambahkan, supervisor queue dan cron scheduler wajib disiapkan.

## Pemeriksaan Produksi

- `https://lulu.kapul.my.id/` merespons `200`.
- HTTP merespons `301` ke HTTPS canonical.
- `/sitemap.xml` merespons XML `200`, cache public, dan tanpa session cookie.
- `/robots.txt` menunjuk sitemap HTTPS.
- `/admin/login` mengirim `X-Robots-Tag: noindex, nofollow, nosnippet`.
- `.env` dan `composer.json` tidak dapat diakses dari web root.
- HSTS aktif dari Nginx.

## Checklist Audit Berikutnya

1. Jalankan test, Composer audit, npm audit, dan build sebelum setiap rilis.
2. Periksa backup/restore database dan upload secara berkala.
3. Tinjau role dan permission setiap ada modul admin baru.
4. Tinjau retention visitor log dan booking sesuai kebijakan privasi perusahaan.
5. Pantau Search Console setelah sitemap disubmit.
6. Uji proses update dan prosedur pemulihan pada staging sebelum rilis besar.
