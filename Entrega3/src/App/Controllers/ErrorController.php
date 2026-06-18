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
        echo $this->twig->render('not-found.html.twig', get_defined_vars());
    }
    public function internalError()
    {
        http_response_code(500);
        $titulo = "Error interno del servidor";
        $menu = $this->menu;
        $redes = $this->redes;
        echo $this->twig->render('internal_error.html.twig', get_defined_vars());
    }
    public function invalidFormat($e){
        http_response_code(400);
        $titulo = 'Invalid Format';
        $menu = $this->menu;
        $redes = $this->redes;
        echo $this->twig->render('invalid_format.html.twig', get_defined_vars());
    }
}
