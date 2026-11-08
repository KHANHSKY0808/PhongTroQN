FROM php:8.2-apache

# Cài đặt các gói hệ thống và thư viện cần thiết
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        gd \
        zip \
        fileinfo \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Bật Apache mod_rewrite
RUN a2enmod rewrite

# Cấu hình DocumentRoot trỏ vào thư mục public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Cho phép .htaccess ghi đè trong thư mục DocumentRoot
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/override.conf \
    && a2enconf override

# Cài Composer từ official Composer image
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy toàn bộ mã nguồn vào container
COPY . /var/www/html

# Cài đặt PHP dependencies bằng Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Đảm bảo quyền ghi cho các thư mục lưu trữ và upload
RUN mkdir -p /var/www/html/storage/logs /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/public/uploads \
    && chmod -R 775 /var/www/html/storage /var/www/html/public/uploads

EXPOSE 80

CMD ["apache2-foreground"]

