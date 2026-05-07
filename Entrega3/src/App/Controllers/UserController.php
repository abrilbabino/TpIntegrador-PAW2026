<?php
 
namespace Paw\App\Controllers;
 
use Paw\Core\Controller;
use Paw\App\Models\User;
use Paw\App\Models\Adoptante;
use Paw\App\Models\Refugio;
use Paw\App\Models\Favorito;
use Paw\Core\Database\QueryBuilder;
 
class UserController extends Controller
{
    public ?string $modelName = User::class;
 
    public function perfil()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
 
        if (empty($_SESSION['user'])) {
            header('Location: /iniciar-sesion');
            exit;
        }

        $user = $_SESSION['user'];
        $rol  = $user['rol'] ?? 'adoptante';
 
        if ($rol === 'refugio') {
            $this->cargarPerfilRefugio($user);
        } else {
            $this->cargarPerfilAdoptante($user);
        }
    }
 
    private function getQb(): QueryBuilder
    {
        return new QueryBuilder(
            $this->model->getQueryBuilder()->getConnection(),
            $this->log
        );
    }
 
    private function cargarPerfilAdoptante(array $user): void
    {
        $menu  = $this->menu;
        $redes = $this->redes;
 
        // Cargar modelo Adoptante
        $adoptanteModel = new Adoptante();
        $adoptanteModel->setQueryBuilder($this->getQb());
        $adoptanteModel->load((int) $user['id']);
        $adoptante = $adoptanteModel->fields;
 
        $favoritos   = [];
        $solicitudes = [];
        $adopciones  = [];
 
        $adoptanteId = $user['id'] ?? null;
 
        if ($adoptanteId) {
            $favoritoModel = new Favorito();
            $favoritoModel->setQueryBuilder($this->getQb());
            $favoritos = $favoritoModel->getByAdoptanteId((int) $adoptanteId);
 
            /* CUANDO ESTE ADOPCION DE SOLICITUDES Y ADOPCIONES FUNCIONE, DESCOMENTAR ESTO:
            $solicitudes = $this->model->getSolicitudesAdoptante((int) $adoptanteId);
            $adopciones  = $this->model->getAdopcionesAdoptante((int) $adoptanteId);*/
        }
 
        $titulo = "Mi Perfil - PawMap";
        require $this->viewsDir . '/perfil.view.php';
    }
 
    private function cargarPerfilRefugio(array $user): void
    {
        $menu  = $this->menu;
        $redes = $this->redes;
 
        // Cargar modelo Refugio
        $refugioModel = new Refugio();
        $refugioModel->setQueryBuilder($this->getQb());
        $refugioModel->load((int) $user['id']);
        $refugio = $refugioModel->fields;
 
        $mascotas = [];
        // cuando tengas MascotaModel:
        // $refugioId = $user['refugio_id'] ?? null;
        // $mascotas = $mascotaModel->getByRefugioId((int) $refugioId);
 
        $titulo = "Mi Refugio - PawMap";
        require $this->viewsDir . '/perfil-refugio.view.php';
    }
}