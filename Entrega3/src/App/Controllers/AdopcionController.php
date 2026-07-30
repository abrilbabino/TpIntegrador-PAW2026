<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\Mascota;
use Paw\App\Models\MediaMascotaCollection;
use Paw\App\Models\User;
use Paw\Core\MailService;

use Paw\App\Models\SolicitudAdopcion;
use Paw\App\Models\SolicitudAdopcionCollection;

class AdopcionController extends Controller
{
    public ?string $modelName = SolicitudAdopcionCollection::class;

    public function formulario()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');

        // Si no está logueado o no es adoptante, redirigir a login
        if (empty($userSession) || $userSession['rol'] !== 'adoptante') {
            header('Location: /?auth=login&error=perfil_requerido');
            exit;
        }

        $menu = $this->menu;
        $redes = $this->redes;
        $errores = [];

        $id = $this->request->get('id');
        [$mascota, $mediaExtras] = $this->cargarMediaMascota($id);

        // Obtener datos del adoptante para pre-completar el formulario
        $userModel = $this->loadModel(User::class);
        $adoptanteData = $userModel->getAdoptante((int)$userSession['id']);
        $userData = $userModel->findById((int)$userSession['id']);

        // Verificar proactivamente si ya existe una solicitud para esta mascota
        if (!empty($id) && $this->model->existeSolicitud((int)$userSession['id'], (int)$id)) {
            $errores['solicitud_duplicada'] = 'Ya enviaste una solicitud de adopción para esta mascota. No es posible enviar más de una solicitud por mascota.';
        }

        echo $this->twig->render('formulario-adopcion.html.twig', get_defined_vars());
    }

    public function enviar()
    {
        global $config;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');

        // Seguridad: Verificar sesión en el envío también
        if (empty($userSession) || $userSession['rol'] !== 'adoptante') {
            header('Location: /?auth=login');
            exit;
        }

        $menu = $this->menu;
        $redes = $this->redes;

        $datos = $this->request->post();
        // Inyectar el ID del adoptante desde la sesión (como favoritos)
        $datos['adoptante_id'] = $userSession['id'];

        $solicitudEntity = new SolicitudAdopcion();
        $solicitudEntity->set($datos);
        $errores = $solicitudEntity->validar();

        // Verificar duplicados
        if (empty($errores) && $this->model->existeSolicitud((int)$datos['adoptante_id'], (int)$datos['mascota_id'])) {
            $errores['solicitud_duplicada'] = 'Ya enviaste una solicitud de adopción para esta mascota.';
        }

        $mascota_id = $solicitudEntity->fields['mascota_id'];

        if (count($errores) > 0) {
            [$mascota, $mediaExtras] = $this->cargarMediaMascota($mascota_id);
            echo $this->twig->render('formulario-adopcion.html.twig', get_defined_vars());
        } else {
            $mascota = $this->cargarMascota($mascota_id);
            $this->model->guardar($solicitudEntity, $mascota->fields['refugio_id']);

            $mailService = new MailService;
            $mailService->enviarConfirmacionAdopcion(
                $config->get('MAIL_PERSONAL'),
                [
                    'nombre_mascota' => $mascota->fields['nombre'],
                    'nombre' => $solicitudEntity->fields['nombre'],
                    'apellido' => $solicitudEntity->fields['apellido'],
                    'email' => $solicitudEntity->fields['email'],
                ]
            );

            $id = $mascota_id;
            [$mascota, $mediaExtras] = $this->cargarMediaMascota($id);
            $userModel = $this->loadModel(User::class);
            $adoptanteData = $userModel->getAdoptante((int)$userSession['id']);
            $userData = $userModel->findById((int)$userSession['id']);

            echo $this->twig->render('formulario-adopcion.html.twig', [
                'mascota' => $mascota,
                'mediaExtras' => $mediaExtras,
                'adoptanteData' => $adoptanteData,
                'userData' => $userData,
                'flash_type' => 'adopcion'
            ]);
            exit;
        }
    }

    private function cargarMascota($id)
    {
        $mascota = $this->loadModel(Mascota::class);
        $mascota->load($id);
        return $mascota;
    }

    private function cargarMediaMascota($id): array
    {
        $mascota = $this->cargarMascota($id);

        $mediaCol = $this->loadCollection(MediaMascotaCollection::class);
        $mediaExtras = $mediaCol->getMultimedia(
            (int)$mascota->fields['id'],
            $mascota->fields['imagen'] ?? null
        );

        return [$mascota, $mediaExtras];
    }

    public function actualizar()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');

        // Validar que el usuario esté logueado y sea refugio
        if (empty($userSession) || ($userSession['rol'] ?? '') !== 'refugio') {
            http_response_code(403);
            echo json_encode(['success' => false, 'mensaje' => 'No autorizado. Debe iniciar sesión como refugio.']);
            exit;
        }

        $userId = (int) $userSession['id'];

        // Obtener el body JSON de la solicitud
        $body = json_decode(file_get_contents('php://input'), true);
        $solicitudId = $body['id'] ?? null;
        $accion = $body['accion'] ?? null;

        if (!$solicitudId || !is_numeric($solicitudId) || $solicitudId < 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensaje' => 'ID de solicitud inválido.']);
            exit;
        }

        try {
            $nuevoEstado = $this->model->procesarSolicitud((int)$solicitudId, $accion, $userId);
            echo json_encode(['success' => true, 'estado' => $nuevoEstado]);
        } catch (\Exception $e) {
            $code = $e->getCode();
            if ($code < 100 || $code > 599) {
                $code = 500;
            }
            http_response_code($code);
            echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
        }
        exit;
    }
}
