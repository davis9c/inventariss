#!/bin/sh
set -e

# Host database dibaca langsung dari .env, dan HANYA untuk keperluan log.
# Nilai yang benar-benar dipakai aplikasi tetap dibaca dari .env oleh
# CodeIgniter -- tidak ada konfigurasi database yang ditulis di Dockerfile
# maupun docker-compose.yml, sehingga hanya ada satu sumber kebenaran.
DB_HOST="$(sed -n 's/^database\.default\.hostname[[:space:]]*=[[:space:]]*//p' /var/www/html/.env 2>/dev/null | head -1 | tr -d "\"' ")"
[ -n "$DB_HOST" ] || DB_HOST="(tidak terbaca dari .env)"

echo "[entrypoint] database: ${DB_HOST}"
echo "[entrypoint] menunggu database (maks 30 detik)"

# Compose tidak punya service database untuk depended-on, jadi jeda ini murni
# jaring pengaman bila server MySQL eksternal belum siap menerima koneksi.
attempt=0
until php spark migrate:status >/dev/null 2>&1; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge 15 ]; then
        break
    fi
    sleep 2
done

if [ "$attempt" -ge 15 ]; then
    echo "[entrypoint] PERINGATAN: '${DB_HOST}' belum terjangkau, melewati migrate/seed."
    echo "[entrypoint] Periksa database.default.hostname / .database / .username / .password di .env."
else
    echo "[entrypoint] menjalankan migrasi"
    php spark migrate --all || echo "[entrypoint] migrasi gagal, melanjutkan"

    # Idempoten: upsert berdasarkan nama, jadi baris yang sudah ada tidak
    # ditimpa dan baris lama tidak dikalikan. Aman dijalankan setiap start.
    echo "[entrypoint] mengisi data awal (idempoten)"
    php spark db:seed DatabaseSeeder || echo "[entrypoint] seed gagal, melanjutkan"
fi

# Teruskan ke entrypoint bawaan image (docker-php-entrypoint) yang menjalankan
# docker-php-ext-* untuk ekstensi yang di-install saat runtime.
exec docker-php-entrypoint "$@"
