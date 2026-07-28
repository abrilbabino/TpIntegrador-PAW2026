<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\Mascota;
use Paw\App\Models\MascotaCollection;
use Paw\App\Models\RefugioCollection; 
use Paw\App\Models\MediaMascotaCollection;
use Paw\App\Models\RegistroSanitarioCollection;
use Paw\Core\Exceptions\InvalidValueFormatException;
use Paw\Core\Exceptions\ModelNotFoundException;
use Paw\App\Helpers\GCSHelper;


class MascotaController extends Controller
{
    public ?string $modelName = MascotaCollection::class;

    public function adoptar()
    {
        $request = $this->request;
        $menu    = $this->menu;
        $redes   = $this->redes;
        $metaDescription = "Conocé a los perros y gatos que esperan por un hogar. Filtrá por especie, tamaño y ubicación para encontrar a tu mascota ideal en PawMap.";

        echo $this->twig->render('adoptar.html.twig', get_defined_vars());
    }

    public function apiMascotas() {
        header('Content-Type: application/json');
        
        // Liberar el bloqueo de sesión para permitir requests concurrentes sin que php -S colapse
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        
        $favoritoModel = new \Paw\App\Models\Favorito();
        $favoritoModel->setQueryBuilder($this->model->getQueryBuilder());
        $favoritosIds = $favoritoModel->getFavoritosIds($this->request->session('user'));

        $mascotasData = $this->model->obtenerMascotasApiData($favoritosIds);

        $response = [
            'success' => true,
            'data'    => $mascotasData
        ];
        http_response_code(200);
        echo json_encode($response);
        exit;
    }

    private function loadFotosMascota(int $mascotaId, ?string $imagen): array
    {
        $mediaCol = new MediaMascotaCollection();
        $mediaCol->setQueryBuilder($this->model->getQueryBuilder());
        return $mediaCol->getMultimedia($mascotaId, $imagen);
    }

    public function detalle()
    {
        $request = $this->request;
        $menu  = $this->menu;
        $redes = $this->redes;
        $id    = $request->get('id');

        $mascota = $this->model->get($id);
        
        $nombre = htmlspecialchars($mascota->fields['nombre'] ?? '');
        $especie = htmlspecialchars(strtolower($mascota->fields['especie'] ?? 'mascota'));
        $metaDescription = "Conocé a {$nombre}, un {$especie} en adopción. Descubrí su historia y si es tu compañero ideal en PawMap.";

        $refugios = new RefugioCollection();
        $refugios->setQueryBuilder($this->model->getQueryBuilder());
        $refugio =$refugios->get($mascota->fields['refugio_id']);

        
        $ubicaciones = [];
        $ubicacionTexto = 'Ubicación a confirmar';
        if ($mascota && $mascota->fields['refugio_id']) {
            $ubicaciones = $refugios->obtenerUbicaciones((int)$mascota->fields['refugio_id']);
            $ciudades = [];
            $provincias = [];
            foreach ($ubicaciones as $u) {
                if (!empty($u['ciudad'])) $ciudades[] = $u['ciudad'];
                if (!empty($u['provincia'])) $provincias[] = $u['provincia'];
            }
            $ciudades = array_unique($ciudades);
            $provincias = array_unique($provincias);
            $ciudadStr = implode(', ', $ciudades);
            $provStr = implode(', ', $provincias);
            $uTexto = trim(($ciudadStr ? $ciudadStr . ', ' : '') . $provStr, ', ');
            if ($uTexto !== '') {
                $ubicacionTexto = $uTexto;
            }
        }

        $mediaCol = new MediaMascotaCollection();
        $mediaCol->setQueryBuilder($this->model->getQueryBuilder());
        $mediaExtras = $mediaCol->getMultimedia(
            (int)$mascota->fields['id'],
            $mascota->fields['imagen'] ?? null
        );

        $favoritoModel = new \Paw\App\Models\Favorito();
        $favoritoModel->setQueryBuilder($this->model->getQueryBuilder());
        $favoritosIds = $favoritoModel->getFavoritosIds($this->request->session('user'));
        $esFavorito = in_array($id, $favoritosIds);

        echo $this->twig->render('mascota.html.twig', get_defined_vars());
    }

    public function editarForm()
    {
        $userSession = $this->request->session('user');
        if (empty($userSession) || ($userSession['rol'] ?? '') !== 'refugio') {
            header('Location: /?auth=login');
            exit;
        }

        $id = (int) $this->request->get('id');
        if ($id <= 0) {
            header('Location: /perfil');
            exit;
        }

        try {
            $mascota = $this->model->get($id);
        } catch (ModelNotFoundException | InvalidValueFormatException $e) {
            header('Location: /perfil');
            exit;
        }

        if (!$mascota || (int)$mascota->fields['refugio_id'] !== (int)$userSession['id']) {
            header('Location: /perfil');
            exit;
        }

        $mascotaFields = $mascota->fields;
        $nombreActual  = $mascotaFields['nombre'] ?? '';
        $especieActual = $mascotaFields['especie'] ?? '';
        $tamanioActual = $mascotaFields['tamano'] ?? '';
        $temperamentoActual = $mascotaFields['temperamento'] ?? '';
        $sexoActual    = $mascotaFields['sexo'] ?? '';
        $esterilizadoActual = ($mascotaFields['castrado'] == 1) ? 'si' : 'no';
        $descripcionActual  = $mascotaFields['descripcion'] ?? '';
        $fechaNacimientoActual = $mascotaFields['fecha_nacimiento'] ?? '';
        $mascotaCollection = new MascotaCollection();
        $mascotaCollection->setQueryBuilder($this->model->getQueryBuilder());
        $tamanos       = $mascotaCollection->getTamanos();
        $especies      = $mascotaCollection->getEspecies();
        $temperamentos = $mascotaCollection->getTemperamentos();

        $fotos = $this->loadFotosMascota((int)$mascotaFields['id'], $mascotaFields['imagen'] ?? null);

        $menu  = $this->menu;
        $redes = $this->redes;
        $errores  = [];
        $oldData  = [];
        echo $this->twig->render('editar-mascota.html.twig', get_defined_vars());
    }

    public function editarGuardar()
    {
        $userSession = $this->request->session('user');
        if (empty($userSession) || $this->request->method() !== 'POST'
            || ($userSession['rol'] ?? '') !== 'refugio') {
            header('Location: /?auth=login');
            exit;
        }

        $post  = $this->request->post();
        $foto  = $this->request->file('foto');
        $svg   = $this->request->file('svg');
        $id    = (int) ($post['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /perfil');
            exit;
        }

        try {
            $mascota = $this->model->get($id);
        } catch (ModelNotFoundException | InvalidValueFormatException $e) {
            header('Location: /perfil');
            exit;
        }

        if (!$mascota || (int)$mascota->fields['refugio_id'] !== (int)$userSession['id']) {
            header('Location: /perfil');
            exit;
        }

        $mascotaCollection = new MascotaCollection();
        $mascotaCollection->setQueryBuilder($this->model->getQueryBuilder());
        $valoresDeCampo = static function (array $items, string $campo): array {
            return array_map(
                static fn ($item) => strtolower((string) ($item->fields[$campo] ?? '')),
                $items
            );
        };
        $especiesPermitidas = $valoresDeCampo($mascotaCollection->getEspecies(), 'especie');
        $tamanosPermitidos = $valoresDeCampo($mascotaCollection->getTamanos(), 'tamano');
        $temperamentosPermitidos = $valoresDeCampo($mascotaCollection->getTemperamentos(), 'temperamento');
        
        // --- Validaciones ---
        $opcionesPermitidas = [
            'especies' => $especiesPermitidas,
            'tamanos' => $tamanosPermitidos,
            'temperamentos' => $temperamentosPermitidos,
        ];

        $errores = Mascota::validarEdicion($post, $opcionesPermitidas);

        $nombre = trim($post['nombre'] ?? '');
        $especie = trim($post['especie'] ?? '');
        $tamanio = trim($post['tamanio'] ?? '');
        $temperamento = trim($post['temperamento'] ?? '');
        $sexo = trim($post['sexo'] ?? '');
        $esterilizado = trim($post['esterilizado'] ?? '');
        $descripcionMascota = trim($post['descripcion_mascota'] ?? '');
        $fechaNac = trim($post['fecha_nacimiento'] ?? '');

        if (empty($errores)) {
            $archivos = [
                'foto' => $this->request->file('foto'),
                'svg' => $this->request->file('svg'),
                'archivo_multimedia' => $this->request->file('archivo_multimedia'),
            ];
            $mascotaCollection->actualizarMascotaConArchivos($id, $post, $archivos);
        } else {
            $tamanos       = $mascotaCollection->getTamanos();
            $especies      = $mascotaCollection->getEspecies();
            $temperamentos = $mascotaCollection->getTemperamentos();
            $menu  = $this->menu;
            $redes = $this->redes;
            $oldData = $post;
            $fotos = $this->loadFotosMascota((int)$mascota->fields['id'], $mascota->fields['imagen'] ?? null);
            echo $this->twig->render('editar-mascota.html.twig', get_defined_vars());
            return;
        }

        header('Location: /mascota/editar?id=' . $id . '&update=success');
        exit;
    }

    public function subirArchivoMascota() {
        $userSession = $this->request->session('user');

        // 1. Validar sesión y permisos
        if (empty($userSession) || ($userSession['rol'] ?? '') !== 'refugio' || $this->request->method() !== 'POST') {
            header('Location: /?auth=login');
            exit;
        }

        $mascotaId = filter_input(INPUT_POST, 'mascota_id', FILTER_VALIDATE_INT);
        if ($mascotaId === false || $mascotaId === null) {
            $postData = $this->request->post();
            $mascotaId = (int) ($postData['mascota_id'] ?? 0);
        }

        if ($mascotaId <= 0) {
            error_log("ERROR: Intento de subir foto con ID inválido. POST data: " . json_encode($_POST));
            header('Location: /perfil?error=id_invalido');
            exit;
        }

        $archivo = $this->request->file('archivo_multimedia');
        if (!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
            header('Location: /mascota/editar?id=' . $mascotaId . '&error=archivo_invalido');
            exit;
        }

        // 2. Verificar propiedad de la mascota
        try {
            $mascota = $this->model->get($mascotaId);
        } catch (ModelNotFoundException | InvalidValueFormatException $e) {
            header('Location: /perfil');
            exit;
        }

        if (!$mascota || (int)$mascota->fields['refugio_id'] !== (int)$userSession['id']) {
            header('Location: /perfil');
            exit;
        }

        $mediaCollection = $this->loadCollection('\Paw\App\Models\MediaMascotaCollection');

        // 3. Procesar el archivo
        try {
            $url = GCSHelper::subir($archivo, 'media_mascotas');
            $insertId = $mediaCollection->agregarMedia(
                $mascotaId,
                $esVideo ? 'video' : 'foto',
                $url
            );
            
            $wantsJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            if ($wantsJson) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'id' => $insertId,
                    'url' => str_starts_with($url, 'http') ? $url : '/' . ltrim($url, '/')
                ]);
                exit;
            }
            
            header('Location: /mascota/editar?id=' . $mascotaId . '&upload=success');
        } catch (\Exception $e) {
            error_log("Error subirArchivoMascota: " . $e->getMessage());
            
            $wantsJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            if ($wantsJson) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'upload_failed']);
                exit;
            }
            
            header('Location: /mascota/editar?id=' . $mascotaId . '&error=upload_failed');
        }
        exit;
    }

    public function eliminarSvg() {
        $userSession = $this->request->session('user');
        if (empty($userSession) || ($userSession['rol'] ?? '') !== 'refugio' || $this->request->method() !== 'POST') {
            header('Location: /?auth=login');
            exit;
        }

        $postData = $this->request->post();
        $id = (int) ($postData['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /perfil');
            exit;
        }

        try {
            $mascota = $this->model->get($id);
        } catch (ModelNotFoundException | InvalidValueFormatException $e) {
            header('Location: /perfil');
            exit;
        }

        if (!$mascota || (int)$mascota->fields['refugio_id'] !== (int)$userSession['id']) {
            header('Location: /perfil');
            exit;
        }

        $mascotaCollection = new MascotaCollection();
        $mascotaCollection->setQueryBuilder($this->model->getQueryBuilder());
        $mascotaCollection->eliminarSvg($id);

        header('Location: /mascota/editar?id=' . $id . '&update=success');
        exit;
    }

    public function eliminarFotoPrincipal() {
        $userSession = $this->request->session('user');
        if (empty($userSession) || ($userSession['rol'] ?? '') !== 'refugio' || $this->request->method() !== 'POST') {
            header('Location: /?auth=login');
            exit;
        }

        $postData = $this->request->post();
        $id = (int) ($postData['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /perfil');
            exit;
        }

        try {
            $mascota = $this->model->get($id);
        } catch (ModelNotFoundException | InvalidValueFormatException $e) {
            header('Location: /perfil');
            exit;
        }

        if (!$mascota || (int)$mascota->fields['refugio_id'] !== (int)$userSession['id']) {
            header('Location: /perfil');
            exit;
        }

        $mascotaCollection = new MascotaCollection();
        $mascotaCollection->setQueryBuilder($this->model->getQueryBuilder());
        $mascotaCollection->eliminarFotoPrincipal($id);

        header('Location: /mascota/editar?id=' . $id . '&update=success');
        exit;
    }

public function eliminarFoto() {
    $userSession = $this->request->session('user');
    if (empty($userSession) || ($userSession['rol'] ?? '') !== 'refugio' || $this->request->method() !== 'POST') {
        header('Location: /?auth=login');
        exit;
    }

    $postData = $this->request->post();
    $mediaId = (int) ($postData['id'] ?? 0);
    $mascotaId = (int) ($postData['mascota_id'] ?? 0);

    if ($mediaId <= 0 || $mascotaId <= 0) {
        header('Location: /perfil');
        exit;
    }

    try {
        $mascota = $this->model->get($mascotaId);
    } catch (ModelNotFoundException | InvalidValueFormatException $e) {
        header('Location: /perfil');
        exit;
    }

    if (!$mascota || (int)$mascota->fields['refugio_id'] !== (int)$userSession['id']) {
        header('Location: /perfil');
        exit;
    }

    try {
        $mediaCollection = new MediaMascotaCollection();
        $mediaCollection->setQueryBuilder($this->model->getQueryBuilder());
        $mediaCollection->eliminarFotoAdicional($mediaId, $mascotaId);
    } catch (\Exception $e) {
        header('Location: /mascota/editar?id=' . $mascotaId . '&error=foto_no_encontrada');
        exit;
    }
    header('Location: /mascota/editar?id=' . $mascotaId . '&delete=success');
    exit;
}

    public function libreta()
    {
        $request = $this->request;
        $menu  = $this->menu;
        $redes = $this->redes;
        $id = $request->get('id');

        $mascota = $this->model->get($id);

        $filtros = [
            'anio' => $request->get('anio'),
            'mes' => $request->get('mes'),
            'categoria' => $request->get('categoria'),
        ];

        $coleccion = $this->loadCollection(RegistroSanitarioCollection::class);
        $registros = $coleccion->getByMascota((int)$id, $filtros);

        $proximos = [];
        $historial = [];
        $hoy = date('Y-m-d');

        $proximos = $coleccion->pendientes($registros,$hoy);
        $historial = $coleccion->completos($registros,$hoy);

        $userSession = $this->request->session('user');
        $puedeModificar = false;
        $puedeAgregar = false;
        if (!empty($userSession)) {
            $mascotasCol = $this->loadCollection(MascotaCollection::class);
            $permisos = $mascotasCol->obtenerPermisosLibreta((int)$id, $userSession['id'], $userSession['rol'] ?? '');
            
            $puedeModificar = $permisos['puedeModificar'];
            $puedeAgregar = $permisos['puedeAgregar'];
        }

        echo $this->twig->render('libreta.html.twig', get_defined_vars());
    }

    public function apiLibreta()
    {
        header('Content-Type: application/json');

        $mascota_id = $this->request->get('mascota_id');
        if (!$mascota_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Falta mascota_id']);
            exit;
        }

        $coleccion = $this->loadCollection(RegistroSanitarioCollection::class);
        
        // Obtener todos los registros sin filtro
        $registros = $coleccion->getByMascota((int)$mascota_id, []);

        $userSession = $this->request->session('user');
        $puedeModificar = false;
        $puedeAgregar = false;
        if (!empty($userSession)) {
            $mascotasCol = $this->loadCollection(MascotaCollection::class);
            $permisos = $mascotasCol->obtenerPermisosLibreta((int)$mascota_id, $userSession['id'], $userSession['rol'] ?? '');
            
            $puedeModificar = $permisos['puedeModificar'];
            $puedeAgregar = $permisos['puedeAgregar'];
        }

        $datos = [];
        foreach ($registros as $registro) {
            $fechaAUsar = $registro->fields['fecha_realizada'] ?? $registro->fields['fecha_programada'] ?? '';
            $anio = '';
            $mes = '';
            if ($fechaAUsar) {
                $time = strtotime($fechaAUsar);
                $anio = date('Y', $time);
                $mes = date('n', $time);
            }
            $datos[] = [
                'id'               => $registro->fields['id'],
                'mascota_id'       => $registro->fields['mascota_id'],
                'tipo'             => $registro->fields['tipo'],
                'categoria'        => $registro->fields['tipo'], // alias for frontend
                'titulo'           => $registro->fields['titulo'],
                'fecha_programada' => $registro->fields['fecha_programada'],
                'fecha_realizada'  => $registro->fields['fecha_realizada'],
                'estado'           => $registro->fields['estado'],
                'observaciones'    => $registro->fields['observaciones'],
                'anio'             => $anio,
                'mes'              => $mes,
                'icono'            => $registro->getIcono()
            ];
        }

        http_response_code(200);
        echo json_encode([
            'success'        => true,
            'data'           => $datos,
            'puedeModificar' => $puedeModificar,
            'puedeAgregar'   => $puedeAgregar
        ]);
        exit;
    }

    public function guardarRegistro()
    {
        $userSession = $this->request->session('user');
        if (empty($userSession)) {
            header('Location: /?auth=login');
            exit;
        }

        $datos = $this->request->post();

        $mascota_id = (int) ($datos['mascota_id'] ?? 0);
        $tipo = trim($datos['tipo'] ?? '');
        $titulo = trim($datos['titulo'] ?? '');
        $fecha_programada = $datos['fecha_programada'] ?? '';
        $observaciones = trim($datos['observaciones'] ?? '');

        // Validación estricta de campos requeridos
        if ($mascota_id <= 0 || $tipo === '' || $titulo === '' || $fecha_programada === '') {
            header('Location: /mascota/libreta?id=' . $mascota_id);
            return;
        }

        if ($fecha_programada < date('Y-m-d')) {
            header('Location: /mascota/libreta?id=' . $mascota_id . '&error=fecha_invalida');
            return;
        }

        $coleccion = $this->loadCollection(MascotaCollection::class);
        $rol = $userSession['rol'] ?? '';
        $permisos = $coleccion->obtenerPermisosLibreta($mascota_id, $userSession['id'], $rol);
        if (!$permisos['puedeAgregar']) {
            header('Location: /mascota/libreta?id=' . $mascota_id . '&error=permisos_denegados');
            return;
        }

        $regSanitario = $this->loadCollection(RegistroSanitarioCollection::class);

        $data = [
            'mascota_id' => $mascota_id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'fecha_programada' => $fecha_programada,
            'estado' => 'PENDIENTE',
            'observaciones' => $observaciones !== '' ? $observaciones : null,
        ];

        $regSanitario->crearRegistroSanitario($data);

        header('Location: /mascota/libreta?id=' . $mascota_id);
    }

    public function completarRegistro()
    {
        $userSession = $this->request->session('user');
        if (empty($userSession)) {
            header('Location: /?auth=login');
            exit;
        }

        $datos = $this->request->post();

        $registro_id = (int) ($datos['registro_id'] ?? 0);
        $mascota_id  = (int) ($datos['mascota_id']  ?? 0);

        // Validación estricta: ambos IDs deben ser enteros positivos
        if ($registro_id <= 0 || $mascota_id <= 0) {
            header('Location: /mascota/libreta?id=' . $mascota_id);
            return;
        }

        $coleccion = $this->loadCollection(MascotaCollection::class);
        $permisos = $coleccion->obtenerPermisosLibreta($mascota_id, $userSession['id'], $userSession['rol'] ?? '');
        if (!$permisos['puedeModificar']) {
            header('Location: /mascota/libreta?id=' . $mascota_id . '&error=permisos_denegados');
            return;
        }

        $archivo = $this->request->file('archivo');

        if (!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
            $errCode = $archivo ? $archivo['error'] : 'no_file';
            header('Location: /mascota/libreta?id=' . $mascota_id . '&error=error_carga&registro_id=' . $registro_id . '&motivo=' . $errCode);
            return;
        }

        try {
            $url = GCSHelper::subir($archivo, 'libreta_mascotas');
            $regSanitario = $this->loadCollection(RegistroSanitarioCollection::class);
            $regSanitario->completarRegistroSanitario($registro_id, $url, date('Y-m-d'));
        } catch (\Exception $e) {
            $motivo = 'gcs_upload_failed';

            header('Location: /mascota/libreta?id=' . $mascota_id . '&error=error_carga&registro_id=' . $registro_id . '&motivo=' . $motivo);
            return;
        }

        header('Location: /mascota/libreta?id=' . $mascota_id);
    }

    public function eliminar()
    {
        $postData = $this->request->post();
        $id = (int) ($postData['id'] ?? 0);
        $userSession = $this->request->session('user');

        if (empty($userSession) || ($userSession['rol'] ?? '') !== 'refugio' || $id <= 0) {
            header('Location: /?auth=login');
            exit;
        }

        try {
            $mascota = $this->model->get($id);
        } catch (ModelNotFoundException | InvalidValueFormatException $e) {
            header('Location: /perfil');
            exit;
        }

        if ($mascota && (int)$mascota->fields['refugio_id'] === (int) $userSession['id']) {
            $mascotaCollection = $this->loadCollection('\Paw\App\Models\MascotaCollection');
            $mascotaCollection->eliminar($id);
        }

        header('Location: /perfil?deleted=1#sec-editar-mascota');
        exit;
    }
}

