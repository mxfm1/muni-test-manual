FROM php:8.4-apache

WORKDIR /var/www/html

# Dependencias del sistema + extensiones PHP
RUN apt-get update \
    && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    gd \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Código de la aplicación
COPY . .

# Dependencias PHP
RUN composer install \
    --no-dev \
    --optimize-autoloader

# PHP/Apache corre como www-data y necesita escribir las imágenes subidas.
RUN mkdir -p /var/www/html/public/uploads/media \
    && chown -R www-data:www-data /var/www/html/public/uploads

# Apache
# Apache
RUN a2enmod rewrite

RUN cat <<'EOF' > /etc/apache2/conf-available/manual-muni.conf
<Directory /var/www/html/public>
    AllowOverride All
    Require all granted
</Directory>
EOF

RUN a2enconf manual-muni

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

# /public será el DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

EXPOSE 80

CMD ["apache2-foreground"]
