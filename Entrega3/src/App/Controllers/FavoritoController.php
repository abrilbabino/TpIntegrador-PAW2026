<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\Favorito;

class FavoritoController extends Controller
{
    public ?string $modelName = Favorito::class;

    /**
     * Guarda una mascota como favorita.
     * Requiere sesión activa y mascota_id por POST.
     */
    public function guardar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar sesión
        if (empty($_SESSION['user'])) {
            header('Location: /iniciar-sesion');
            exit;
        }

        $adoptanteId = $_SESSION['user']['id'] ?? null;

        if (!$adoptanteId) {
            $this->log->warning("Intento de guardar favorito sin adoptante vinculado", [
                'user_id' => $_SESSION['user']['id'],
            ]);
            header('Location: /perfil');
            exit;
        }

        $mascotaId = $this->request->get('mascota_id');

        // Validar mascota_id
        if (!$mascotaId || !is_numeric($mascotaId) || $mascotaId < 1) {
            $this->log->warning("mascota_id inválido en favorito", ['mascota_id' => $mascotaId]);
            header('Location: /perfil');
            exit;
        }

        $mascotaId = (int) $mascotaId;

        // Guardar favorito (el modelo evita duplicados)
        $resultado = $this->model->agregar($adoptanteId, $mascotaId);

        if ($resultado) {
            $this->log->info("Favorito guardado", [
                'adoptante_id' => $adoptanteId,
                'mascota_id'   => $mascotaId,
            ]);
        }

        header('Location: /perfil');
        exit;
    }

    /**
     * Elimina un favorito por ID.
     * Requiere sesión activa y favorito_id por POST.
     */
    public function eliminar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user'])) {
            header('Location: /iniciar-sesion');
            exit;
        }

        $adoptanteId = $_SESSION['user']['id'] ?? null;
        $favoritoId  = $this->request->get('favorito_id');

        if (!$adoptanteId || !$favoritoId || !is_numeric($favoritoId)) {
            header('Location: /perfil');
            exit;
        }

        $this->model->eliminar((int) $favoritoId, (int) $adoptanteId);

        $this->log->info("Favorito eliminado", [
            'favorito_id'  => $favoritoId,
            'adoptante_id' => $adoptanteId,
        ]);

        header('Location: /perfil');
        exit;
    }
}
