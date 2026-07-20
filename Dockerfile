# Imagem de producao com Apache, PHP e extensoes exigidas pela aplicacao.
FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev nodejs npm \
    && docker-php-ext-install pdo pdo_mysql mbstring

# Disponibiliza o Composer sem manter uma segunda imagem em execucao.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Expoe somente a pasta public como raiz HTTP.
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . .

# Instala dependencias otimizadas e gera os recursos estaticos de producao.
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Autoriza o Apache a gravar cache, sessoes e logs do Laravel.
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
# Executa as migracoes antes de iniciar o Apache.
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]