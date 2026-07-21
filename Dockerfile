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

# Pastikan hanya ada SATU MPM yang aktif (prefork adalah bawaan php-apache)
# Matikan event dan worker jika sempat terinstal oleh dependensi lain
RUN a2dismod mpm_event mpm_worker mpm_prefork || true \
    && a2enmod mpm_prefork

# Install ekstensi PHP untuk Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Konfigurasi DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# PENTING UNTUK RAILWAY: Apache harus listen ke environment variable $PORT, bukan selalu port 80
RUN sed -i 's/80/${PORT:-80}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

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
RUN echo '#!/bin/bash\n\
php artisan migrate --force\n\
exec apache2-foreground\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
