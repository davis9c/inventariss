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

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/writable \
    && chmod -R 755 /var/www/html/public/vendor \
    && chmod -R 755 /var/www/html/public/js

# Konfigurasi khusus container. container-env.php disuntikkan ke $_ENV
# sebelum CodeIgniter membaca .env host yang ter-bind-mount, supaya service
# `db` di docker-compose.yml benar-benar dipakai. Penjelasan ada di
# docker/container-env.php.
COPY docker/container-env.php /usr/local/share/container-env.php
COPY docker/php-container.ini /usr/local/etc/php/conf.d/docker-container.ini

# Entrypoint: menyiapkan skema database sebelum Apache melayani trafik.
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

# Expose port 80
EXPOSE 80

# Start Apache
ENTRYPOINT ["app-entrypoint"]
CMD ["apache2-foreground"]
