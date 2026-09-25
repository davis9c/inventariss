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

# Nama env var tanpa titik, karena Docker membuang nama bertitik.
DB_HOST="$(php -r 'echo getenv("DB_HOST") ?: "db";' 2>/dev/null || echo db)"

echo "[entrypoint] menunggu database di '${DB_HOST}' (maks 60 detik)"

i=0
until php spark migrate:status >/dev/null 2>&1; do
    i=$((i + 1))
    if [ "$i" -ge 30 ]; then
        echo "[entrypoint] PERINGATAN: database belum terjangkau setelah ${i} percobaan."
        echo "[entrypoint] Melanjutkan tanpa migrate/seed -- cek konfigurasi database."
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
