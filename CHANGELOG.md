# Changelog

Semua perubahan penting proyek ini dicatat di file ini. Format mengikuti ringkasan rilis per versi agar admin dapat membaca catatan update dari panel.

## [v2026.06.19] - 2026-06-19

- Website kini lebih siap ditemukan di Google melalui sitemap, canonical, metadata sosial, dan structured data.
- Admin dapat mengatur informasi SEO global, setiap halaman, serta paket umrah langsung dari panel.
- Halaman Pembaruan Sistem kini lebih ringkas, jelas, dan mengikuti urutan kerja admin awam.
- Keamanan instalasi dan pembaruan diperkuat melalui credential wajib, security header, serta penggunaan PHP 8.3 yang konsisten.
- Halaman privat booking dan seluruh panel admin dikeluarkan dari indeks mesin pencari.
- URL HTTP dan trailing slash diarahkan permanen ke alamat HTTPS canonical.
- Sitemap dinamis hanya memuat halaman dan paket yang aktif serta diizinkan untuk diindeks.
- Social image, gambar konten, dan layout responsif dioptimalkan untuk stabilitas tampilan.
- Dependency frontend yang terkena advisory telah diperbarui; audit Composer dan npm kini bersih.
- Dokumentasi setup, deployment, RBAC, booking, SEO, update, dan audit sistem diperbarui sesuai kondisi produksi.
- Automated test mencakup workflow publik, booking, RBAC, SEO, pengaturan, tracking, dan system update.

## [v2026.06.16] - 2026-06-16

- Pemesanan online kini dilengkapi cek status dan proses persetujuan admin.
- Kuota jadwal diperbarui otomatis saat pemesanan disetujui, ditolak, atau dibatalkan.
- Pengelolaan pengguna dan pembagian hak akses kini tersedia di panel admin.
- Pengaturan akun dan website kini tersusun dalam satu menu Pengaturan.
- Logo, ikon situs, dan gambar beranda dapat diganti langsung dari panel admin.
- Aplikasi dapat diperbarui langsung dari sumber GitHub resmi.
- Akses ke sumber pembaruan privat dapat disimpan dengan aman.
- Tampilan panel admin, dashboard, mode gelap/terang, dan tampilan mobile telah dirapikan.
- Panduan pemasangan server telah dilengkapi untuk pengelola teknis.
