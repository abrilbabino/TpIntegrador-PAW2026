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
        $solicitudes = [];
        $tamanos=[];
        $especies=[];
        $temperamentos=[];
        $mascotaPublicada = false;
       
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
        $mascotaPublicada = ($this->request->get('publicado') === '1');
        $titulo = "Mi Refugio - PawMap";
        require $this->viewsDir . '/perfil-refugio.view.php';
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
            $user
        );

        if (!empty($errores)) {
            $this->cargarPerfilRefugio($user, $errores, $this->request->post());
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
            if (!$d || $d->format('Y-m-d') !== $fechaNac) {
                $erroresMascota['fecha_nacimiento'] = 'Fecha de nacimiento inválida.';
            } elseif ($d > new \DateTime()) {
                $erroresMascota['fecha_nacimiento'] = 'La fecha de nacimiento no puede ser futura.';
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

        // Si hay errores, re-renderizar el perfil con los datos ingresados
        if (!empty($erroresMascota)) {
            $this->cargarPerfilRefugio($userSession, [], [], $erroresMascota, $post);
            return;
        }

        if ($fotoValidaParaMover) {
            $nombreFinal = uniqid('mascota_', true) . '.' . $extension;
            $directorio  = __DIR__ . '/../../../public/assets/img/uploads/';
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }
            if (move_uploaded_file($foto['tmp_name'], $directorio . $nombreFinal)) {
                $imagenRelativa = 'uploads/' . $nombreFinal;
            } else {
                $erroresMascota['foto'] = 'No se pudo guardar la imagen.';
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
            $userSession = array_merge($userSession, $updatedUser);
            $this->request->setSession('user', $userSession);
        }

        header("Location: /perfil?update=success");
        exit;
    }
}
