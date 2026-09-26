#!/bin/sh
#
# Entrypoint container aplikasi.
#
# Menyiapkan skema database sebelum Apache menerima permintaan, supaya tidak
# ada kondisi di mana container sudah melayani trafik tapi tabelnya belum ada.
#
# Ketiga langkah di sini idempoten, jadi aman dijalankan setiap kali container
# start ulang:
#
#   migrate  -> hanya menjalankan migrasi yang belum tercatat
#   seed     -> upsert berdasarkan nama, jadi data yang sudah ada tidak
#               ditimpa dan baris lama tidak dikalikan
#   wait     -> hanya menunggu, tidak mengubah apa pun
#
# Kegagalan sengaja tidak dibiarkan fatal. Kalau database belum terjangkau,
# Apache tetap dinyalakan: halaman login tetap bisa dibuka dan pesan
# kesalahannya jauh lebih berguna daripada container yang restart terus
# tanpa penjelasan.

set -e

# Nama host database dibaca dari .env dengan loader yang sama seperti
# aplikasi, supaya log menyebut server yang benar-benar dipakai.
#
# Compose tidak lagi punya service MySQL, jadi tidak ada lagi nama host
# "db" yang bisa dijadikan default; default seperti itu akan menyesatkan
# kalau .env menunjuk server lain.
DB_HOST="$(php -r '
require "/var/www/html/vendor/autoload.php";
require "/var/www/html/vendor/codeigniter4/framework/system/Config/DotEnv.php";
(new \CodeIgniter\Config\DotEnv("/var/www/html"))->load();
echo $_ENV["database.default.hostname"] ?? "?";
' 2>/dev/null || echo "?")"

echo "[entrypoint] menunggu database '${DB_HOST}' (maks 60 detik)"

i=0
until php spark migrate:status >/dev/null 2>&1; do
    i=$((i + 1))
    if [ "$i" -ge 30 ]; then
        echo "[entrypoint] PERINGATAN: database '${DB_HOST}' belum terjangkau setelah ${i} percobaan."
        echo "[entrypoint] Melanjutkan tanpa migrate/seed."
        echo "[entrypoint] Periksa database.default.hostname / .database / .password di .env."
        break
    fi
    sleep 2
done

if php spark migrate:status >/dev/null 2>&1; then
    echo "[entrypoint] menjalankan migrasi"
    php spark migrate --all || echo "[entrypoint] migrasi gagal, melanjutkan"

    echo "[entrypoint] mengisi data awal (idempoten)"
    php spark db:seed DatabaseSeeder || echo "[entrypoint] seed gagal, melanjutkan"
else
    echo "[entrypoint] Lewati migrate/seed karena database tidak terjangkau."
fi

# Teruskan ke entrypoint bawaan image (docker-php-entrypoint) yang
# menjalankan docker-php-ext-* untuk ekstensi yang di-install runtime.
exec docker-php-entrypoint "$@"
