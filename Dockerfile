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
# Script-nya ada di berkas repo (docker-entrypoint.sh), bukan ditulis inline
# di sini, supaya bisa dibaca dan diperbaiki tanpa menyentuh Dockerfile.
#
# Kegagalan sengaja tidak dibiarkan fatal. Database berada di luar Docker
# (lihat docker-compose.yml), jadi belum tentu hidup ketika container start.
# Apache tetap dinyalakan: halaman login tetap bisa dibuka, dan pesan
# kesalahannya jauh lebih berguna daripada container yang restart terus tanpa
# penjelasan.
#
# Layer COPY dipisah dari `COPY . .` supaya build cache tidak hilang setiap kali
# kode aplikasi berubah.
COPY docker-entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

# Expose port 80
EXPOSE 80

# Start Apache
ENTRYPOINT ["app-entrypoint"]
CMD ["apache2-foreground"]
