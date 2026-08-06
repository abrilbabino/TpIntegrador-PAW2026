<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\NotificacionCollection;

class NotificacionController extends Controller
{
    public ?string $modelName = NotificacionCollection::class;

    /**
     * Devuelve las notificaciones del usuario logueado en formato JSON
     */
    public function getRecientes()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');

        if (empty($userSession) || !isset($userSession['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }

        $usuarioId = (int)$userSession['id'];

        try {
            $notificaciones = $this->model->getRecientes($usuarioId);
            $noLeidasCount = $this->model->contarNoLeidas($usuarioId);

            echo json_encode([
                'success' => true, 
                'notificaciones' => $notificaciones,
                'no_leidas_count' => $noLeidasCount
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Marca un array de IDs de notificaciones como leídas
     */
    public function marcarLeidas()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');

        if (empty($userSession) || !isset($userSession['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }

        $usuarioId = (int)$userSession['id'];
        
        $postData = $this->request->post();
        $ids = $postData['ids'] ?? [];
        if (!is_array($ids) && is_string($ids)) {
            $ids = json_decode($ids, true) ?? [];
        }

        if (empty($ids)) {
            echo json_encode(['success' => true]); // Nada que marcar
            exit;
        }

        try {
            $this->model->marcarComoLeidas($ids, $usuarioId);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
