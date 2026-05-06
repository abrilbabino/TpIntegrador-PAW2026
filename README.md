# PawMap

**PawMap** es una aplicación web orientada a facilitar la **adopción de mascotas** que conecta adoptantes con refugios a través de un **mapa interactivo**. La plataforma permite visualizar mascotas disponibles según ubicación y acceder a información detallada sobre cada animal para favorecer adopciones responsables.

## Objetivo

El objetivo de PawMap es centralizar la información de animales en adopción y simplificar el proceso de búsqueda y contacto con refugios. La aplicación permite explorar mascotas disponibles mediante un mapa, aplicar filtros según características del animal y visualizar perfiles con contenido multimedia.

## Funcionalidades principales

- **Registro y autenticación de usuarios y refugios** con roles diferenciados.
- **Perfil de usuario**, que incluye información personal, mascotas favoritas y solicitudes de adopción realizadas.
- **Perfiles de mascotas** con información detallada, fotos, videos y elementos interactivos.
- **Mapa interactivo** que muestra mascotas disponibles según su ubicación.
- **Filtros de búsqueda** por especie, tamaño, edad, temperamento y localización.
- **Panel de administración para refugios**, que permite agregar, editar y eliminar perfiles de mascotas.
- **Solicitudes de adopción y contacto** entre adoptantes y refugios.
- **Test de compatibilidad**, que recomienda mascotas según el estilo de vida y el entorno del adoptante.

## ejecucion

`php -S localhost:3000 -t public`

## migration

```
phinx rollback -e development -t 0
phinx migrate
phinx seed:run

phinx migrate -e development

php vendor/bin/phinx migration:run -e development

```

## composer

```composer install

```

```composer update

```
