<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\RefugioCollection;

class RefugioController extends Controller
{
    public ?string $modelName = RefugioCollection::class;

    public function lista()
    {
        $request = $this->request;
        $menu    = $this->menu;
        $redes   = $this->redes;

        $filtros = $this->getFiltros();
        $page = (int) $request->get('pagina') ?: 1;
        $perPage = 6;

        $resultado = $this->model->getPaginated($filtros, $page, $perPage);
        $refugios = $resultado['items'];
        $pagination = $resultado['pagination'];

        $provincias = $this->model->getProvincias();
        $ciudades   = $this->model->getCiudades();

        require $this->viewsDir . '/refugios.view.php';
    }

    public function detalle()
    {
        $request = $this->request;
        $menu  = $this->menu;
        $redes = $this->redes;
        $id    = $request->get('id');

        $refugio = null;
        if ($this->model) {
            try {
                $refugio = $this->model->get($id);
            } catch (\Exception $e) {
                error_log("Error cargando detalle de refugio: " . $e->getMessage());
            }
        }

        require $this->viewsDir . '/refugio.view.php';
    }

    private function getFiltros()
    {
        $request = $this->request;
        return [
            'provincia' => $request->get('provincia'),
            'ciudad'    => $request->get('ciudad'),
        ];
    }
}
