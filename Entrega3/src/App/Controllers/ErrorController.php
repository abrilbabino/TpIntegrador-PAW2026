<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;

class ErrorController extends Controller
{
    public function notFound()
    {
        http_response_code(404);
        $titulo = 'Página no encontrada';
        $mensaje_error = 'Error 404: La página solicitada no existe.';
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/not-found.view.php';
    }

    public function internalError()
    {
        http_response_code(500);
        $titulo = "Error interno del servidor";
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/internal_error.view.php';
    }

    public function invalidFormat($e = null)
    {
        http_response_code(400);
        $titulo = 'Formato Inválido';
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/invalid_format.view.php';
    }

    public function mascotaNotFound($e = null)
    {
        http_response_code(404);
        $titulo = 'Mascota no encontrada';
        $mensaje_error = 'Error: La mascota que estás buscando no existe o ya no está disponible para adopción.';
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/not-found.view.php'; 
    }
}