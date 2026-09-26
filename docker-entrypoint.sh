#!/bin/sh
set -e

# Host database dibaca langsung dari .env, dan HANYA untuk keperluan log.
# Nilai yang benar-benar dipakai aplikasi tetap dibaca dari .env oleh
# CodeIgniter -- tidak ada konfigurasi database yang ditulis di Dockerfile
# maupun docker-compose.yml, sehingga hanya ada satu sumber kebenaran.
DB_HOST="$(sed -n 's/^database\.default\.hostname[[:space:]]*=[[:space:]]*//p' /var/www/html/.env 2>/dev/null | head -1 | tr -d "\"' ")"
[ -n "$DB_HOST" ] || DB_HOST="(tidak terbaca dari .env)"

echo "[entrypoint] database: ${DB_HOST}"

# ═══ Mode migrate-saja ═══════════════════════════════
# Dipanggil dari host dengan:  docker compose run --rm migrate
#
# Jalankan migrate + seed lalu keluar tanpa menyalakan Apache. Dipisah dari
# container aplikasi supaya restart, stop, dan enable tidak pernah menyentuh
# database: migrasi hanya terjadi saat langkah deploy yang disengaja.
if [ "${MIGRATE_ONLY:-0}" = "1" ]; then
    # Database berada di luar Docker dan compose tidak punya service database
    # untuk depended-on, jadi menunggu di sini murni jaring pengaman: MySQL
    # belum tentu siap menerima koneksi saat perintah ini dijalankan.
    #
    # Menunggu TANPA BATAS lalu langsung bermigrasi begitu database terbaca.
    # Apache tidak involved di mode ini, jadi menahan tidak merugikan siapa pun.
    attempt=0
    waited=0

    until php spark migrate:status >/dev/null 2>&1; do
        attempt=$((attempt + 1))
        waited=$((waited + 2))

        if [ "$attempt" -eq 1 ]; then
            echo "[entrypoint] '${DB_HOST}' belum terjangkau, menunggu tanpa batas"
        fi

        # Detak denyut supaya jelas proses ini MENUNGGU, bukan macet. Tanpa
        # batas, jeda yang sepi membuat ini tidak bisa dibedakan dari hang.
        if [ $((attempt % 8)) -eq 0 ]; then
            echo "[entrypoint] masih menunggu '${DB_HOST}' (${waited} detik)"
        fi

        sleep 2
    done

    if [ "$waited" -gt 0 ]; then
        echo "[entrypoint] database siap setelah ${waited} detik"
    fi

    echo "[entrypoint] menjalankan migrasi"
    # Sengaja TIDAK memakai `|| echo` seperti mode aplikasi: di mode ini
    # kegagalan harus menghentikan deploy. Kalau migrasi gagal dan exit non-zero,
    # `docker compose run` dan `docker compose up` sama-sama berhenti, jadi
    # aplikasi versi lama tidak pernah dilayani dengan skema setengah jadi.
    php spark migrate --all

    # Idempoten: upsert berdasarkan nama, jadi baris yang sudah ada tidak
    # ditimpa dan baris lama tidak dikalikan.
    echo "[entrypoint] mengisi data awal (idempoten)"
    php spark db:seed DatabaseSeeder

    echo "[entrypoint] mode migrate-saja selesai, keluar"
    exit 0
fi

# ═══ Mode aplikasi ══════════════════════════════════
# Container aplikasi TIDAK menjalankan migrasi. Satu-satunya jalan untuk
# memperbarui skema adalah `docker compose run --rm migrate`.
#
# Pemeriksaan di bawah tidak menulis apa pun ke database. Gunanya hanya
# membuat "lupa migrate" kelihatan seketika di log, bukan muncul sebagai
# 'table not found' yang membingungkan saat aplikasi dipakai.
if ! php spark db:table migrations >/dev/null 2>&1; then
    echo "[entrypoint] PERINGATAN: tabel 'migrations' belum ada di '${DB_HOST}'."
    echo "[entrypoint] Jalankan:  docker compose run --rm migrate"
    echo "[entrypoint] (jawaban 'Tidak ada tabel' berarti migrasi belum pernah dijalankan)"
fi

# Teruskan ke entrypoint bawaan image (docker-php-entrypoint) yang menjalankan
# docker-php-ext-* untuk ekstensi yang di-install saat runtime.
exec docker-php-entrypoint "$@"
