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

        if (empty($_SESSION['user'])) {
            header('Location: /iniciar-sesion');
            exit;
        }

        $menu  = $this->menu;
        $redes = $this->redes;
        $user  = $_SESSION['user'];
        $rol   = $user['rol'] ?? 'adoptante';

        if ($rol === 'refugio') {
            $this->cargarPerfilRefugio($user);
        } else {
            $this->cargarPerfilAdoptante($user);
        }
    }

    private function cargarPerfilAdoptante(array $user): void
    {
        $favoritos   = [];
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
        $menu  = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/perfil.view.php';
    }

    private function cargarPerfilRefugio(array $user): void
    {
        $mascotas  = [];
        $refugioId = $user['refugio_id'] ?? null;

        if ($refugioId) {
            // Cuando tengas MascotaModel podés traer las mascotas del refugio
            // $mascotas = $this->mascotaModel->getByRefugioId((int) $refugioId);
        }

        $titulo = "Mi Refugio - PawMap";
        $menu  = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/perfil-refugio.view.php';
    }
}
