<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\MascotaCollection;

class MascotaController extends Controller
{
    public ?string $modelName = MascotaCollection::class;

    public function adoptar()
    {
        $request = $this->request;
        $menu    = $this->menu;
        $redes   = $this->redes;

        $filtros = $this->getFiltros();
        $page = (int) $request->get('pagina') ?: 1;
        $perPage = 6;

        $resultado = $this->model->getPaginated($filtros, $page, $perPage);
        $mascotas = $resultado['items'];
        $pagination = $resultado['pagination'];

        $tamanos     = $this->model->getTamanos();
        $especies     = $this->model->getEspecies();
        $temperamentos = $this->model->getTemperamentos();

        require $this->viewsDir . '/adoptar.view.php';
    }

    private function getFiltros()
    {
        $request = $this->request;
        return [
            'especie'        => $request->get('especie'),
            'tamano'         => $request->get('tamano'),
            'temperamento'    => $request->get('temperamento'),
            'edad_min'       => $request->get('edad_min'),
            'edad_max'       => $request->get('edad_max'),
            'ubicacion'      => $request->get('ubicacion'),
            'estado_adopcion' => 'DISPONIBLE',
        ];
    }

    public function detalle()
    {
        $request = $this->request;
        $menu  = $this->menu;
        $redes = $this->redes;
        $id    = $request->get('id');
        $mascota = $this->model->get($id);

        require $this->viewsDir . '/mascota.view.php';
    }

    public function buscar()
    {
        $request = $this->request;
        $menu  = $this->menu;
        $redes = $this->redes;
        $q     = $request->get('q');
        $page = (int) $request->get('pagina') ?: 1;
        $perPage = 6;

        $resultado = $this->model->buscarPaginated($q, $page, $perPage);
        $mascotas = $resultado['items'];
        $pagination = $resultado['pagination'];

        $tamanos     = $this->model->getTamanos();
        $especies     = $this->model->getEspecies();
        $temperamentos = $this->model->getTemperamentos();

        require $this->viewsDir . '/adoptar.view.php';
    }
}
