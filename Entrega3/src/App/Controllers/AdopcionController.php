<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\Core\Database\QueryBuilder;
use Paw\App\Models\Mascota;
use Paw\Core\MailService;

class AdopcionController extends Controller
{
    public ?string $modelName = \Paw\App\Models\SolicitudAdopcion::class;

    public function formulario()
    {
        $menu = $this->menu;
        $redes = $this->redes;
        $errores = [];

        $id = $this->request->get('id');

        $mascota = $this->cargarMascota($id);

        require $this->viewsDir . '/formulario-adopcion.view.php';
    }

    public function enviar()
    {
        global $config;

        $menu = $this->menu;
        $redes = $this->redes;

        $this->model->set($this->request->post());
        $errores = $this->model->validar();

        $mascota_id = $this->model->fields['mascota_id'];

        $mascota = $this->cargarMascota($mascota_id);
    

        if (count($errores) > 0) {
            require $this->viewsDir . '/formulario-adopcion.view.php';
        } else {
            $this->model->guardar($mascota->fields['refugio_id']);

            $mailService = new MailService;
            $mailService->enviarConfirmacionAdopcion(
                $config->get('MAIL_PERSONAL'),
                [
                    'nombre_mascota' => $mascota->fields['nombre'],
                    'nombre' => $this->model->fields['nombre'],
                    'apellido' => $this->model->fields['apellido'],
                    'email' => $this->model->fields['email'],
                ]
            );

            header('Location: /adopcion-exitosa');
        }
    }

    private function cargarMascota($id)
    {
        $mascota = new Mascota;
        $mascota->setQueryBuilder(new QueryBuilder($this->connection, $this->log));
        $mascota->load($id);
        return $mascota;
    }
}
