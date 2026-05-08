<?php

namespace Paw\Core;

use Exception;
use Paw\Core\Exceptions\InvalidValueFormatException;
use Paw\Core\Exceptions\RouteNotFoundException;
use Paw\Core\Exceptions\MascotaNotFoundException;
use Paw\Core\Request;
use Paw\Core\Traits\Loggable;

class Router
{
    use Loggable;

    private ControllerFactory $factory;

    public array $routes = [
        "GET"  => [],
        "POST" => [],
    ];

    public string $notFound        = 'not_found';
    public string $internalError   = 'internal_error';
    public string $invalidFormat   = 'invalid_format';
    public string $mascotaNotFound = 'mascota_not_found';

    public function __construct()
    {
        $this->get($this->notFound,        'ErrorController@notFound');
        $this->get($this->internalError,   'ErrorController@internalError');
        $this->get($this->invalidFormat,   'ErrorController@invalidFormat');
        $this->get($this->mascotaNotFound, 'ErrorController@mascotaNotFound');
    }

    public function setControllerFactory(ControllerFactory $factory)
    {
        $this->factory = $factory;
    }

    public function loadRoutes($path, $action, $method = 'GET')
    {
        $this->routes[$method][$path] = $action;
    }

    public function get($path, $action)
    {
        $this->loadRoutes($path, $action, 'GET');
    }

    public function post($path, $action)
    {
        $this->loadRoutes($path, $action, 'POST');
    }

    public function exists($path, $method)
    {
        return array_key_exists($path, $this->routes[$method]);
    }

    public function getController($path, $http_method)
    {
        if (!$this->exists($path, $http_method)) {
            throw new RouteNotFoundException("No existe ruta para: $path [$http_method]");
        }
        return explode('@', $this->routes[$http_method][$path]);
    }

    public function call($controller, $method, $params = [])
    {
        $objController = $this->factory->create($controller);
        $objController->$method(...$params);
    }

    public function direct(Request $request)
    {
        $path = $request->uri();
        $http_method = $request->method();

        try {
            [$controller, $method] = $this->getController($path, $http_method);
            
            $this->logger->info("Status Code: 200", ["path" => $path, "method" => $http_method]);
            $this->call($controller, $method);

        } catch (RouteNotFoundException $e) {
            $this->logger->debug("Status Code: 404", ["path" => $path, "exception" => $e->getMessage()]);
            [$controller, $method] = $this->getController($this->notFound, 'GET');
            $this->call($controller, $method);

        } catch (InvalidValueFormatException $e) {
            $this->logger->debug("Status Code: 400", ["path" => $path, "exception" => $e->getMessage()]);
            [$controller, $method] = $this->getController($this->invalidFormat, 'GET');
            $this->call($controller, $method, [$e]);

        } catch (MascotaNotFoundException $e) {
            $this->logger->debug("Status Code: 404", ["path" => $path, "exception" => $e->getMessage()]);
            [$controller, $method] = $this->getController($this->mascotaNotFound, 'GET');
            $this->call($controller, $method, [$e]);

        } catch (\Throwable $e) {
            // Captura cualquier otro error, incluyendo PDOException (SQL) o TypeErrors fatales
            $this->logger->error("Status Code: 500 - Internal Server Error", ["path" => $path, "exception" => $e->getMessage()]);
            [$controller, $method] = $this->getController($this->internalError, 'GET');
            $this->call($controller, $method);
        }
    }
}