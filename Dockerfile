FROM php:8.4-apache

RUN apt-get update && apt-get install -y libpq-dev git unzip \
    && docker-php-ext-install pdo pdo_pgsql \
    && a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

ENV APACHE_DOCUMENT_ROOT=/var/www/html/Entrega3/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY Entrega3/composer.json Entrega3/composer.lock* Entrega3/
RUN cd /var/www/html/Entrega3 && composer install --no-interaction --no-scripts --no-autoloader --ignore-platform-req=ext-pdo_mysql

COPY . .
RUN cd Entrega3 && composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p /var/www/html/Entrega3/logs \
    && chmod 777 /var/www/html/Entrega3/logs

RUN printf '#!/bin/sh\nmkdir -p /var/www/html/Entrega3/logs\nsed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf\ncd /var/www/html/Entrega3 && php vendor/bin/phinx migrate -e production 2>/dev/null; php vendor/bin/phinx seed:run -e production 2>/dev/null\nexec "$@"\n' > /entrypoint.sh && chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]

CMD ["apache2-foreground"]
