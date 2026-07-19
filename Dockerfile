FROM php:8.2-apache

# Mengatur working directory
WORKDIR /var/www/html

# Menginstal dependensi sistem dan ekstensi PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql zip gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Mengaktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Menginstal Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Menyalin file konfigurasi apache khusus untuk public folder Laravel
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Menyalin seluruh file aplikasi
COPY . .

# Memberikan permission yang tepat untuk storage dan bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Menyalin entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Mengekspos port 80
EXPOSE 80

# Menjalankan entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Command default jika entrypoint selesai (menjalankan apache di foreground)
CMD ["apache2-foreground"]
