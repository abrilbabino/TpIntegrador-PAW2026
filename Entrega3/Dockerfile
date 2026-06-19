FROM php:8.5-apache

# 1. Habilitar mod_rewrite para el ruteo MVC
RUN a2enmod rewrite

# 2. Instalar dependencias necesarias para Composer y la base de datos (PostgreSQL y MySQL)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql pdo_pgsql zip

# 3. Cloud Run requiere que el contenedor escuche en el puerto 8080
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 4. Configurar Apache para apuntar a la carpeta /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# 5. Permitir el uso de archivos .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 6. Copiar el código fuente
COPY . /var/www/html/

# 7. Instalar dependencias de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# 8. Asignar permisos al usuario de Apache, crear la carpeta/archivo de logs y la carpeta de uploads
RUN chown -R www-data:www-data /var/www/html/ && \
    mkdir -p /var/www/html/logs && \
    touch /var/www/html/logs/app.log && \
    chown -R www-data:www-data /var/www/html/logs && \
    chmod -R 775 /var/www/html/logs && \
    mkdir -p /var/www/html/public/assets/img/uploads && \
    chown -R www-data:www-data /var/www/html/public/assets/img/uploads && \
    chmod -R 775 /var/www/html/public/assets/img/uploads
