<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\Mascota;
use Paw\App\Models\MediaMascotaCollection;
use Paw\Core\MailService;

class AdopcionController extends Controller
{
    public ?string $modelName = \Paw\App\Models\SolicitudAdopcion::class;

    public function formulario()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si no está logueado o no es adoptante, redirigir a login
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'adoptante') {
            header('Location: /iniciar-sesion?error=perfil_requerido');
            exit;
        }

        $menu = $this->menu;
        $redes = $this->redes;
        $errores = [];

        $id = $this->request->get('id');
        [$mascota, $mediaExtras] = $this->cargarMediaMascota($id);

        // Obtener datos del adoptante para pre-completar el formulario
        $userModel = new \Paw\App\Models\User;
        $userModel->setQueryBuilder($this->model->getQueryBuilder());
        $adoptanteData = $userModel->getAdoptante((int)$_SESSION['user']['id']);
        $userData = $userModel->findById((int)$_SESSION['user']['id']);

        require $this->viewsDir . '/formulario-adopcion.view.php';
    }

    public function enviar()
    {
        global $config;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Seguridad: Verificar sesión en el envío también
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'adoptante') {
            header('Location: /iniciar-sesion');
            exit;
        }

        $menu = $this->menu;
        $redes = $this->redes;

        $datos = $this->request->post();
        // Inyectar el ID del adoptante desde la sesión (como favoritos)
        $datos['adoptante_id'] = $_SESSION['user']['id'];

        $this->model->set($datos);
        $errores = $this->model->validar();

        $mascota_id = $this->model->fields['mascota_id'];

        if (count($errores) > 0) {
            [$mascota, $mediaExtras] = $this->cargarMediaMascota($mascota_id);
            require $this->viewsDir . '/formulario-adopcion.view.php';
        } else {
            $mascota = $this->cargarMascota($mascota_id);
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
        $mascota->setQueryBuilder($this->model->getQueryBuilder());
        $mascota->load($id);
        return $mascota;
    }

    private function cargarMediaMascota($id): array
    {
        $mascota = $this->cargarMascota($id);

        $mediaCol = new MediaMascotaCollection;
        $mediaCol->setQueryBuilder($mascota->getQueryBuilder());
        $mediaExtras = $mediaCol->getMultimedia(
            (int)$mascota->fields['id'],
            $mascota->fields['imagen'] ?? null
        );

        return [$mascota, $mediaExtras];
    }
}
