<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\Favorito;

class FavoritoController extends Controller
{
    public ?string $modelName = Favorito::class;

    /**
     * Alterna (toggle) el estado de favorito de una mascota.
     * Retorna JSON.
     */
    public function toggle()
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');

        if (empty($userSession)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }

        $adoptanteId = $userSession['id'] ?? null;

        if (!$adoptanteId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Usuario inválido']);
            exit;
        }

        $mascotaId = $this->request->get('mascota_id');

        if (!$mascotaId || !is_numeric($mascotaId) || $mascotaId < 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de mascota inválido']);
            exit;
        }

        $mascotaId = (int) $mascotaId;

        // Comprobar si ya es favorito
        $favoritos = $this->model->getByAdoptanteId($adoptanteId);
        $favoritoId = null;
        foreach ($favoritos as $fav) {
            if ($fav['id'] == $mascotaId) { // 'id' es mascota_id por el SELECT m.*
                $favoritoId = $fav['favorito_id'];
                break;
            }
        }

        if ($favoritoId) {
            // Eliminar
            $this->model->eliminar((int)$favoritoId, $adoptanteId);
            http_response_code(200);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            // Agregar
            $this->model->agregar($adoptanteId, $mascotaId);
            http_response_code(200);
            echo json_encode(['success' => true, 'action' => 'added']);
        }
        exit;
    }
}
