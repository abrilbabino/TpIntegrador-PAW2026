# Deploy a Render con Docker

## ¿Para qué este Dockerfile?

La página está hecha con PHP y PostgreSQL. Para subirla a Render (un servicio gratis en la nube), necesitamos "empaquetar" el proyecto en una imagen Docker. Render agarra esa imagen y la ejecuta en sus servidores.

## Qué hace cada cosa

### `FROM php:8.4-apache`

Arrancamos desde una imagen oficial que ya viene con PHP 8.4 y Apache instalado. 

### `RUN apt-get update && apt-get install -y libpq-dev git unzip && docker-php-ext-install pdo pdo_pgsql && a2enmod rewrite`

- `libpq-dev`: librería para que PHP pueda hablar con PostgreSQL
- `git` y `unzip`: los necesita Composer para descargar dependencias
- `docker-php-ext-install pdo pdo_pgsql`: instala la extensión de PHP para conectar a PostgreSQL

### `COPY --from=composer:latest /usr/bin/composer /usr/bin/composer`

Copia el ejecutable de Composer desde la imagen oficial de Composer. Así no tenemos que instalar PHP solo para bajar las dependencias.

### `WORKDIR /var/www/html`

Le decimos a Docker que todos los comandos de ahora en adelante se ejecuten desde esa carpeta (es la carpeta donde Apache busca los archivos).

### `ENV APACHE_DOCUMENT_ROOT=/var/www/html/Entrega3/public`

Le decimos a Apache que la carpeta pública es `Entrega3/public`, no la raíz del proyecto. Así nadie puede acceder a archivos como `.env` desde el navegador.

### `RUN sed ...`

Modifica los archivos de configuración de Apache para que apunten a la carpeta que le acabamos de decir.

### `COPY Entrega3/composer.json Entrega3/composer.lock* Entrega3/`

Copia primero solo los archivos de dependencias. Esto es un truquito para que Docker cachee las dependencias: si no cambiamos `composer.json`, Docker no vuelve a instalar todo.

### `RUN cd /var/www/html/Entrega3 && composer install --no-interaction --optimize-autoloader`

Instala las dependencias de PHP (los paquetes que están en `composer.json`).

### `COPY . .`

Copia todo el proyecto a la imagen. Como ya instalamos las dependencias antes, este paso es más rápido porque aprovecha la cache.

### `RUN cd Entrega3 && composer dump-autoload --optimize`

Regenera el autoloader de Composer con las rutas definitivas (optimizado para producción).

### `RUN chown -R www-data:www-data /var/www/html`

Le da permisos al usuario de Apache (`www-data`) para que pueda leer los archivos.

### Entrypoint script (`/entrypoint.sh`)

```sh
#!/bin/sh
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
exec "$@"
```

Render asigna un puerto al azar cuando arranca el contenedor (lo guarda en la variable `$PORT`). Apache por defecto escucha en el puerto 80. Este script, antes de arrancar Apache, reemplaza el `80` por el puerto que nos dio Render. Después ejecuta lo que venga en `CMD` (que es `apache2-foreground`).

### `EXPOSE 80`

Le avisa a Docker que el contenedor usa el puerto 80. Es solo documentación.

### `ENTRYPOINT ["/entrypoint.sh"]` + `CMD ["apache2-foreground"]`

- `ENTRYPOINT` es el script que se ejecuta siempre al arrancar el contenedor
- `CMD` es el argumento que le pasamos a ese script (`apache2-foreground` = arrancar Apache)

## Cómo se usa

1. Subir todo a GitHub (incluyendo el Dockerfile)
2. En Render: New → Web Service → conectar repo
3. Elegir "Docker" como entorno
4. Crear una base de datos PostgreSQL en Render y copiar los datos de conexión
5. Configurar las variables de entorno en Render (`DB_HOSTNAME`, `DB_DBNAME`, etc.)
6. Render builddea la imagen y la corre automágicamente
7. Desde la terminal de Render, correr las migrations:
   ```bash
   cd Entrega3 && php vendor/bin/phinx migrate -e production && php vendor/bin/phinx seed:run -e production
   ```
