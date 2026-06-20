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
        $metaDescription = "Conocé los refugios y protectoras de animales asociados a PawMap. Apoyá su labor y encontrá a tu nueva mascota en tu zona.";

        

        echo $this->twig->render('refugios.html.twig', get_defined_vars());
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
        $ciudad = '';
        $prov = '';
        $metaDescription = "Conocé este refugio en PawMap. Mirá las mascotas que tienen en adopción y apoyá su causa.";
        
        if ($this->model) {
            try {
                $refugio = $this->model->get($id);
                if ($refugio) {
                    $nombreRefugio = htmlspecialchars($refugio->fields['nombre_institucion'] ?? 'Refugio');
                    $metaDescription = "Conocé a {$nombreRefugio}, un refugio en PawMap. Mirá las mascotas que tienen en adopción y apoyá su causa.";
                }
                
                $mascotaCollection = new \Paw\App\Models\MascotaCollection();
                $mascotaCollection->setQueryBuilder($this->model->getQueryBuilder());
                $mascotas = $mascotaCollection->getAll(['refugio_id' => $id, 'estado_adopcion' => 'DISPONIBLE']);
                
                $ubicaciones = $this->model->getQueryBuilder()->obtenerUbicacionesPorRefugio((int)$id);
                $ciudades = [];
                $provincias = [];
                foreach ($ubicaciones as $u) {
                    if (!empty($u['ciudad'])) $ciudades[] = $u['ciudad'];
                    if (!empty($u['provincia'])) $provincias[] = $u['provincia'];
                }
                $ciudades = array_unique($ciudades);
                $provincias = array_unique($provincias);
                $ciudad = implode(', ', $ciudades);
                $prov = implode(', ', $provincias);
            } catch (\Exception $e) {
                error_log("Error cargando detalle de refugio: " . $e->getMessage());
            }
        }

        $favoritoModel = new \Paw\App\Models\Favorito();
        $favoritoModel->setQueryBuilder($this->model->getQueryBuilder());
        $favoritosIds = $favoritoModel->getFavoritosIds($this->request->session('user'));

        echo $this->twig->render('detalleRefugio.html.twig', get_defined_vars());
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
