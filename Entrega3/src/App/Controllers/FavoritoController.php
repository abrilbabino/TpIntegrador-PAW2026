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

        if (empty($_SESSION['user'])) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }

        $adoptanteId = $_SESSION['user']['id'] ?? null;

        if (!$adoptanteId) {
            echo json_encode(['success' => false, 'error' => 'Usuario inválido']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $mascotaId = $input['mascota_id'] ?? $this->request->get('mascota_id');

        if (!$mascotaId || !is_numeric($mascotaId) || $mascotaId < 1) {
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
            $resultado = $this->model->eliminar((int) $favoritoId, $adoptanteId);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            // Agregar
            $resultado = $this->model->agregar($adoptanteId, $mascotaId);
            echo json_encode(['success' => true, 'action' => 'added']);
        }
        exit;
    }
}
