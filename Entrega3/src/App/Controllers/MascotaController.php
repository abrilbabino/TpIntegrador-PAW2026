<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\MascotaCollection;
use Paw\App\Models\RefugioCollection; 
use Paw\App\Models\MediaMascotaCollection;
use Paw\App\Models\RegistroSanitarioCollection;
use Paw\Core\Exceptions\InvalidValueFormatException;
use Paw\Core\Exceptions\MascotaNotFoundException;


class MascotaController extends Controller
{
    public ?string $modelName = MascotaCollection::class;

    public function adoptar()
    {
        $request = $this->request;
        $menu    = $this->menu;
        $redes   = $this->redes;
        $metaDescription = "Conocé a los perros y gatos que esperan por un hogar. Filtrá por especie, tamaño y ubicación para encontrar a tu mascota ideal en PawMap.";

        require $this->viewsDir . '/adoptar.view.php';
    }

    public function apiMascotas() {
        header('Content-Type: application/json');
        
        $resultado = $this->model->getAll(['estado_adopcion' => 'DISPONIBLE']);
        
        $refugioCollection = $this->loadCollection(RefugioCollection::class);
        
        $favoritoModel = new \Paw\App\Models\Favorito();
        $favoritoModel->setQueryBuilder($this->model->getQueryBuilder());
        $favoritosIds = $favoritoModel->getFavoritosIds($this->request->session('user'));

        $mascotasData = [];
        foreach ($resultado as $mascota) {
            
            $refugio = $refugioCollection->get($mascota->fields['refugio_id']);

            $mascotasData[] = [
                'id'           => $mascota->fields['id'],
                'nombre'       => $mascota->fields['nombre'],
                'imagen'       => $mascota->fields['imagen'],
                'edad'         => $mascota->fields['edad'],
                'tamano'       => $mascota->fields['tamano'],
                'temperamento' => $mascota->fields['temperamento'],
                'especie'      => $mascota->fields['especie'],
                'refugio_id'   => $mascota->fields['refugio_id'],
                'provincia'    => $refugio->fields['provincia'] ?? null,
                'ciudad'       => $refugio->fields['ciudad'] ?? null,
                'es_favorito'  => in_array($mascota->fields['id'], $favoritosIds)
            ];
        }

        $response = [
            'success' => true,
            'data'    => $mascotasData
        ];
        http_response_code(200);
        echo json_encode($response);
        exit;
    }
    
    private function loadCollection($className){
        $model = new $className;
        $model->setQueryBuilder($this->model->getQueryBuilder());
        return $model;
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
        if ($mascota && $mascota->fields['refugio_id']) {
            $ubicaciones = $this->model->getQueryBuilder()->obtenerUbicacionesPorRefugio((int)$mascota->fields['refugio_id']);
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

        require $this->viewsDir . '/mascota.view.php';
    }

    public function editarForm()
    {
        $userSession = $this->request->session('user');
        if (empty($userSession) || ($userSession['rol'] ?? '') !== 'refugio') {
            header('Location: /iniciar-sesion');
            exit;
        }

        $id = (int) $this->request->get('id');
        if ($id <= 0) {
            header('Location: /perfil');
            exit;
        }

        try {
            $mascota = $this->model->get($id);
        } catch (MascotaNotFoundException | InvalidValueFormatException $e) {
            header('Location: /perfil');
            exit;
        }

        if (!$mascota || (int)$mascota->fields['refugio_id'] !== (int)$userSession['id']) {
            header('Location: /perfil');
            exit;
        }

        $mascotaCollection = new MascotaCollection();
        $mascotaCollection->setQueryBuilder($this->model->getQueryBuilder());
        $tamanos       = $mascotaCollection->getTamanos();
        $especies      = $mascotaCollection->getEspecies();
        $temperamentos = $mascotaCollection->getTemperamentos();

        $menu  = $this->menu;
        $redes = $this->redes;
        $errores  = [];
        $oldData  = [];
        require $this->viewsDir . '/editar-mascota.view.php';
    }

    public function editarGuardar()
    {
        $userSession = $this->request->session('user');
        if (empty($userSession) || $this->request->method() !== 'POST'
            || ($userSession['rol'] ?? '') !== 'refugio') {
            header('Location: /iniciar-sesion');
            exit;
        }

        $post  = $this->request->post();
        $foto  = $this->request->file('foto');
        $id    = (int) ($post['id'] ?? 0);

        if ($id <= 0) {
            header('Location: /perfil');
            exit;
        }

        try {
            $mascota = $this->model->get($id);
        } catch (MascotaNotFoundException | InvalidValueFormatException $e) {
            header('Location: /perfil');
            exit;
        }

        if (!$mascota || (int)$mascota->fields['refugio_id'] !== (int)$userSession['id']) {
            header('Location: /perfil');
            exit;
        }

        $errores = [];
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
            $errores['nombre'] = 'El nombre es obligatorio.';
        } elseif ($largoNombre < 2 || $largoNombre > 60) {
            $errores['nombre'] = 'El nombre debe tener entre 2 y 60 caracteres.';
        } elseif (!preg_match("/^[\\p{L}\\s'-]+$/u", $nombre)) {
            $errores['nombre'] = 'Solo se permiten letras, espacios, apóstrofes y guiones.';
        }

        $especie = trim($post['especie'] ?? '');
        if ($especie === '') {
            $errores['especie'] = 'Debe seleccionar una especie.';
        } elseif (!in_array(strtolower($especie), $especiesPermitidas, true)) {
            $errores['especie'] = 'La especie seleccionada no es válida.';
        }

        $tamanio = trim($post['tamanio'] ?? '');
        if ($tamanio === '') {
            $errores['tamanio'] = 'Debe seleccionar un tamaño.';
        } elseif (!in_array(strtolower($tamanio), $tamanosPermitidos, true)) {
            $errores['tamanio'] = 'El tamaño seleccionado no es válido.';
        }

        $temperamento = trim($post['temperamento'] ?? '');
        if ($temperamento === '') {
            $errores['temperamento'] = 'Debe seleccionar un temperamento.';
        } elseif (!in_array(strtolower($temperamento), $temperamentosPermitidos, true)) {
            $errores['temperamento'] = 'El temperamento seleccionado no es válido.';
        }

        $sexo = trim($post['sexo'] ?? '');
        if (!in_array($sexo, ['macho', 'hembra'], true)) {
            $errores['sexo'] = 'Debe seleccionar un sexo válido.';
        }

        $esterilizado = trim($post['esterilizado'] ?? '');
        if (!in_array($esterilizado, ['si', 'no'], true)) {
            $errores['esterilizado'] = 'Debe indicar si la mascota está esterilizada.';
        }

        $descripcionMascota = trim($post['descripcion_mascota'] ?? '');
        $largoDescripcion = function_exists('mb_strlen') ? mb_strlen($descripcionMascota) : strlen($descripcionMascota);
        if ($largoDescripcion > 500) {
            $errores['descripcion_mascota'] = 'La descripción no puede superar 500 caracteres.';
        }

        // Fecha de nacimiento y cálculo de edad
        $fechaNac = trim($post['fecha_nacimiento'] ?? '');
        $edad = null;
        if (!empty($fechaNac)) {
            $d = \DateTime::createFromFormat('Y-m-d', $fechaNac);
            if (!$d || $d->format('Y-m-d') !== $fechaNac) {
                $errores['fecha_nacimiento'] = 'Fecha de nacimiento inválida.';
            } elseif ($d > new \DateTime()) {
                $errores['fecha_nacimiento'] = 'La fecha de nacimiento no puede ser futura.';
            } else {
                $edad = (int) $d->diff(new \DateTime())->y;
            }
        } else {
            $edad = $mascota->fields['edad'];
        }

        // Foto (opcional)
        $imagenRelativa = $mascota->fields['imagen'];
        $fotoValidaParaMover = false;
        $extension = '';
        if ($foto && isset($foto['error']) && $foto['error'] === UPLOAD_ERR_OK) {
            $maxBytes = 2 * 1024 * 1024; // 2 MB
            if ($foto['size'] > $maxBytes) {
                $errores['foto'] = 'La imagen no puede superar 2 MB.';
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
                    $errores['foto'] = 'Archivo no válido. Solo JPG, PNG o WEBP.';
                } else {
                    $fotoValidaParaMover = true;
                }
            }
        } elseif ($foto && isset($foto['error']) && $foto['error'] !== UPLOAD_ERR_NO_FILE) {
            $errores['foto'] = 'Error al subir la imagen (código ' . $foto['error'] . ').';
        }

        if (!empty($errores)) {
            $tamanos       = $mascotaCollection->getTamanos();
            $especies      = $mascotaCollection->getEspecies();
            $temperamentos = $mascotaCollection->getTemperamentos();
            $menu  = $this->menu;
            $redes = $this->redes;
            $oldData = $post;
            require $this->viewsDir . '/editar-mascota.view.php';
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
                $errores['foto'] = 'No se pudo guardar la imagen.';
                $tamanos       = $mascotaCollection->getTamanos();
                $especies      = $mascotaCollection->getEspecies();
                $temperamentos = $mascotaCollection->getTemperamentos();
                $menu  = $this->menu;
                $redes = $this->redes;
                $oldData = $post;
                require $this->viewsDir . '/editar-mascota.view.php';
                return;
            }
        }

        $db = $this->model->getQueryBuilder();
        $db->update('mascota', [
            'nombre'       => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            'especie'      => htmlspecialchars($especie, ENT_QUOTES, 'UTF-8'),
            'descripcion'  => htmlspecialchars($descripcionMascota, ENT_QUOTES, 'UTF-8'),
            'edad'         => $edad,
            'tamano'       => htmlspecialchars($tamanio, ENT_QUOTES, 'UTF-8'),
            'sexo'         => htmlspecialchars($sexo, ENT_QUOTES, 'UTF-8'),
            'temperamento' => htmlspecialchars($temperamento, ENT_QUOTES, 'UTF-8'),
            'castrado'     => ($esterilizado === 'si') ? 1 : 0,
            'imagen'       => $imagenRelativa,
        ], ['id' => $id]);

        header('Location: /mascota/editar?id=' . $id . '&update=success');
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

        $coleccion = new RegistroSanitarioCollection();
        $coleccion->setQueryBuilder($this->model->getQueryBuilder());
        $registros = $coleccion->getByMascota((int)$id, $filtros);

        $proximos = [];
        $historial = [];
        $hoy = date('Y-m-d');

        $proximos = $coleccion->pendientes($registros,$hoy);
        $historial = $coleccion->completos($registros,$hoy);

        require $this->viewsDir . '/libreta.view.php';
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

        $coleccion = new RegistroSanitarioCollection();
        $coleccion->setQueryBuilder($this->model->getQueryBuilder());
        
        // Obtener todos los registros sin filtro
        $registros = $coleccion->getByMascota((int)$mascota_id, []);

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
                'icono'            => $registro->getIconoHtml()
            ];
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data'    => $datos
        ]);
        exit;
    }

    public function guardarRegistro()
    {
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

        $coleccion = new RegistroSanitarioCollection();
        $coleccion->setQueryBuilder($this->model->getQueryBuilder());

        $data = [
            'mascota_id' => $mascota_id,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'fecha_programada' => $fecha_programada,
            'estado' => 'PENDIENTE',
            'observaciones' => $observaciones !== '' ? $observaciones : null,
        ];

        $coleccion->getQueryBuilder()->insert('registro_sanitario', $data);

        header('Location: /mascota/libreta?id=' . $mascota_id);
    }

    public function completarRegistro()
    {
        $datos = $this->request->post();

        $registro_id = (int) ($datos['registro_id'] ?? 0);
        $mascota_id  = (int) ($datos['mascota_id']  ?? 0);

        // Validación estricta: ambos IDs deben ser enteros positivos
        if ($registro_id <= 0 || $mascota_id <= 0) {
            header('Location: /mascota/libreta?id=' . $mascota_id);
            return;
        }

        $this->model->getQueryBuilder()->update(
            'registro_sanitario',
            [
                'estado'          => 'COMPLETADO',
                'fecha_realizada' => date('Y-m-d'),
            ],
            ['id' => $registro_id]
        );

        header('Location: /mascota/libreta?id=' . $mascota_id);
    }
}
