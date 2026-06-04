<?php
 
namespace Paw\App\Controllers;
 
use Paw\Core\Controller;
use Paw\App\Models\User;
use Paw\App\Models\Adoptante;
use Paw\App\Models\Refugio;
use Paw\App\Models\Favorito;
use Paw\App\Models\MascotaCollection;
 
class UserController extends Controller
{
    public ?string $modelName = User::class;
 
    public function perfil()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userSession = $this->request->session('user');

        if (empty($userSession)) {
            header('Location: /iniciar-sesion');
            exit;
        }

        $dbUser = $this->model->findById((int) $userSession['id']);
        $user = array_merge($userSession, $dbUser);
        $user['rol'] = $userSession['rol'] ?? 'adoptante'; // ← preservar el rol de la sesión
        $rol = $user['rol'];
 
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
       $request = $this->request;
       // Cargar modelo Refugio
       $refugioModel = new Refugio();
       $refugioModel->setQueryBuilder($this->model->getQueryBuilder());
       $refugioModel->load((int) $user['id']);
       $refugio = $refugioModel->fields;
       $refugioId = $user['id'] ?? null;
       $mascotas = [];
       $solicitudes = [];
       $tamanos=[];
       $especies=[];
       $temperamentos=[];
       
      
       if ($refugioId) {
           $mascotaCollection = new \Paw\App\Models\MascotaCollection();
           $mascotaCollection->setQueryBuilder($this->model->getQueryBuilder());
           $mascotas = $mascotaCollection->getByRefugioId((int) $refugioId);


           $solicitudesCollection = new \Paw\App\Models\SolicitudAdopcionCollection();
           $solicitudesCollection->setQueryBuilder($this->model->getQueryBuilder());
           $solicitudes = $solicitudesCollection->getSolicitudesRefugio((int) $refugioId);


           $tamanos       = $mascotaCollection->getTamanos();
           $especies      = $mascotaCollection->getEspecies();
           $temperamentos = $mascotaCollection->getTemperamentos();
       }
       $titulo = "Mi Refugio - PawMap";
       require $this->viewsDir . '/perfil-refugio.view.php';
    }


    public function guardar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');

        if (empty($userSession) || $this->request->method() !== 'POST') {
            header('Location: /iniciar-sesion');
            exit;
        }

        $user   = $userSession;
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
            $userSession = array_merge($userSession, $updatedUser);
            $this->request->setSession('user', $userSession);
        }

        header("Location: /perfil?update=success");
        exit;
    }
}