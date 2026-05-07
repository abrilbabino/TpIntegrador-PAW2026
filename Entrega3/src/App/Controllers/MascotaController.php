<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\MascotaCollection;
use Paw\App\Models\RefugioCollection; 
use Paw\App\Models\RegistroSanitarioCollection;


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

        $refugios = new RefugioCollection();
        $refugios->setQueryBuilder($this->model->getQueryBuilder());
        $refugio =$refugios->get($mascota->fields['refugio_id']);

        $ubicaciones = [];
        if ($mascota && $mascota->fields['refugio_id']) {
            $sql = "SELECT ciudad, provincia FROM ubicacion WHERE refugio_id = :rid ORDER BY ciudad";
            $stmt = $this->model->getQueryBuilder()->getConnection()->prepare($sql);
            $stmt->bindValue(':rid', $mascota->fields['refugio_id'], \PDO::PARAM_INT);
            $stmt->execute();
            $ubicaciones = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

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

    public function libreta()
    {
        $request = $this->request;
        $menu  = $this->menu;
        $redes = $this->redes;
        $id = $request->get('id');

        $mascota = $this->model->get($id);

        $filtros = [
            'anio' => $request->get('anio'),
            'mes' => $request->get('mes'),
            'categoria' => $request->get('categoria'),
        ];

        $coleccion = new RegistroSanitarioCollection();
        $coleccion->setQueryBuilder($this->model->getQueryBuilder());
        $registros = $coleccion->getByMascota((int)$id, $filtros);

        $proximos = [];
        $historial = [];
        $hoy = date('Y-m-d');

        $proximos = $coleccion->pendientes($registros,$hoy);
        $historial = $coleccion->completos($registros,$hoy);

        require $this->viewsDir . '/libreta.view.php';
    }
}
