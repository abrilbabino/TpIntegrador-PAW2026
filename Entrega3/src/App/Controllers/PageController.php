<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;

class PageController extends Controller
{
    public function index()
    {
        $titulo = htmlspecialchars($_GET["nombre"] ?? "Inicio-PawMap");
        $redes = $this->redes;
        require $this -> viewsDir . '/index.view.php';
    }
}