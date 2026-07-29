# PawMap

**PawMap** es una aplicación web orientada a facilitar la **adopción de mascotas** que conecta adoptantes con refugios a través de un **mapa interactivo**. La plataforma permite visualizar mascotas disponibles según ubicación y acceder a información detallada sobre cada animal para favorecer adopciones responsables.

## Objetivo

El objetivo de PawMap es centralizar la información de animales en adopción y simplificar el proceso de búsqueda y contacto con refugios. La aplicación permite explorar mascotas disponibles mediante un mapa, aplicar filtros según características del animal y visualizar perfiles con contenido multimedia.

## Funcionalidades principales

### Modulo de Usuario y Acceso
**Gestion de Sesiones:** Login y registro diferenciado para Adoptantes y Refugios.  

**Perfil de Usuario:**
- *Adoptante:* Edición de datos personales y carga de foto de perfil, "Favoritos", estado de solicitudes, historial de adopciones y seguimiento sanitario. 
- *Refugio:* Datos institucionales, contacto y ubicación.

*(Nuevo)* **Sistema de Rate Limit:** Bloqueo temporal de ingresos al sistema tras superar un límite de intentos fallidos (ej: 5 intentos) para evitar accesos no autorizados.

*(Nuevo)* **Sistema de Seguridad CSRF:** Generación e inyección de tokens aleatorios en campos ocultos de los formularios para evitar la falsificación de peticiones.

### Modulo de Navegacion e Información
**Buscador General:** Barra de búsqueda global e integrada en la plataforma que permite realizar consultas en todo el sitio, buscando coincidencias de forma simultánea tanto en los perfiles de las mascotas como en los datos de los refugios (por nombres, palabras clave o características).

**Página "¿Cómo Adoptar?":** con contenido informativo sobre los pasos y responsabilidades de la
adopción.

**Seccion de Donaciones:** Interfaz que lista los métodos de donación de cada refugio recuperados de la base de datos (ej. CBU, Alias bancario o links externos como Mercado Pago). 

*(Nuevo)* **Sistema de Reseñas:** Sección en el inicio para que los adoptantes puedan dejar comentarios y reseñas sobre su experiencia. 

### Modulo de Refugios
**Listado dinámico de todos los refugios registrados.**

**Filtros de Refugios:** Buscador localidad ( Mercedes, Lujan, etc.) para que el usuario encuentre los mas cercanos.

**Mapa Interactivo de Refugios:** Mapa dinámico integrado con la API de OpenStreetMap, consumiendo las coordenadas almacenadas en la base de datos para visualizar la ubicación geográfica.
  - *Pines interactivos* en el frontend que despliegan tarjetas emergentes con datos de contacto rápido y enlace directo al perfil del refugio.
  - *Sección de filtros y carrusel* de mascotas.
  - *Sección de refugios más cercanos* activando geolocalización.

### Módulo de Mascotas
**Listado de Mascotas:** Grilla con fotos, nombres y etiquetas.

**Filtros:** Especie, Tamaño, Edad y Sexo.

**Ficha de Detalle:** Galería de imágenes (MediaMascota), animación svg y descripción técnica.

*(Nuevo)*  **Cola de Espera Inteligente:** Botón "Avisame" en filtros de búsqueda vacíos que inscribe al usuario en una cola para notificarle automáticamente cuando ingrese una mascota con esas características.

*(Nuevo)*  **Algoritmo de "Mascotas Invisibles":** Sistema que calcula un "puntaje de invisibilidad" (cruzando visitas y días en adopción) para destacar automáticamente en el inicio a los animales más olvidados.

*(Nuevo)*  **Generador de Medallitas / Chapitas:** Botón para imprimir una imagen QR que enlaza directamente al perfil público de la mascota en PawMap.

*(Nuevo)*  **Botón de Compartir:** Accesos directos en el perfil de la mascota para enviar su ficha técnica y enlace a WhatsApp, Instagram y TikTok.

### Módulo de Vinculación (Test y Solicitudes)
**Test de Compatibilidad:** Formulario dinámico que procesa las respuestas del usuario y sugiere mascotas afines segun estilo de vida y entorno del adoptante.

**Solicitud de Adopcion:** Formulario dinámico que vincula al Adoptante logueado con la Mascota y envía la petición al Refugio, incluyendo la validación obligatoria y el registro de aceptación de un Contrato de Adopción (con fecha y hora) por parte del adoptante.

**Sistema de Mensajería Interna (Chat Refugio-Adoptante):** Canal de comunicación directo e integrado. Una vez que el refugio aprueba una solicitud, se habilita un chat privado para coordinar la entrega o despejar dudas.

**Formulario de Contacto:** Interfaz dedicada para que los usuarios visitantes puedan enviar consultas generales, reportes o propuestas de colaboración directamente a los administradores de la plataforma PawMap.

### Módulo de notificaciones
*(Nuevo)*  **Notificaciones (Campanita):** Ícono en el menú superior para emitir alertas automáticas e instantáneas sobre los distintos procesos del sistema. 
Eventos Notificados: 
  - El sistema avisa sobre la aprobación de solicitudes.
  - El ingreso de nuevas solicitudes de adopción al refugio. 
  - Recepción de mensajes en el chat.
  - Carga y recordatorios de registros sanitarios (vía Cron)
  - Alertas cuando una "mascota favorita" es adoptada por otra persona.

### Modulo de Seguimiento Post-Adopción
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

**Gestion de Solicitudes:** Bandeja de entrada para visualizar solicitudes de adopción.
  - Acciones de "Aprobar" o "Rechazar" solicitudes con actualización de estado en tiempo real en la base de datos.

**Dashboard de Monitoreo:** Vista para supervisar las encuestas y fotos enviadas por los
adoptantes.

*(Nuevo)*  **Importador Masivo de Mascotas:** Herramienta para que los refugios suban archivos .csv o Excel, validando e insertando múltiples registros en la base de datos de una sola vez.

*(Nuevo)*  **Panel de Estadísticas Gráficas (Dashboard):** Pestaña en el perfil del refugio con gráficos sobre el rendimiento de sus publicaciones y el flujo de adopciones.

### Módulo de Arquitectura SEO y Visibilidad (Técnico) 
**Metaetiquetas:** Descripciones meta dinámicas en las páginas del sitio adaptadas al contenido específico de cada sección.

**Datos Estructurados (Schema.org):** Implementación de bloques JSON-LD generados dinámicamente desde PHP para clasificar entidades como Refugio (Animal Shelter) y Organización (Organization).

**Sitemap XML Dinámico:** Generado vía PHP para indexar páginas estáticas, fichas de mascotas y perfiles de refugios desde la base de datos.

**Archivo Robots.txt:** Configurado para permitir la indexación de contenido público (mascotas, refugios) y bloquear el rastreo de secciones privadas (perfiles de usuario, paneles de gestión y formularios internos), indicando además la ruta del sitemap.

## Intrucciones de ejecución

### Composer

```bash
composer install
```

_Si ya instalaste el composer.json y se agregaron dependencias:_

```bash
composer update
```

### Migration

```bash
phinx migrate -e development
phinx seed:run
phinx rollback -e development -t 0
```

_Si no tenes phinx instalado en tu compu:_

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

### Comando para probar las notificaciones
```bash
cd Entrega3
php bin/websocket-server.php
```

## Deploy


### Ngrok
La aplicación se encuentra disponible de forma temporal a través de un túnel de ngrok.  
**Solicitud de acceso**: Por favor, contactar para obtener el enlace activo de la sesión actual.

## Autores

Abril Babino  
Naiara Collazo  
Tobias Avila
