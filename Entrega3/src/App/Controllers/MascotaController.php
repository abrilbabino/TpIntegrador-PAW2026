<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\MascotaCollection;
use \Paw\App\Models\Refugio;

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

        $refugio = new Refugio();
        $refugio->setQueryBuilder($this->model->getQueryBuilder());
        if ($mascota && $mascota->fields['refugio_id']) {
            try {
                $refugio->load($mascota->fields['refugio_id']);
            } catch (\Exception $e) {
                // Manejar error al cargar el refugio
            }
        }

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
}
