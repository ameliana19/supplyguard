# Menggunakan PHP 8.2 dengan Apache
FROM php:8.2-apache

# Install dependencies yang dibutuhkan sistem dan ekstensi PHP
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

# Install ekstensi PHP yang dibutuhkan Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Mengaktifkan mod_rewrite Apache (dibutuhkan Laravel untuk routing)
RUN a2enmod rewrite

# Mengubah DocumentRoot Apache ke folder /public Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set working directory
WORKDIR /var/www/html

# Menyalin seluruh file project (kecuali yang ada di .dockerignore)
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Menjalankan composer install untuk menginstall dependensi PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Install dependensi frontend dan build menggunakan Vite
RUN npm install
RUN npm run build

# Memberikan permission pada folder storage dan bootstrap/cache agar bisa ditulis oleh web server
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
