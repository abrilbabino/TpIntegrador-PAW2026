<?php
 
namespace Paw\App\Controllers;
 
use Paw\Core\Controller;
use Paw\App\Models\User;
use Paw\App\Models\Adoptante;
use Paw\App\Models\Refugio;
use Paw\App\Models\Favorito;
use Paw\App\Models\MascotaCollection;
use Paw\App\Models\SolicitudAdopcionCollection;
use Paw\App\Helpers\GCSHelper;
 
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
            header('Location: /?auth=login');
            exit;
        }

        $dbUser = $this->model->findById((int) $userSession['id']);
        
        if (!$dbUser) {
            $this->request->unsetSession('user');
            header('Location: /?auth=login');
            exit;
        }

        if (array_key_exists('rol', $dbUser)) unset($dbUser['rol']);
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
 
        $adoptanteModel = $this->loadModel(Adoptante::class);
        $adoptanteModel->load((int) $user['id']);
        $adoptante = $adoptanteModel->fields;

        $favoritos   = [];
        $solicitudes = [];
        $adopciones  = [];
 
        $adoptanteId = $user['id'] ?? null;
 
        if ($adoptanteId) {
            $favoritoModel = $this->loadModel(Favorito::class);
            $favoritos = $favoritoModel->getByAdoptanteId((int) $adoptanteId);
 
            $solicitudesCollection = $this->loadCollection(SolicitudAdopcionCollection::class);
            
            $solicitudes = $solicitudesCollection->getSolicitudesAdoptante((int) $adoptanteId);
            $adopciones  = $solicitudesCollection->getAdopcionesAdoptante((int) $adoptanteId);
        }
 
        $titulo = "Mi Perfil - PawMap";
        echo $this->twig->render('perfil.html.twig', get_defined_vars());
    }
 
    private function cargarPerfilRefugio(array $user, array $errores = [], array $oldData = [], array $erroresMascota = [], array $oldMascota = []): void
    {
        $menu  = $this->menu;
        $redes = $this->redes;
        $request = $this->request;
        // Cargar modelo Refugio
        $refugioModel = $this->loadModel(Refugio::class);
        $refugioModel->load((int) $user['id']);
        $refugio = $refugioModel->fields;
        $refugioId = $user['id'] ?? null;
        $mascotas = [];
        $mascotasAdoptadas = [];
        $solicitudes = [];
        $tamanos=[];
        $especies=[];
        $temperamentos=[];
        $mascotaPublicada = false;
        
        $encuestas = [];
        $fotosSeguimiento = [];
        $seguimientoAgrupado = [];
       
        if ($refugioId) {
            $mascotaCollection = $this->loadCollection(MascotaCollection::class);
            $mascotas = $mascotaCollection->getByRefugioId((int) $refugioId);
            $mascotasAdoptadas = $mascotaCollection->getAll(['refugio_id' => $refugioId, 'estado_adopcion' => 'ADOPTADO']);

            $solicitudesCollection = $this->loadCollection(SolicitudAdopcionCollection::class);
            $solicitudes = $solicitudesCollection->getSolicitudesRefugio((int) $refugioId);

            $tamanos       = $mascotaCollection->getTamanos();
            $especies      = $mascotaCollection->getEspecies();
            $temperamentos = $mascotaCollection->getTemperamentos();
            
            $seguimientoAgrupado = $refugioModel->getSeguimientoAgrupado();
            
            $todasLasMascotas = $mascotaCollection->getAll(['refugio_id' => $refugioId]);
            $statsEspecie = [];
            $statsEstado = [];
            $statsTamano = [];
            $statsTemperamento = [];
            $statsCastrado = [];
            
            if (is_array($todasLasMascotas)) {
                foreach ($todasLasMascotas as $m) {
                    $estado = empty($m->fields['estado_adopcion']) ? 'Desconocido' : ucfirst(strtolower($m->fields['estado_adopcion']));
                    $especie = empty($m->fields['especie']) ? 'Desconocido' : ucfirst(strtolower($m->fields['especie']));
                    $tamano = empty($m->fields['tamano']) ? 'Desconocido' : ucfirst(strtolower($m->fields['tamano']));
                    $temperamento = empty($m->fields['temperamento']) ? 'Desconocido' : ucfirst(strtolower($m->fields['temperamento']));
                    
                    if (isset($m->fields['castrado'])) {
                        $castrado = ($m->fields['castrado'] == 1 || $m->fields['castrado'] === true || strtolower($m->fields['castrado']) == 'si') ? 'Sí' : 'No';
                    } else {
                        $castrado = 'Desconocido';
                    }
                    
                    $statsEstado[$estado] = ($statsEstado[$estado] ?? 0) + 1;
                    $statsEspecie[$especie] = ($statsEspecie[$especie] ?? 0) + 1;
                    $statsTamano[$tamano] = ($statsTamano[$tamano] ?? 0) + 1;
                    $statsTemperamento[$temperamento] = ($statsTemperamento[$temperamento] ?? 0) + 1;
                    $statsCastrado[$castrado] = ($statsCastrado[$castrado] ?? 0) + 1;
                }
            }

            $statsSolicitudes = [];
            if (is_array($solicitudes)) {
                foreach ($solicitudes as $sol) {
                    $estadoSol = empty($sol['estado']) ? 'Desconocido' : ucfirst(strtolower($sol['estado']));
                    $statsSolicitudes[$estadoSol] = ($statsSolicitudes[$estadoSol] ?? 0) + 1;
                }
            }
            
            $estadisticas = [
                'kpis' => [
                    'totalMascotas' => is_array($todasLasMascotas) ? count($todasLasMascotas) : 0,
                    'adopcionesExitosas' => $statsEstado['Adoptado'] ?? ($statsEstado['Adoptada'] ?? 0),
                    'totalSolicitudes' => is_array($solicitudes) ? count($solicitudes) : 0
                ],
                'especie' => $statsEspecie,
                'estado' => $statsEstado,
                'tamano' => $statsTamano,
                'temperamento' => $statsTemperamento,
                'castrado' => $statsCastrado,
                'solicitudes' => $statsSolicitudes
            ];
        }
        $resultados_importacion = $this->request->session('resultados_importacion');
        if ($resultados_importacion !== null) {
            $this->request->unsetSession('resultados_importacion');
        }
        
        $error_importacion = $this->request->session('error_importacion');
        if ($error_importacion !== null) {
            $this->request->unsetSession('error_importacion');
        }
        $mascotaPublicada = ($this->request->get('publicado') === '1');
        $titulo = "Mi Refugio - PawMap";
        echo $this->twig->render('perfil-refugio.html.twig', get_defined_vars());
    }


    public function guardarRefugio()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');

        if (empty($userSession) || $this->request->method() !== 'POST') {
            header('Location: /?auth=login');
            exit;
        }

        $user   = $userSession;
        $userId = (int) $user['id'];
        $errores = $this->model->actualizarPerfilRefugio(
            $userId, 
            $this->request->post(), 
            $this->request->file('foto_perfil_o_logo'),
            $user
        );

        if (!empty($errores)) {
            $this->cargarPerfilRefugio($user, $errores, $this->request->post());
            return;
        }

        $updatedUser = $this->model->findById($userId);
        if ($updatedUser) {
            if (array_key_exists('rol', $updatedUser)) unset($updatedUser['rol']);
            $userSession = array_merge($userSession, $updatedUser);
            $this->request->setSession('user', $userSession);
        }

        header("Location: /perfil?update=success");
        exit;
    }

    public function guardarUbicacion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');

        if (empty($userSession) || $this->request->method() !== 'POST') {
            header('Location: /?auth=login');
            exit;
        }

        $userId = (int) $userSession['id'];
        $postData = $this->request->post();

        $errores = $this->model->actualizarUbicacionRefugio($userId, $postData);

        if (!empty($errores)) {
            $this->cargarPerfilRefugio($userSession, $errores, $postData);
            return;
        }

        header("Location: /perfil?update=success#sec-ubicacion");
        exit;
    }

    public function guardarMascota()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');

        if (empty($userSession) || $this->request->method() !== 'POST'
            || ($userSession['rol'] ?? '') !== 'refugio') {
            header('Location: /?auth=login');
            exit;
        }

        $post  = $this->request->post();
        $foto  = $this->request->file('foto');
        $svg   = $this->request->file('svg');
        $userId = (int) $userSession['id'];

        $mascotaCollection = $this->loadCollection(MascotaCollection::class);
        $erroresMascota = $mascotaCollection->guardarMascotaIndividual($post, $foto, $svg, $userId);

        if (!empty($erroresMascota)) {
            $this->cargarPerfilRefugio($userSession, [], [], $erroresMascota, $post);
            return;
        }

        header('Location: /perfil?publicado=1#sec-publicar');
        exit;
    }


    public function guardar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');

        if (empty($userSession) || $this->request->method() !== 'POST') {
            header('Location: /?auth=login');
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
            if (array_key_exists('rol', $updatedUser)) unset($updatedUser['rol']);
            $userSession = array_merge($userSession, $updatedUser);
            $this->request->setSession('user', $userSession);
        }

        header("Location: /perfil?update=success");
        exit;
    }

    public function importarMascotasCsv()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userSession = $this->request->session('user');

        if (empty($userSession) || $this->request->method() !== 'POST'
            || ($userSession['rol'] ?? '') !== 'refugio') {
            header('Location: /?auth=login');
            exit;
        }

        $csv = $this->request->file('csv_mascotas');
        $userId = (int) $userSession['id'];

        if (!$csv || $csv['error'] !== UPLOAD_ERR_OK) {
            $this->request->setSession('error_importacion', 'falla_subida');
            header('Location: /perfil?importacion=error#sec-publicar');
            exit;
        }
        
        $extension = strtolower(pathinfo($csv['name'] ?? '', PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $this->request->setSession('error_importacion', 'extension_invalida');
            header('Location: /perfil?importacion=error#sec-publicar');
            exit;
        }

        $mascotaCollection = $this->loadCollection(MascotaCollection::class);
        
        $resultados = $mascotaCollection->importarMascotasCsv($csv['tmp_name'], $userId);

        $this->request->setSession('resultados_importacion', $resultados);

        header('Location: /perfil?importacion=finalizada#sec-publicar');
        exit;
    }
}
