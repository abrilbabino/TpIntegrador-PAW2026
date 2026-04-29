<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;

class PageController extends Controller
{
    public function index()
    {
        $titulo = htmlspecialchars($_GET["nombre"] ?? "Inicio-PawMap");
        $menu = $this->menu;
        $redes = $this->redes;
        require $this -> viewsDir . '/index.view.php';
    }

    public function contacto()
    {
        $titulo = "Contacto - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/contacto.view.php';
    }
}