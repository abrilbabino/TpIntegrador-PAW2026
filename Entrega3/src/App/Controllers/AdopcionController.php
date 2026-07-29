<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\Mascota;
use Paw\App\Models\MediaMascotaCollection;
use Paw\App\Models\User;
use Paw\Core\MailService;
use Paw\Core\PdfService;

class AdopcionController extends Controller
{
    public ?string $modelName = \Paw\App\Models\SolicitudAdopcion::class;

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
        $userModel = new User;
        $userModel->setQueryBuilder($this->model->getQueryBuilder());
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

        $this->model->set($datos);
        $errores = $this->model->validar();

        $mascota_id = $this->model->fields['mascota_id'];

        if (count($errores) > 0) {
            [$mascota, $mediaExtras] = $this->cargarMediaMascota($mascota_id);
            echo $this->twig->render('formulario-adopcion.html.twig', get_defined_vars());
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

            $id = $mascota_id;
            [$mascota, $mediaExtras] = $this->cargarMediaMascota($id);
            
            $userModel = new User;
            $userModel->setQueryBuilder($this->model->getQueryBuilder());
            $adoptanteData = $userModel->getAdoptante((int)$userSession['id']);
            $userData = $userModel->findById((int)$userSession['id']);

            echo $this->twig->render('formulario-adopcion.html.twig', [
                'mascota' => $mascota,
                'mediaExtras' => $mediaExtras,
                'adoptanteData' => $adoptanteData,
                'userData' => $userData,
                'flash_type' => 'adopcion',
                'descargar_pdf_mascota_id' => $id,
            ]);
            exit;
        }
    }

    public function descargarAcuerdo()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');
        if (!$userSession || $userSession['rol'] !== 'adoptante') {
            http_response_code(403);
            echo "Acceso denegado. Solo los adoptantes pueden descargar el acuerdo.";
            exit;
        }

        $mascota_id = $this->request->get('mascota_id');
        if (!$mascota_id) {
            http_response_code(400);
            echo "ID de mascota no proporcionado.";
            exit;
        }

        [$mascota, $mediaExtras] = $this->cargarMediaMascota($mascota_id);
        
        $userModel = new User;
        $userModel->setQueryBuilder($this->model->getQueryBuilder());
        $adoptanteData = $userModel->getAdoptante((int)$userSession['id']);
        $userData = $userModel->findById((int)$userSession['id']);

        $pdfService = new PdfService();
        $publicDir = __DIR__ . '/../../../public';
        $fotoPath = $pdfService->imageToBase64($mascota->fields['imagen'] ?? '', $publicDir);

        $htmlPdf = $this->twig->render('pdf/acuerdo_adopcion.html.twig', [
            'adoptante_nombre' => $adoptanteData['nombre'] ?? $this->model->fields['nombre'],
            'adoptante_apellido' => $adoptanteData['apellido'] ?? $this->model->fields['apellido'],
            'adoptante_email' => $userData['email'] ?? $this->model->fields['email'],
            'mascota_nombre' => $mascota->fields['nombre'],
            'mascota_especie' => $mascota->fields['especie'] ?? 'Desconocida',
            'mascota_foto' => $fotoPath,
        ]);

        $pdfBinary = $pdfService->generarDesdeHtml($htmlPdf);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="acuerdo_adopcion_' . $mascota_id . '.pdf"');
        header('Content-Length: ' . strlen($pdfBinary));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdfBinary;
        exit;
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
