# PawMap

**PawMap** es una aplicación web orientada a facilitar la **adopción de mascotas** que conecta adoptantes con refugios a través de un **mapa interactivo**. La plataforma permite visualizar mascotas disponibles según ubicación y acceder a información detallada sobre cada animal para favorecer adopciones responsables.

## Objetivo

El objetivo de PawMap es centralizar la información de animales en adopción y simplificar el proceso de búsqueda y contacto con refugios. La aplicación permite explorar mascotas disponibles mediante un mapa, aplicar filtros según características del animal y visualizar perfiles con contenido multimedia.

## Funcionalidades principales
### Modulo de Usuario y Acceso
**Gestion de Sesiones:** Login y registro diferenciado para Adoptantes y Refugios.  
**Perfil de Usuario:**
- Adoptante: Edición de datos, "Favoritos" y estado de solicitudes.  
- Refugio: Datos institucionales, contacto y ubicación.  

### Modulo de Navegacion e Información
**Página "¿Cómo Adoptar?":** con contenido informativo sobre los pasos y responsabilidades de la
adopción.

**Seccion de Donaciones:** Interfaz que lista los metodos de donacion de cada refugio (CBU, Alias
o links externos de pago) recuperados de la base de datos.

### Modulo de Refugios
**Listado dinámico de todos los refugios registrados.**

**Filtros de Refugios:** Buscador por nombre o localidad ( Mercedes, Lujan, etc.) para que el
usuario encuentre los mas cercanos.

### Módulo de Mascotas
**Mapa Interactivo:** Pines dinamicos usando Google Maps API basados en la tabla Ubicacion.

**Listado de Mascotas:** Grilla con fotos, nombres y etiquetas.

**Filtros:** Especie, Tamaño, Edad y Sexo.

**Ficha de Detalle:** Galería de imágenes (MediaMascota) y descripción técnica.

### Módulo de Vinculación (Test y Solicitudes)
**Test de Compatibilidad:** Formulario dinámico que procesa las respuestas del usuario y sugiere
mascotas afines segun estilo de vida y entorno del adoptante.

**Solicitud de Adopcion:** Formulario dinamico que vincula al Adoptante logueado con la Mascota y
envía la petición al Refugio.

### NUEVO: Modulo de Seguimiento Post-Adopción
**Calendario Sanitario:** Cronograma de vacunas y desparasitaciones generado automáticamente.

**Recordatorios:** Notificaciones (Email/SMS) sobre fechas sanitarias y castración.

**Encuestas de Adaptación:** Formularios post-adopción para evaluar la alimentación, sueño y
conducta del animal.

**Repositorio Documental:** Subida de fotos de la nueva vida de la mascota, boletas veterinarias y
certificados.

**Alertas al Refugio:** Disparador automatico si hay reportes negativos en las encuestas o falta de
vacunas.

### Panel de Gestión (Refugios)
**CRUD de Mascotas:** Administración total de los animales (Alta/Baja/Modificación).

**Gestion de Solicitudes:** Aprobar o rechazar adopciones.

**Dashboard de Monitoreo:** Vista para supervisar las encuestas y fotos enviadas por los
adoptantes.

## Intrucciones de ejecución
### composer

```bash
composer install
```
*Si ya instalaste el composer.json y se agregaron dependencias:*
```bash
composer update
```

### Migration

```bash
phinx migrate -e development
phinx seed:run
phinx rollback -e development -t 0
```
*Si no tenes phinx instalado en tu compu:*
```bash
php vendor/bin/phinx migration:run -e development
```

### Variables de Entorno
Configurar las variables de entorno necesarias
```bash
cp .env.example.env
```

### Comando para levantar el server
```bash
cd Entrega3
php -S localhost:3000 -t public
```
### Comando para probar el cron_notificaciones
```bash
cd Entrega3
php bin/cron_recordatorios.php
```
## Deploy
![Pawmap](https://tpintegrador-paw2026-1.onrender.com/)