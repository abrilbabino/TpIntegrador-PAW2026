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
$router->get('/iniciar-sesion', 'PageController@iniciarSesion');
$router->get('/como-adoptar', 'PageController@comoAdoptar');
$router->get('/donar', 'PageController@donar');
$router->get('/mapa', 'PageController@mapa');
$router->get('/contacto', 'PageController@contacto');

// Mascotas / adopción
$router->get('/adoptar', 'MascotaController@adoptar');
$router->get('/mascota', 'MascotaController@detalle');
$router->get('/buscar', 'MascotaController@buscar');

// Formulario de adopción
$router->get('/formulario-adopcion', 'AdopcionController@formulario');
$router->post('/formulario-adopcion/enviar', 'AdopcionController@enviar');
$router->get('/adopcion-exitosa', 'PageController@adopcionExitosa');

// Test de compatibilidad
$router->get('/test-de-compatibilidad', 'TestController@test');
$router->post('/test-de-compatibilidad/resultado', 'TestController@resultado');

// Refugios
$router->get('/refugios', 'RefugioController@lista');
$router->get('/refugio', 'RefugioController@detalle');

// Autenticación
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->post('/register', 'AuthController@register');

// Perfil de usuario
$router->get('/perfil', 'UserController@perfil');

// Favoritos
$router->post('/favorito', 'FavoritoController@guardar');
$router->post('/favorito/eliminar', 'FavoritoController@eliminar');

// Errores
$router->get('not_found', 'ErrorController@notFound');
$router->get('internal_error', 'ErrorController@internalError');
$router->get('invalid_format', 'ErrorController@invalidFormat');