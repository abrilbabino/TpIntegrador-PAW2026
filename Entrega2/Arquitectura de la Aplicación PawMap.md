# PawMap - Documento de Arquitectura

## 1. Introducción
PawMap es una plataforma web diseñada para facilitar la adopción de perros y gatos mediante herramientas interactivas, como mapas de ubicación y tests de compatibilidad. La arquitectura del sistema está diseñada para ser flexible, escalable y fácil de mantener, siguiendo los principios de separación de intereses.

---

## 2. Actores y contexto
El sistema interactúa con tres actores principales y un sistema externos:

* **Usuario adoptante:** Explora mascotas, realiza el test de compatibilidad, envía solicitudes de adopción.
* **Refugio (administrador):** Gestiona perfiles de animales y responde solicitudes.
* **Visitante anónimo:** Accede al mapa y perfil de mascotas sin registrarse.
* **Google Maps API (externo):** Provee el mapa interactivo y geolocalización.

---

## 3. Metáfora y Modelo Conceptual
La metáfora central del sistema es la de un mapa de refugios: cada refugio es un 'pin' en el mapa que contiene mascotas. Un usuario navega el mapa, filtra, visita el perfil de una mascota y genera una solicitud de adopción. Esta metáfora guía la nomenclatura del sistema:

| Concepto | Definición en el sistema |
|---|---|
| **Refugio** | Institución registrada que publica mascotas. Tiene ubicación geográfica (lat/lng) para el mapa. |
| **Mascota** | Animal con perfil detallado: especie, tamaño, edad, temperamento, fotografías. |
| **Solicitud de adopción** | Petición formal de un usuario hacia un refugio por una mascota específica. |
| **Test de compatibilidad** | Cuestionario que cruza el estilo de vida del usuario con el perfil de mascotas para sugerir candidatos. |
| **Sesión** | Estado de autenticación de un usuario o refugio durante su interacción con el sistema. |

### 3.1. Entendimiento del Contexto: ¿Qué se conoce como Cliente?
* **El Cliente Arquitectónico (Nivel de Infraestructura):** En nuestro modelo el cliente es el navegador web (browser) que se ejecuta en el dispositivo del usuario final. Su única responsabilidad estructural es renderizar el HTML/CSS, ejecutar la lógica de interfaz en JavaScript (como la integración con la API de Google Maps) y enviar peticiones HTTP al controlador backend.
 
* **El Cliente de Negocio (Nivel de Dominio y Usuarios):** Desde la perspectiva funcional de la plataforma, consideramos "clientes" a todos los actores que interactúan con el sistema, independientemente de su rol.
 
* **Visitantes (No autenticados):** Clientes potenciales que exploran el mapa y el catálogo de forma pasiva.
 
* **Adoptantes (Autenticados):** Usuarios finales de la plataforma que consumen los servicios principales (realizar tests de compatibilidad, guardar favoritos y enviar solicitudes).
 
* **Refugios (Autenticados institucionales):** Aunque actúan como proveedores de información publicando mascotas, también son clientes del sistema, ya que utilizan el panel de administración que PawMap les provee como servicio para gestionar sus adopciones.

---

## 4. Desarrollo (Development)

### 4.1. Patrones de Arquitectura y Diseño
A continuación se describen los patrones aplicados en PawMap, su motivación y dónde se aplican concretamente.

#### 4.1.1 Patrón de Arquitectura: N-Tier (3 Niveles)
Decidimos separar la arquitectura estructural del sistema en 3 capas lógicas y físicas independientes (Presentación, Aplicación/Lógica y Datos) para reducir el acoplamiento y facilitar el mantenimiento a largo plazo.

* **Nivel de Presentación (Frontend):** Responsable de la interfaz y la interacción con el usuario (HTML, CSS, JS).
  
* **Nivel de Aplicación (Backend):** Contiene la lógica de negocio y las reglas de ruteo (Servidor Java Web). A su vez este nivel aplica Arquitectura Layer MVC.
  
* **Nivel de Datos (Persistencia):** Responsable del almacenamiento y la integridad de la información (PostgreSQL).
  
* **Beneficio central:** Esta separación asegura que un cambio estructural en el modelo de la base de datos no afecte el código del cliente web, y a su vez, un rediseño visual en el frontend no obligue a recompilar la lógica del servidor.
  
* **Integración de Servicios Externos:** Si bien el sistema base se rige por un patrón de 3 capas, PawMap requiere la integración de servicios de terceros para funcionalidades específicas. La API de Google Maps no actúa como una cuarta capa del sistema, sino como un Servicio Externo consumido asíncronamente por la Capa de Presentación.

![Diagrama de Arquitectura](<Arquitectura de aplicación - PawMap.drawio.png>)

#### 4.1.2 Patrón de Arquitectura: MVC (Modelo-Vista-Controlador)
Decidimos organizar el código del servidor (Tier 2) separando la recepción de requests, la lógica de negocio y la generación de respuestas mediante la aplicación del patrón MVC.

* **Controlador:** Recibe el request HTTP, identifica la operación y delega al servicio correspondiente.
  
* **Modelo:** Contiene las clases del dominio y los servicios de negocio.
  
* **Vista:** Serializa los objetos del Modelo a JSON y construye la respuesta HTTP.
  
* **Beneficios:** Cada capa (Modelo, Vista, Controlador) tiene un rol definido, evitando código acoplado y facilitando el mantenimiento de una capa sin afectar a las demás, también facilita el enrutamiento centralizado.

#### 4.1.3 Patrón de Diseño: DAO (Data Access Object)
Decidimos encapsular toda la lógica de acceso a la base de datos en clases dedicadas, ocultando los detalles de SQL al resto del sistema. DAOs definidos. Cada DAO expone métodos.

* **Táctica de seguridad:** Centraliza los puntos de acceso a la BD, facilita auditoría y uso de PreparedStatements.
  
* **Táctica de mantenibilidad:** Si se cambia una consulta SQL, solo se modifica el DAO correspondiente.

#### 4.1.4 Patrón de Arquitectura: API REST (Stateless)
Decidimos diseñar la comunicación entre Tier 1 y Tier 2 como una API sin estado, donde cada request contiene toda la información necesaria para procesarse.

* **Estado:** El servidor no guarda estado entre requests: la sesión se mantiene mediante una cookie que el navegador envía automáticamente con cada request.
  
* **Formato:** Respuestas siempre en JSON: el cliente es responsable de renderizar la información.
  
* **Rutas:** URLs representan recursos: GET/mascotas, POST /solicitudes.
  
* **Beneficio:** Desacopla el cliente del servidor. En el futuro, una app móvil podría consumir la misma API.
  
---

## 5. Despliegue y entornos
Decidimos que el diseño de la aplicación PawMap va a estar orientado a un esquema de despliegue ágil basado en infraestructura propia, utilizando redes de túneles seguros para la etapa de pruebas y la exposición final ante el cliente. El proceso se estructura de la siguiente manera:

* **5.1 Control de Versiones:** Todo el código fuente (Frontend, Backend Java Web y scripts de base de datos) se mantiene centralizado en un repositorio público de GitHub, permitiendo el trabajo colaborativo asíncrono y el control de ramas durante el desarrollo.
  
* **5.2 Infraestructura de Ejecución (Host Local):** La arquitectura de PawMap se ejecutará on-premise sobre el equipo de uno de los desarrolladores durante las sesiones de prueba y la entrega final.
  
    * **5.2.1 Capa de Datos:** El motor relacional PostgreSQL se ejecuta localmente.
      
    * **5.2.2 Capa de Aplicación:** El servidor web Java compila y levanta la aplicación en un puerto local designado (por ejemplo, localhost:8080).
      
* **5.3 Exposición a Internet (Túnel Inverso con Ngrok):** Para cumplir con el requisito de accesibilidad remota sin incurrir en costos de alojamiento (hosting), se implementa Ngrok como servicio de proxy inverso. Ngrok intercepta el tráfico externo y lo redirige de forma segura hacia el servidor local de Java. Al ejecutar el cliente de Ngrok, este genera dinámicamente una URL pública temporal con protocolo HTTPS. A través de este enlace, cualquier usuario externo puede acceder y utilizar la plataforma PawMap desde sus propios dispositivos móviles o de escritorio interactuando en tiempo real con la base de datos local.
  
* **5.4 Mantenimiento y Control del Entorno:** Este enfoque garantiza un control absoluto sobre los recursos del sistema durante la presentación. Al no depender de cuotas de memoria de servidores gratuitos en la nube, se mitiga el riesgo de caídas por falta de recursos (Out of Memory) al cargar imágenes de las mascotas o al procesar múltiples consultas espaciales en el mapa interactivo.

---

## 6. Operación
Para que el sistema sea fácil de operar, depurar y comprender por cualquier miembro del equipo de desarrollo, la arquitectura se estructuró buscando que el propósito de PawMap sea evidente a simple vista, aplicando el principio de Arquitectura que revela su intención (Screaming Architecture).

* **6.1. Visibilidad de Casos de Uso:** La estructura de paquetes y clases en el servidor Java se organiza puramente por la lógica de negocio. Las rutas del controlador y la lógica del modelo están organizadas de tal forma que las operaciones clave son elementos de primera clase en el código. Al examinar el proyecto, el equipo identifica rápidamente flujos funcionales críticos.
  
* **6.2. Trazabilidad mediante Front Controller:** Al centralizar todas las peticiones HTTP del cliente web en un Front Controller, la operación diaria y el debugging se simplifican enormemente. Este único punto de entrada permite registrar en la consola del servidor cada petición entrante. Esto facilita monitorear qué endpoints reciben más tráfico y capturar excepciones globales de forma controlada antes de que afecten la experiencia del usuario.

---

## 7. Procesos, Hilos y Asignación de Recursos

* **7.1. Gestión de Procesos e Hilos (Threads):** El Servidor Web Java opera bajo un modelo Thread-per-Request (un hilo por petición). Cuando múltiples usuarios acceden al mapa simultáneamente o un Refugio sube imágenes, el servidor asigna un hilo independiente a cada conexión. El Front Controller asegura que estos hilos se gestionen eficientemente mediante un Thread Pool, evitando la saturación del servidor.
  
* **7.2. Asignación de Recursos:** Pool de Conexiones: Los recursos de base de datos son costosos. Se implementará un pool de conexiones JDBC para que los hilos reutilicen conexiones a PostgreSQL en lugar de abrir y cerrar una por cada solicitud (ej. al visualizar repetidamente las mascotas en el mapa).
  
* **7.3. Gestión de Memoria y Archivos:** La asignación de recursos limitará el tamaño de las cargas, evitando el agotamiento de la memoria (Out of Memory) en el servidor.
  
* **7.4. Ciclo de vida de un request:** El cliente envía HTTP request, el servidor web asigna un hilo, el Controlador valida la sesión y delega el procesamiento al Servicio de negocio, el DAO ejecuta la query JDBC, la respuesta JSON se serializa y por último el hilo envía HTTP response y queda disponible para otra solicitud.
  
* **7.5. Proceso del cliente (navegador):** Se ejecuta JavaScript de forma single-threaded con event loop. Las llamadas a la API del servidor son asíncronas (fetch/AJAX) para no bloquear la interfaz.
  
---

## 8. Selección de Tecnología y Justificación

* **8.1. Backend (Lógica y API):** Decidimos utilizar Java ya que proporciona un tipado estricto y un entorno fuertemente orientado a objetos, ideal para plasmar el modelo de dominio complejo. Además, su madurez en el manejo de hilos y ecosistema web garantiza estabilidad. También lo elegimos por su compatibilidad directa con JDBC para el acceso a la base de datos.
  
* **8.2. Base de Datos:** Decidimos utilizar PostgreSQL ya que es un motor relacional ACID robusto. La elección es crítica por dos motivos: 1) Tiene un soporte nativo espacial, ideal para guardar los datos de latitud/longitud de la clase Ubicación y realizar consultas eficientes por radio de distancia. 2) Se integra correctamente con JDBC. 3) Es de Software libre (No requiere licencias).
  
* **8.3. Frontend:** Utilizamos HTML5, CSS3, JavaScript ya que es solicitado en la consigna del integrador, además, es el estándar multiplataforma. JavaScript permite consumir la API REST en tiempo real y es mandatorio para integrar y manipular dinámicamente la API de Google Maps de forma asincrona.

---

## 9. Atributos de calidad
La arquitectura y las decisiones tecnológicas adoptadas aseguran los siguientes atributos de calidad para PawMap:

* **9.1. Mantenibilidad:** * **Separación en capas:** Cada capa puede modificarse sin afectar las otras.
  
    * **Patrón DAO:** Toda la lógica de acceso a datos está encapsulada en clases DAO. Cambiar la base de datos o una consulta no afecta la lógica de negocio.
      
* **9.2. Escalabilidad:** El backend (Java) y la base de datos (PostgreSQL) están desacoplados. Si la carga de Adoptantes navegando el mapa crece masivamente, el servidor de aplicaciones se puede escalar horizontalmente sin afectar la persistencia.
  
* **9.3. Rendimiento (Performance):** Optimizado mediante el uso de un pool de conexiones a la base de datos y la carga asincrona en el Frontend. Solo se transmiten datos JSON ligeros, mientras que el cliente absorbe el costo computacional de renderizar el mapa.
  
* **9.4. Seguridad:**
 
    * **Autenticación:** Centralizada en el Front Controller. Cualquier acceso a operaciones sensibles pasará por un filtro de validación de sesión (Usuario.iniciarSesion()) antes de tocar los controladores específicos o el modelo de objetos.
      
    * **Autorización por roles:** El sistema distingue tres roles visitante, usuario adoptante y refugio, cada endpoint verifica el rol antes de ejecutar la lógica.
      
    * **Queries parametrizadas:** (JDBC PreparedStatement) Para prevenir inyecciones SQL en todos los accesos a la base de datos.
      
    * **Uso de HTTPS:** Todas las comunicaciones entre cliente y servidor están cifradas con TLS.
      
    * **Validación en servidor:** Los datos enviados desde el cliente son validados también en el Tier 2, no solo en el formulario del navegador.
      
* **9.5. Usabilidad:**
  
    * **Diseño responsive:** La interfaz se adapta a pantallas de cualquier tamaño usando CSS Flexbox/Grid.
      
    * **Cross-browser:** La aplicación funciona en Chrome, Firefox, Safari y Edge sin modificaciones.
      
* **9.6. Disponibilidad:**
  
    * **Degradación elegante:** Si la API de Google Maps no responde, la aplicación muestra los refugios en una lista alternativa sin el mapa, sin interrumpir el flujo de adopción.
      
    * **Manejo de excepciones:** El Tier 2 captura errores de JDBC y devuelve respuestas de error descriptivas al cliente en lugar de fallar silenciosamente.
      
    * **Pool de conexiones JDBC:** Evita que solicitudes concurrentes agoten las conexiones disponibles a la base de datos.

---

## 10. Política vs. Detalles
Siguiendo la premisa de mantener el software flexible, PawMap separa sus reglas de negocio de los detalles de implementación:

### Política (Core)
* **Reglas de SolicitudDeAdopcion:** un Adoptante no puede enviar una solicitud sobre una Mascota cuyo estadoAdopcion sea ADOPTADO. Solo se permiten solicitudes sobre mascotas con estado DISPONIBLE.

* **Transición de estados de una Mascota:** al crearse es DISPONIBLE, y pasa a ADOPTADO cuando el refugio acepta la solicitud de adopción. Estas transiciones son irreversibles salvo cancelación explícita.

* **Transición de estados de una SolicitudDeAdopcion:** al crearse está PENDIENTE, puede pasar a APROBADA o RECHAZADA por el Refugio, o a CANCELADA por el Adoptante mediante cancelarSolicitud().

* **Lógica de autorización por roles:** un Refugio solo puede editar, eliminar y cambiar el estado de sus propias mascotas. Un Adoptante solo puede cancelar sus propias solicitudes y gestionar sus propios favoritos. Un visitante anónimo solo puede consultar mascotas, refugios y donar, sin poder enviar solicitudes ni guardar favoritos.

* **Regla de unicidad de solicitud:** un Adoptante no puede enviar más de una solicitud activa sobre la misma Mascota simultáneamente.

* **Validación al registrar una Mascota:** nombre, especie, edad, tamaño, vacunas, castrado, descripción y temperamento son campos requeridos. Una mascota no puede publicarse sin esta información completa.

* **Validación al registrar un Adoptante:** nombre, apellido, DNI, fecha de nacimiento, usuario, email y contraseña son obligatorios. No pueden existir dos cuentas con el mismo usuario.

* **Validación al registrar un Refugio:** nombreInstitucion, CUIT, email, CBU/CVU, alias y al menos una Ubicacion son obligatorios. No pueden existir dos refugios con el mismo CUIT.

* **Relación MediaMascota:** La regla de que una MediaMascota sólo puede asociarse a una Mascota existente y registrada. Al eliminar una Mascota, sus medios asociados también se eliminan.

* **Relación Favorito:** Un Favorito sólo puede existir si tanto el Adoptante como la Mascota referenciada existen en el sistema. Al eliminar una Mascota, sus registros de favoritos se eliminan en cascada.

* **Regla de TestDeCompatibilidad:** Un TestDeCompatibilidad pertenece a un único Adoptante y sus resultados sólo son visibles para ese adoptante. El test puede rehacerse, pero cada instancia genera un nuevo resultado independiente.

* **Autenticación centralizada:** Cualquier operación que no sea exploración pública requiere sesión activa válida antes de llegar a los controladores específicos.

### Detalles (Externos)
* **Google Maps API:** Es el mecanismo visual para renderizar las coordenadas de Ubicacion. La clase Ubicacion guarda únicamente latitud y longitud como datos. El sistema no sabe ni le importa cómo se dibuja el mapa.
  
* **PostgreSQL:** Es el detalle de persistencia. Las clases Adoptante, Refugio, Mascota, SolicitudDeAdopcion, Favorito, MediaMascota, Ubicacion y TestDeCompatibilidad existen como objetos Java en el Tier 2. Los DAOs son los únicos que conocen cómo traducir esos objetos a tablas SQL y ejecutar queries mediante JDBC.
  
* **JDBC y PreparedStatements:** Son el mecanismo técnico de comunicación entre el backend Java y PostgreSQL. El uso de PreparedStatements es un detalle de implementación que previene inyecciones SQL, pero es invisible para el modelo de dominio.
  
* **Pool de conexiones JDBC:** Es un detalle de infraestructura que optimiza el uso de recursos al reutilizar conexiones a la base de datos entre requests concurrentes.
  
* **HTML5, CSS3 y JavaScript (Tier 1):** Son el mecanismo de entrega de la interfaz al usuario. El backend responde siempre con JSON estructurado, sin importar qué tecnología de frontend lo consuma.
* **Cookies de sesión HTTP-only:** Son el mecanismo técnico mediante el cual el navegador mantiene la sesión activa entre requests. Es un detalle del protocolo HTTP: la política de autenticación (quién puede acceder a qué) vive en el backend.
  
* **Ngrok:** Es un detalle de infraestructura exclusivo de la etapa de pruebas y presentación. Actúa como proxy inverso para exponer el servidor local a Internet.
  
* **GitHub:** Es el detalle de infraestructura para el control de versiones y la colaboración del equipo. No forma parte de la arquitectura en ejecución del sistema.
