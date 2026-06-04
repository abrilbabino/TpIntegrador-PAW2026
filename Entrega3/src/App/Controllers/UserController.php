<?php
 
namespace Paw\App\Controllers;
 
use Paw\Core\Controller;
use Paw\App\Models\User;
use Paw\App\Models\Adoptante;
use Paw\App\Models\Refugio;
use Paw\App\Models\Favorito;
 
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

        $dbUser = $this->model->findById((int) $_SESSION['user']['id']);
        $user = array_merge($_SESSION['user'], $dbUser);
        $rol  = $user['rol'] ?? 'adoptante';
 
        if ($rol === 'refugio') {
            $this->cargarPerfilRefugio($user);
        } else {
            $this->cargarPerfilAdoptante($user);
        }
    }
 
    private function cargarPerfilAdoptante(array $user, array $errores = [], array $oldData = []): void
    {
        $menu  = $this->menu;
        $redes = $this->redes;
 
        // Cargar modelo Adoptante
        $adoptanteModel = new Adoptante();
        $adoptanteModel->setQueryBuilder($this->model->getQueryBuilder());
        $adoptanteModel->load((int) $user['id']);
        $adoptante = $adoptanteModel->fields;

        $favoritos   = [];
        $solicitudes = [];
        $adopciones  = [];
 
        $adoptanteId = $user['id'] ?? null;
 
        if ($adoptanteId) {
            $favoritoModel = new Favorito();
            $favoritoModel->setQueryBuilder($this->model->getQueryBuilder());
            $favoritos = $favoritoModel->getByAdoptanteId((int) $adoptanteId);
 
            $solicitudesCollection = new \Paw\App\Models\SolicitudAdopcionCollection();
            $solicitudesCollection->setQueryBuilder($this->model->getQueryBuilder());
            
            $solicitudes = $solicitudesCollection->getSolicitudesAdoptante((int) $adoptanteId);
            $adopciones  = $solicitudesCollection->getAdopcionesAdoptante((int) $adoptanteId);
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
        $refugioModel->setQueryBuilder($this->model->getQueryBuilder());
        $refugioModel->load((int) $user['id']);
        $refugio = $refugioModel->fields;
 
        $refugioId = $user['id'] ?? null;
        $mascotas = [];
        
        if ($refugioId) {
            $mascotaCollection = new \Paw\App\Models\MascotaCollection();
            $mascotaCollection->setQueryBuilder($this->model->getQueryBuilder());
            $mascotas = $mascotaCollection->getByRefugioId((int) $refugioId);
        }

 
        $titulo = "Mi Refugio - PawMap";
        require $this->viewsDir . '/perfil-refugio.view.php';
    }

    public function guardar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user']) || $this->request->method() !== 'POST') {
            header('Location: /iniciar-sesion');
            exit;
        }

        $user   = $_SESSION['user'];
        $userId = (int) $user['id'];
        $errores = $this->model->actualizarPerfilCompleto(
            $userId, 
            $this->request->post(), 
            $this->request->file('foto_perfil_o_logo'), 
            $user
        );

        if (!empty($errores)) {
            $this->cargarPerfilAdoptante($user, $errores, $this->request->post());
            return;
        }

        $updatedUser = $this->model->findById($userId);
        if ($updatedUser) {
            $_SESSION['user'] = array_merge($_SESSION['user'], $updatedUser);
        }

        header("Location: /perfil?update=success");
        exit;
    }
}