# Apache Root Fallback

Gunakan fallback ini hanya jika control panel hosting tidak mengizinkan document root diarahkan ke `public/`.

Repository ini menyertakan dua file kompatibilitas di root:

- `.htaccess` root: memblokir folder/file sensitif dan meneruskan request ke `public/`.
- `index.php` root: shim yang memuat `public/index.php`.

Rekomendasi tetap memakai vhost dengan `DocumentRoot /www/wwwroot/lulu.kapul.my.id/public`. Fallback root tidak direkomendasikan untuk Nginx karena Nginx tidak membaca `.htaccess`; gunakan contoh `nginx.conf.example` dan arahkan `root` ke `public/`.
