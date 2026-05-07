<?php

namespace Paw\Core;

use Paw\Core\Exceptions\InvalidValueFormatException;

class Request
{
    public function uri()
    {
        $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        if (str_contains($path, 'index.php')) {
            $path = '/';
        }
        return $path;
    }

    public function method()
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    public function route()
    {
        return [$this->uri(), $this->method()];
    }

    public function get($key)
    {
        return $_POST[$key] ?? $_GET[$key] ?? null;
    }

    public function getAll()
    {
        return $_GET;
    }

    public function paginaActual()
    {
        $pagina = $_GET['pagina'] ?? 1;
        if (!is_numeric($pagina) || $pagina < 1) {
            throw new InvalidValueFormatException("El parámetro 'pagina' debe ser un número entero válido.");
        }
        return $pagina;
    }
}