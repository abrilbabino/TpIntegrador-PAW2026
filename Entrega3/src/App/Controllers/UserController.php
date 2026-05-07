<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\User;
use Paw\App\Models\Favorito;
use Paw\Core\Database\QueryBuilder;

class UserController extends Controller
{
    public ?string $modelName = User::class;

    /**
     * Muestra el perfil del usuario con sus favoritos.
     * Requiere sesión activa, sino redirige a login.
     */
    public function perfil()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar sesión
        if (empty($_SESSION['user'])) {
            header('Location: /iniciar-sesion');
            exit;
        }

        $menu  = $this->menu;
        $redes = $this->redes;
        $user  = $_SESSION['user'];

        // Obtener favoritos del usuario
        $favoritos = [];
        $adoptanteId = $user['adoptante_id'] ?? null;

        if ($adoptanteId) {
            $favoritoModel = new Favorito();
            $qb = new QueryBuilder(
                $this->model->getQueryBuilder()->getConnection(),
                $this->log
            );
            $favoritoModel->setQueryBuilder($qb);
            $favoritos = $favoritoModel->getByAdoptanteId((int) $adoptanteId);
        }

        $titulo = "Mi Perfil - PawMap";

        require $this->viewsDir . '/perfil.view.php';
    }
}
