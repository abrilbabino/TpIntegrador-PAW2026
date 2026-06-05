<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\SolicitudAdopcion;

class SolicitudController extends Controller
{
    public ?string $modelName = SolicitudAdopcion::class;

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

        if ($accion !== 'aceptar' && $accion !== 'rechazar') {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensaje' => 'Acción no válida. Solo se permite aceptar o rechazar.']);
            exit;
        }

        $solicitudId = (int)$solicitudId;

        // Cargar la solicitud de adopción
        $db = $this->model->getQueryBuilder();
        $solicitud = $db->selectOne('solicitud_de_adopcion', ['id' => $solicitudId]);

        if (!$solicitud) {
            http_response_code(404);
            echo json_encode(['success' => false, 'mensaje' => 'No se encontró la solicitud especificada.']);
            exit;
        }

        // Validar que el refugio de la solicitud sea el usuario logueado
        if ((int)$solicitud['refugio_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para modificar esta solicitud.']);
            exit;
        }

        // Solo permitir modificar si está PENDIENTE
        if (strtoupper($solicitud['estado'] ?? '') !== 'PENDIENTE') {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensaje' => 'La solicitud ya ha sido procesada previamente.']);
            exit;
        }

        $nuevoEstado = ($accion === 'aceptar') ? 'ACEPTADA' : 'RECHAZADA';

        // Iniciar transacción en la base de datos para consistencia
        $db->getConnection()->beginTransaction();
        try {
            // Actualizar estado de la solicitud
            $db->update('solicitud_de_adopcion', [
                'estado' => $nuevoEstado,
                'fecha_aceptacion' => date('Y-m-d H:i:s')
            ], ['id' => $solicitudId]);

            // Si es aceptada, marcar mascota como adoptada
            if ($accion === 'aceptar') {
                $mascotaId = (int)$solicitud['mascota_id'];
                $db->update('mascota', [
                    'estado_adopcion' => 'ADOPTADO',
                    'fecha_adopcion' => date('Y-m-d H:i:s')
                ], ['id' => $mascotaId]);
            }

            $db->getConnection()->commit();
        } catch (\Exception $e) {
            $db->getConnection()->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar la base de datos: ' . $e->getMessage()]);
            exit;
        }

        echo json_encode(['success' => true, 'estado' => $nuevoEstado]);
        exit;
    }
}
