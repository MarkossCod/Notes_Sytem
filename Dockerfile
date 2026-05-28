FROM php:8.3-apache

# Instala extensões necessárias
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev nodejs npm \
    && docker-php-ext-install pdo pdo_mysql mbstring

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configura Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf
RUN a2enmod rewrite

# Copia o projeto
WORKDIR /var/www/html
COPY . .

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader

# Instala dependências JS e compila assets
RUN npm install && npm run build

# Permissões
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Gera chave
RUN cp .env.example .env && php artisan key:generate

EXPOSE 80