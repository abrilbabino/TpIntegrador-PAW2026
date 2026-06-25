<?php
 
namespace Paw\App\Controllers;
 
use Paw\Core\Controller;
use Paw\App\Models\User;
use Paw\App\Models\Adoptante;
use Paw\App\Models\Refugio;
use Paw\App\Models\Favorito;
use Paw\App\Models\MascotaCollection;
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
            header('Location: /iniciar-sesion');
            exit;
        }

        $dbUser = $this->model->findById((int) $userSession['id']);
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
        echo $this->twig->render('perfil.html.twig', get_defined_vars());
    }
 
    private function cargarPerfilRefugio(array $user, array $errores = [], array $oldData = [], array $erroresMascota = [], array $oldMascota = []): void
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
            $mascotaCollection = new \Paw\App\Models\MascotaCollection();
            $mascotaCollection->setQueryBuilder($this->model->getQueryBuilder());
            $mascotas = $mascotaCollection->getByRefugioId((int) $refugioId);
            $mascotasAdoptadas = $mascotaCollection->getAll(['refugio_id' => $refugioId, 'estado_adopcion' => 'ADOPTADO']);

            $solicitudesCollection = new \Paw\App\Models\SolicitudAdopcionCollection();
            $solicitudesCollection->setQueryBuilder($this->model->getQueryBuilder());
            $solicitudes = $solicitudesCollection->getSolicitudesRefugio((int) $refugioId);

            $tamanos       = $mascotaCollection->getTamanos();
            $especies      = $mascotaCollection->getEspecies();
            $temperamentos = $mascotaCollection->getTemperamentos();
            
            $seguimientoAgrupado = $refugioModel->getSeguimientoAgrupado();
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
            header('Location: /iniciar-sesion');
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
            header('Location: /iniciar-sesion');
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
            header('Location: /iniciar-sesion');
            exit;
        }

        $post  = $this->request->post();
        $foto  = $this->request->file('foto');
        $svg   = $this->request->file('svg');
        $userId = (int) $userSession['id'];

        $erroresMascota = [];
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
        $nombre = trim($post['nombre'] ?? '');
        $largoNombre = function_exists('mb_strlen') ? mb_strlen($nombre) : strlen($nombre);
        if ($nombre === '') {
            $erroresMascota['nombre'] = 'El nombre es obligatorio.';
        } elseif ($largoNombre < 2 || $largoNombre > 60) {
            $erroresMascota['nombre'] = 'El nombre debe tener entre 2 y 60 caracteres.';
        } elseif (!preg_match("/^[\\p{L}\\s'-]+$/u", $nombre)) {
            $erroresMascota['nombre'] = 'Solo se permiten letras, espacios, apóstrofes y guiones.';
        }

        $especie = trim($post['especie'] ?? '');
        if ($especie === '') {
            $erroresMascota['especie'] = 'Debe seleccionar una especie.';
        } elseif (!in_array(strtolower($especie), $especiesPermitidas, true)) {
            $erroresMascota['especie'] = 'La especie seleccionada no es válida.';
        }

        $tamanio = trim($post['tamanio'] ?? '');
        if ($tamanio === '') {
            $erroresMascota['tamanio'] = 'Debe seleccionar un tamaño.';
        } elseif (!in_array(strtolower($tamanio), $tamanosPermitidos, true)) {
            $erroresMascota['tamanio'] = 'El tamaño seleccionado no es válido.';
        }

        $temperamento = trim($post['temperamento'] ?? '');
        if ($temperamento === '') {
            $erroresMascota['temperamento'] = 'Debe seleccionar un temperamento.';
        } elseif (!in_array(strtolower($temperamento), $temperamentosPermitidos, true)) {
            $erroresMascota['temperamento'] = 'El temperamento seleccionado no es válido.';
        }

        $sexo = trim($post['sexo'] ?? '');
        if (!in_array($sexo, ['macho', 'hembra'], true)) {
            $erroresMascota['sexo'] = 'Debe seleccionar un sexo válido.';
        }

        $esterilizado = trim($post['esterilizado'] ?? '');
        if (!in_array($esterilizado, ['si', 'no'], true)) {
            $erroresMascota['esterilizado'] = 'Debe indicar si la mascota está esterilizada.';
        }

        $descripcionMascota = trim($post['descripcion_mascota'] ?? '');
        $largoDescripcion = function_exists('mb_strlen') ? mb_strlen($descripcionMascota) : strlen($descripcionMascota);
        if ($largoDescripcion > 500) {
            $erroresMascota['descripcion_mascota'] = 'La descripción no puede superar 500 caracteres.';
        }

        // Fecha de nacimiento y cálculo de edad
        $fechaNac = trim($post['fecha_nacimiento'] ?? '');
        $edad = null;
        if (!empty($fechaNac)) {
            $d = \DateTime::createFromFormat('Y-m-d', $fechaNac);
            $minDate = (new \DateTime())->modify('-30 years');
            if (!$d || $d->format('Y-m-d') !== $fechaNac) {
                $erroresMascota['fecha_nacimiento'] = 'Fecha de nacimiento inválida.';
            } elseif ($d > new \DateTime()) {
                $erroresMascota['fecha_nacimiento'] = 'La fecha de nacimiento no puede ser futura.';
            } elseif ($d < $minDate) {
                $erroresMascota['fecha_nacimiento'] = 'La edad máxima permitida es de 30 años.';
            } else {
                $edad = (int) $d->diff(new \DateTime())->y;
            }
        }

        // Foto (opcional)
        $imagenRelativa = null;
        $fotoValidaParaMover = false;
        $extension = '';
        if ($foto && isset($foto['error']) && $foto['error'] === UPLOAD_ERR_OK) {
            $maxBytes = 2 * 1024 * 1024; // 2 MB
            if ($foto['size'] > $maxBytes) {
                $erroresMascota['foto'] = 'La imagen no puede superar 2 MB.';
            } else {
                $extension = strtolower(pathinfo($foto['name'] ?? '', PATHINFO_EXTENSION));
                $esImagenValida = false;
                if (file_exists($foto['tmp_name'])) {
                    $mime = mime_content_type($foto['tmp_name']);
                    $info = getimagesize($foto['tmp_name']);
                    if ($info !== false && strpos($mime, 'image/') === 0) {
                        $esImagenValida = true;
                    }
                }
                if (!$esImagenValida || !in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    $erroresMascota['foto'] = 'Archivo no válido. Solo JPG, PNG o WEBP.';
                } else {
                    $fotoValidaParaMover = true;
                }
            }
        } elseif ($foto && isset($foto['error']) && $foto['error'] !== UPLOAD_ERR_NO_FILE) {
            $erroresMascota['foto'] = 'Error al subir la imagen (código ' . $foto['error'] . ').';
        }

        // SVG (opcional)
        $svgRelativa = null;
        $svgValidoParaMover = false;
        if ($svg && isset($svg['error'])) {
            if ($svg['error'] === UPLOAD_ERR_OK) {
                $errorSvg = \Paw\App\Models\Mascota::validarArchivoSvg($svg);
                if ($errorSvg !== null) {
                    $erroresMascota['svg'] = $errorSvg;
                } else {
                    $svgValidoParaMover = true;
                }
            } elseif ($svg['error'] !== UPLOAD_ERR_NO_FILE) {
                $erroresMascota['svg'] = 'Error al subir el SVG (código ' . $svg['error'] . ').';
            }
        }

        // Si hay errores, re-renderizar el perfil con los datos ingresados
        if (!empty($erroresMascota)) {
            $this->cargarPerfilRefugio($userSession, [], [], $erroresMascota, $post);
            return;
        }

        if ($svgValidoParaMover) {
            try {
                $svgRelativa = GCSHelper::subir($svg, 'mascotas_svg');
            } catch (\Exception $e) {
                $erroresMascota['svg'] = 'No se pudo guardar el SVG: ' . $e->getMessage();
                $this->cargarPerfilRefugio($userSession, [], [], $erroresMascota, $post);
                return;
            }
        }

        if ($fotoValidaParaMover) {
            try {
                $imagenRelativa = GCSHelper::subir($foto, 'mascotas');
            } catch (\Exception $e) {
                $erroresMascota['foto'] = 'No se pudo guardar la imagen: ' . $e->getMessage();
                $this->cargarPerfilRefugio($userSession, [], [], $erroresMascota, $post);
                return;
            }
        }

        // Insertar en la BD
        $db = $this->model->getQueryBuilder();
        $db->insert('mascota', [
            'refugio_id'      => $userId,
            'nombre'          => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            'especie'         => htmlspecialchars($especie, ENT_QUOTES, 'UTF-8'),
            'descripcion'     => htmlspecialchars($descripcionMascota, ENT_QUOTES, 'UTF-8'),
            'edad'            => $edad,
            'tamano'          => htmlspecialchars($tamanio, ENT_QUOTES, 'UTF-8'),
            'sexo'            => htmlspecialchars($sexo, ENT_QUOTES, 'UTF-8'),
            'temperamento'    => htmlspecialchars($temperamento, ENT_QUOTES, 'UTF-8'),
            'castrado'        => ($esterilizado === 'si') ? 1 : 0,
            'vacunado'        => 0,
            'estado_adopcion' => 'DISPONIBLE',
            'imagen'          => $imagenRelativa ?? 'default-pet.jpg',
            'svg'             => $svgRelativa,
        ]);

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
            if (array_key_exists('rol', $updatedUser)) unset($updatedUser['rol']);
            $userSession = array_merge($userSession, $updatedUser);
            $this->request->setSession('user', $userSession);
        }

        header("Location: /perfil?update=success");
        exit;
    }
}
