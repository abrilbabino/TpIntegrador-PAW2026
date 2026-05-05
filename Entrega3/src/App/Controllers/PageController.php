<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\MascotaCollection;

class PageController extends Controller
{
    public ?string $modelName = MascotaCollection::class;

    public function index()
    {
        $titulo = htmlspecialchars($_GET["nombre"] ?? "Inicio-PawMap");
        $menu = $this->menu;
        $redes = $this->redes;
        
        $mascotas = $this->model->getAll(['estado_adopcion' => 'DISPONIBLE']);

        require $this->viewsDir . '/index.view.php';
    }

    public function contacto()
    {
        $titulo = "Contacto - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/contacto.view.php';
    }

    public function comoAdoptar()
    {
        $titulo = "Como Adoptar - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/como-adoptar.view.php';
    }
}