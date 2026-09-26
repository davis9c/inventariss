# Frontend versi 1.4+ dipakai karena entrypoint di bawah ditulis dengan
# heredoc. Directive ini membuat build deterministik dan tetap jalan di
# server dengan buildx versi lama.
# syntax=docker/dockerfile:1

FROM php:8.2-apache

# Install system dependencies
#
# libonig-dev wajib ada: sejak Debian trixie, mbstring menolak dikonfigurasi
# tanpa oniguruma, dan build berhenti dengan
# "Package requirements (oniguruma) were not met". Tanpa paket ini image
# sama sekali tidak bisa dibangun.
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    intl \
    mbstring \
    mysqli \
    pdo \
    pdo_mysql \
    zip \
    gd \
    opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set recommended PHP settings
RUN { \
    echo 'memory_limit = 256M'; \
    echo 'upload_max_filesize = 64M'; \
    echo 'post_max_size = 64M'; \
    echo 'max_execution_time = 300'; \
    echo 'max_input_time = 300'; \
    echo 'date.timezone = Asia/Jakarta'; \
    } > /usr/local/etc/php/conf.d/recommended.ini

# Set document root to public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .

# Apache hanya perlu MEMBACA berkas statis dan kode aplikasi, dan `COPY`
# sudah memberi izin 644/755 yang cukup untuk itu. Satu-satunya path yang
# harus bisa ditulis adalah `writable`: session, log, cache, dan berkas
# unggahan.
#
# `chown -R` ke seluruh /var/www/html sengaja dihindari. Ada ribuan berkas
# di vendor/ sehingga build melambat, dan tidak ada yang perlu ditulis di
# sana.
#
# `chmod` untuk public/vendor dan public/js juga dihapus: keduanya tidak
# butuh izin khusus. public/vendor kadang juga tidak ada sama sekali --
# `chmod` pada path yang tidak ada mengembalikan exit 1 dan menggagalkan
# build di server yang repo-nya hasil git clone.
RUN chown -R www-data:www-data /var/www/html/writable

# ─── Entrypoint ──────────────────────────────────────
# Menyiapkan skema database sebelum Apache menerima trafik, supaya tidak ada
# kondisi di mana container sudah melayani permintaan tapi tabelnya belum ada.
#
# Script ditulis inline lewat heredoc, bukan disalin dari berkas di dalam
# repo, supaya seluruh konfigurasi container hanya tinggal di dua tempat:
# Dockerfile dan docker-compose.yml.
#
# Kegagalan sengaja tidak dibiarkan fatal. Database berada di luar Docker
# (lihat docker-compose.yml), jadi belum tentu hidup ketika container start.
# Apache tetap dinyalakan: halaman login tetap bisa dibuka, dan pesan
# kesalahannya jauh lebih berguna daripada container yang restart terus tanpa
# penjelasan.
RUN <<'SHELL'
cat > /usr/local/bin/app-entrypoint <<'SCRIPT'
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
SCRIPT
chmod +x /usr/local/bin/app-entrypoint
SHELL

# Expose port 80
EXPOSE 80

# Start Apache
ENTRYPOINT ["app-entrypoint"]
CMD ["apache2-foreground"]
