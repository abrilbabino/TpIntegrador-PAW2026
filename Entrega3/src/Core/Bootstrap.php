<?php

require __DIR__ . '/../../vendor/autoload.php';

use Paw\Core\Config;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Dotenv\Dotenv;

use Paw\Core\Router;
use Paw\Core\Request;

use Paw\Core\Database\ConnectionBuilder;

use Paw\Core\ControllerFactory;

date_default_timezone_set('America/Argentina/Buenos_Aires');

$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../../');
$dotenv->safeLoad();
$config = new Config;

// Iniciar sesión global
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$request = new Request;

$log = new Logger('pawmap-app');
$handler = new StreamHandler($config->get("LOG_PATH"));
$handler->setLevel($config->get("LOG_LEVEL", "DEBUG"));
$log->pushHandler($handler);

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$connectionBuilder = new ConnectionBuilder;
$connectionBuilder->setLogger($log);
$connection = $connectionBuilder->make($config);

$controllerFactory = new ControllerFactory($request, $log, $connection);

$router = new Router;
$router->setLogger($log);
$router->setControllerFactory($controllerFactory);

// Páginas estáticas
$router->get('/', 'PageController@index');

$router->get('/como-adoptar', 'PageController@comoAdoptar');
// $router->get('/donar', 'PageController@donar');
$router->get('/mapa', 'PageController@mapa');

// Mascotas / adopción
$router->get('/adoptar', 'MascotaController@adoptar');
$router->get('/api/mascotas', 'MascotaController@apiMascotas');
$router->get('/mascota', 'MascotaController@detalle');
$router->get('/mascota/libreta', 'MascotaController@libreta');
$router->get('/api/mascota/libreta', 'MascotaController@apiLibreta');
$router->post('/mascota/registro/guardar', 'MascotaController@guardarRegistro');
$router->post('/mascota/registro/completar', 'MascotaController@completarRegistro');
$router->get('/buscar', 'PageController@buscar');
$router->get('/mascota/editar', 'MascotaController@editarForm');
$router->post('/mascota/editar/guardar', 'MascotaController@editarGuardar');
$router->post( '/perfil/eliminar', 'MascotaController@eliminar');
$router->post('/mascota/subir-archivo', 'MascotaController@subirArchivoMascota');
$router->post('/mascota/eliminar-svg', 'MascotaController@eliminarSvg');
$router->post('/mascota/eliminar-foto-principal', 'MascotaController@eliminarFotoPrincipal');
$router->post('/mascota/eliminar-foto', 'MascotaController@eliminarFoto');

// Formulario de adopción
$router->get('/formulario-adopcion', 'AdopcionController@formulario');
$router->post('/formulario-adopcion/enviar', 'AdopcionController@enviar');

// Test de compatibilidad
$router->get('/test-de-compatibilidad', 'TestController@test');
$router->post('/test-de-compatibilidad/resultado', 'TestController@resultado');

// Refugios
$router->get('/refugios', 'RefugioController@lista');
$router->get('/api/refugios', 'RefugioController@apiRefugios');
$router->get('/refugio', 'RefugioController@detalle');
$router->get('/refugio/perfil', 'RefugioController@detalle');

// Autenticación
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->post('/register', 'AuthController@register');

// Perfil de usuario
$router->get('/perfil', 'UserController@perfil');
$router->post('/perfil/guardar', 'UserController@guardar');
$router->post('/perfil/refugio/guardar', 'UserController@guardarRefugio');
$router->post('/perfil/refugio/ubicacion', 'UserController@guardarUbicacion');
$router->post('/perfil/mascota/publicar', 'UserController@guardarMascota');
$router->post('/perfil/mascota/importar', 'UserController@importarMascotasCsv');
$router->get('/seguimiento', 'SeguimientoController@index');
$router->post('/seguimiento/subir-archivo', 'SeguimientoController@subirArchivo');
$router->post('/encuesta/guardar', 'SeguimientoController@guardarEncuesta');
$router->post('/resena/guardar', 'ResenaController@guardar');

// Favoritos
$router->post('/api/favorito/toggle', 'FavoritoController@toggle');

// Solicitudes API
$router->post('/api/solicitud/actualizar', 'AdopcionController@actualizar');

// Notificaciones API
$router->get('/api/notificaciones', 'NotificacionController@getRecientes');
$router->post('/api/notificaciones/leer', 'NotificacionController@marcarLeidas');

// Chat
$router->get('/chat', 'ChatController@verChat');
$router->post('/chat/enviar', 'ChatController@enviarMensaje');
$router->get('/api/chat/mensajes', 'ChatController@apiMensajes');
$router->get('/api/chat/list', 'ChatController@apiListarChatsActivos');
// Errores
$router->get('not_found', 'ErrorController@notFound');
$router->get('internal_error', 'ErrorController@internalError');
$router->get('invalid_format', 'ErrorController@invalidFormat');

// Contacto
$router->get('/contacto', 'PageController@contacto');
$router->post('/contacto/enviar', 'PageController@contactoEnviar');

// Donaciones
$router->get('/donar', 'DonacionController@index');
$router->post('/procesar-donacion', 'DonacionController@enviar');
$router->post('/enviar-comprobante', 'DonacionController@enviarComprobante');

// SEO
$router->get('/sitemap.xml', 'PageController@sitemap');

// Generar QR
$router->get('/generar-qr', 'QrController@index');
$router->get('/generar-qr/imagen', 'QrController@generarImagen');
