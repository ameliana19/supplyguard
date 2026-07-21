# Menggunakan PHP 8.2 dengan Apache
FROM php:8.2-apache

# Install dependencies sistem
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    nodejs \
    npm

# Install ekstensi PHP untuk Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Konfigurasi DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# PENTING UNTUK RAILWAY: Penggantian port tidak boleh dilakukan saat build (karena $PORT kosong).
# Kita memindahkannya ke dalam start.sh agar dieksekusi saat runtime.

# Set working directory
WORKDIR /var/www/html

# Salin kode sumber
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependensi PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Install & Build frontend (Vite)
RUN npm install
RUN npm run build

# Atur permission
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Gunakan script entrypoint untuk memastikan migration berjalan sebelum Apache start
# tanpa menimpa default CMD apache2-foreground
RUN echo '#!/bin/bash' > /usr/local/bin/start.sh \
    && echo 'sed -i "s/80/\${PORT:-80}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf' >> /usr/local/bin/start.sh \
    && echo 'php artisan migrate --force' >> /usr/local/bin/start.sh \
    && echo 'exec apache2-foreground' >> /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

# --- FINAL MPM CLEANUP & VALIDATION ---
# Audit dan hapus secara fisik semua jejak mpm_event dan mpm_worker
# dari SELURUH folder konfigurasi Apache untuk memastikan hanya prefork yang bertahan.
RUN rm -f /etc/apache2/mods-enabled/mpm_event* \
    && rm -f /etc/apache2/mods-enabled/mpm_worker* \
    && rm -f /etc/apache2/mods-available/mpm_event* \
    && rm -f /etc/apache2/mods-available/mpm_worker* \
    && rm -f /etc/apache2/conf-enabled/mpm_event* \
    && rm -f /etc/apache2/conf-enabled/mpm_worker* \
    && rm -f /etc/apache2/conf-available/mpm_event* \
    && rm -f /etc/apache2/conf-available/mpm_worker* \
    && sed -i '/mpm_event/d' /etc/apache2/apache2.conf \
    && sed -i '/mpm_worker/d' /etc/apache2/apache2.conf

# Validasi ketat di akhir build: harus memunculkan 'mpm_prefork_module' dan 'Syntax OK'
RUN echo "=== DAFTAR MPM AKTIF ===" \
    && apache2ctl -M | grep mpm \
    && echo "=== TEST KONFIGURASI ===" \
    && apache2ctl configtest


CMD ["/usr/local/bin/start.sh"]
