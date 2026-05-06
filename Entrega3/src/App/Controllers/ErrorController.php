<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;

class ErrorController extends Controller
{

    public function notFound()
    {
        http_response_code(404);
        $titulo = 'Pagina no encontrada';
        $menu = $this->menu;
        $redes = $this->redes;
        require $this -> viewsDir . '/not-found.view.php';
    }
    public function internalError()
    {
        http_response_code(500);
        $titulo = "Error interno del servidor";
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/internal_error.view.php';
    }
    public function invalidFormat($e){
        http_response_code(400);
        $titulo = 'Invalid Format';
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/invalid_format.view.php';
    }
}