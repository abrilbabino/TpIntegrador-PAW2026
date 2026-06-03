<?php
namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\Refugio;
use Paw\App\Models\RefugioCollection;

class DonacionController extends Controller
{
    public ?string $modelName = RefugioCollection::class;
    
    public function index()
    {
        $titulo = "Donaciones - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;
        $errores = [];
        $valores = [];
        $refugios = $this->model->getAll();

        require $this->viewsDir . '/donacion.view.php';
    }

    public function enviar()
    {
        $titulo = "Donaciones - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;

        $valores = [
            'refugio_id' => $this->request->get('refugio_id'),
            'monto' => $this->request->get('monto'),
            'metodo_pago' => $this->request->get('metodo_pago'),
        ];

        $errores = $this->validar($valores);

        if (!empty($errores)) {
            $refugios = $this->model->getAll();
            require $this->viewsDir . '/donacion.view.php';
            return;
        }

        try {
            $refugio = $this->model->get((int) $valores['refugio_id']);
        } catch (\Exception $e) {
            $errores[] = "No se encontro el refugio seleccionado.";
            $refugios = $this->model->getAll();
            require $this->viewsDir . '/donacion.view.php';
            return;
        }

        $monto = number_format((float) $valores['monto'], 2, ',', '.');
        $metodoPago = $valores['metodo_pago'] === 'mp' ? 'Mercado Pago' : 'Transferencia bancaria';

        require $this->viewsDir . '/donacion-exitosa.view.php';
    }
}
