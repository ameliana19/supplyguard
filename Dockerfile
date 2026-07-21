# Menggunakan PHP 8.2 FPM
FROM php:8.2-fpm

# Install dependencies sistem, termasuk nginx
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
    npm \
    nginx

# Install ekstensi PHP untuk Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Set working directory
WORKDIR /var/www/html

# Salin kode sumber
COPY . .

# Hapus konfigurasi default Nginx dan salin konfigurasi kustom Laravel kita
RUN rm /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default || true

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependensi PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Install & Build frontend (Vite)
RUN npm install
RUN npm run build

# Atur permission (www-data adalah user default nginx dan php-fpm di Debian)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Pastikan PHP-FPM tidak men-clear environment variables agar Laravel bisa membaca env dari Railway
RUN echo "clear_env = no" >> /usr/local/etc/php-fpm.d/zz-docker.conf

# Buat script startup
RUN echo '#!/bin/bash\n\
# Ganti <PORT> di konfigurasi nginx dengan $PORT Railway\n\
sed -i "s/<PORT>/$PORT/g" /etc/nginx/sites-available/default\n\
\n\
# Hapus cache bawaan (jika ada) sebelum Laravel menyentuh database\n\
php artisan config:clear\n\
php artisan cache:clear\n\
php artisan route:clear\n\
php artisan view:clear\n\
\n\
# Pastikan APP_KEY tersedia\n\
if [ -z "$APP_KEY" ]; then\n\
    echo "APP_KEY is missing. Generating one..."\n\
    php artisan key:generate --force\n\
fi\n\
\n\
# Jalankan migration\n\
php artisan migrate --force\n\
\n\
# Optimasi caching untuk production\n\
php artisan optimize\n\
\n\
# Perbaiki permission agar FPM (www-data) bisa menulis ke storage\n\
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache\n\
\n\
# Jalankan PHP-FPM di background\n\
php-fpm -D\n\
\n\
# Jalankan Nginx di foreground (utama)\n\
exec nginx -g "daemon off;"\n\
' > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
