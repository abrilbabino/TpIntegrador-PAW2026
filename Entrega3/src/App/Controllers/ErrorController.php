<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;

class ErrorController extends Controller
{

    public function notFound($e = null)
    {
        http_response_code(404);
        $titulo = 'Página no encontrada';
        $menu = $this->menu;
        $redes = $this->redes;
        
        $mensajePrincipal = 'Ups! Lo sentimos, no pudimos encontrar la página que estás buscando.';

        if ($e) {
            if ($e instanceof \Paw\Core\Exceptions\RouteNotFoundException) {
                $mensaje = 'Ups! ' . $e->getMessage();
            } else {
                $mensaje = $e->getMessage();
            }
        } else {
            $mensaje = $mensajePrincipal;
        }

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
    public function invalidFormat($e = null){
        http_response_code(400);
        $titulo = 'Formato Inválido';
        $menu = $this->menu;
        $redes = $this->redes;
        $mensaje = $e ? $e->getMessage() : 'El formato de los datos proporcionados no es válido.';
        echo $this->twig->render('invalid_format.html.twig', get_defined_vars());
    }
}
