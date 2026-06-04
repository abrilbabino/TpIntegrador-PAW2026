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



        require $this->viewsDir . '/refugios.view.php';
    }

    public function apiRefugios() {
        header('Content-Type: application/json');
        
        $resultado = $this->model->getAll(); 
        
        $refugiosData = [];
        foreach ($resultado as $refugio) {
            $refugiosData[] = [
                'id'                     => $refugio->fields['usuario_id'] ?? $refugio->fields['id'],
                'nombre_institucion'     => $refugio->fields['nombre_institucion'],
                'imagen'                 => $refugio->fields['imagen'] ?? 'default-refugio.jpg',
                'ciudad'                 => $refugio->fields['ciudad'],
                'provincia'              => $refugio->fields['provincia'],
                'telefono'               => $refugio->fields['telefono'],
                'adoptables_disponibles' => $refugio->fields['adoptables_disponibles'] ?? 0
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $refugiosData
        ]);
        exit;
    }

    public function detalle()
    {
        $request = $this->request;
        $menu  = $this->menu;
        $redes = $this->redes;
        $id    = $request->get('id');

        $refugio = null;
        $mascotas = [];
        if ($this->model) {
            try {
                $refugio = $this->model->get($id);
                
                $mascotaCollection = new \Paw\App\Models\MascotaCollection();
                $mascotaCollection->setQueryBuilder($this->model->getQueryBuilder());
                $mascotas = $mascotaCollection->getAll(['refugio_id' => $id, 'estado_adopcion' => 'DISPONIBLE']);
                
                $ubicaciones = $this->model->getQueryBuilder()->obtenerUbicacionesPorRefugio((int)$id);
            } catch (\Exception $e) {
                error_log("Error cargando detalle de refugio: " . $e->getMessage());
            }
        }

        require $this->viewsDir . '/detalleRefugio.view.php';
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
