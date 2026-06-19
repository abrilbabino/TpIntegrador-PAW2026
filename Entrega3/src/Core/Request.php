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

    public function server($key)
    {
        return $_SERVER[$key] ?? null;
    }

    public function get($key)
    {
        $postData = $this->post();
        return $postData[$key] ?? $_GET[$key] ?? null;
    }

    public function getAll()
    {
        return $_GET;
    }

    public function post()
    {
        $postData = $_POST;
        if (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
            $jsonData = json_decode(file_get_contents('php://input'), true);
            if (is_array($jsonData)) {
                $postData = array_merge($postData, $jsonData);
            }
        }
        return $postData;
    }

    public function files()
    {
        return $_FILES;
    }

    public function file($key)
    {
        return $_FILES[$key] ?? null;
    }

    public function session($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function setSession($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function destroySession()
    {
        $_SESSION = [];
        session_destroy();
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