<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\ColaEsperaCollection;

class ColaEsperaController extends Controller
{
    public ?string $modelName = ColaEsperaCollection::class;

    public function suscribir()
    {
        header('Content-Type: application/json');

        $userSession = $this->request->session('user');
        
        if (empty($userSession) || $this->request->method() !== 'POST') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Para crear alertas necesitás iniciar sesión en tu cuenta.']);
            exit;
        }

        if (($userSession['rol'] ?? '') !== 'adoptante') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Solo los adoptantes pueden crear alertas de búsqueda.']);
            exit;
        }

        $filtros = $this->request->post();
        $usuarioId = (int) $userSession['id'];

        try {
            $resultado = $this->model->agregar($usuarioId, $filtros);

            if ($resultado !== false) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Te avisaremos cuando ingrese un animal con estas características.']);
            } else {
                http_response_code(200);
                echo json_encode(['success' => false, 'message' => 'Ya estás suscripto a esta búsqueda.']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al suscribirse: ' . $e->getMessage()]);
        }
        exit;
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $userSession = $this->request->session('user');
        
        if (empty($userSession) || $this->request->method() !== 'POST') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Para crear alertas necesitás iniciar sesión en tu cuenta.']);
            exit;
        }

        $postData = $this->request->post();
        $id = $postData['id'] ?? null;
        $usuarioId = (int) $userSession['id'];

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de alerta no especificado.']);
            exit;
        }

        try {
            $resultado = $this->model->delete((int)$id, $usuarioId);
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Alerta eliminada correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró la alerta o no te pertenece.']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al eliminar alerta: ' . $e->getMessage()]);
        }
        exit;
    }
}
