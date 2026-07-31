<?php

namespace Paw\App\Models;

use Paw\Core\Model;

use Paw\App\Models\Mascota;
use Paw\App\Models\DiccionarioCollection;
use Paw\App\Helpers\GCSHelper;

class MascotaCollection extends Model
{
    public string $table = 'mascota';

    private array $camposPermitidosParaFiltro = ['tamano', 'especie', 'temperamento'];

    public function getAll(array $filtros = []): array
    {
        $mascotas = $this->queryBuilder->select($this->table, $filtros);
        return $this->mapMascotas($mascotas);
    }

    public function buscarCompatibles(array $filtros): array
    {
        $resultadosDB = $this->queryBuilder->selectCompatibles($this->table, $filtros);
        return $this->mapMascotas($resultadosDB);
    }

    public function getFiltered(array $filtros): array
    {
        $resultados = $this->queryBuilder->obtenerMascotasFiltradas($filtros);
        return $this->mapMascotas($resultados);
    }

    public function get($id)
    {
        $mascota = new Mascota;
        $mascota->setQueryBuilder($this->queryBuilder);
        $mascota->load($id);
        return $mascota;    
    }

    public function getTamanos(): array { 
        $d = new DiccionarioCollection(); 
        $d->setQueryBuilder($this->queryBuilder); 
        return $d->obtenerTodos('tamano'); 
    }
    public function getEspecies(): array { 
        $d = new DiccionarioCollection(); 
        $d->setQueryBuilder($this->queryBuilder); 
        return $d->obtenerTodos('especie'); 
    }
    public function getTemperamentos(): array { 
        $d = new DiccionarioCollection(); 
        $d->setQueryBuilder($this->queryBuilder); 
        return $d->obtenerTodos('temperamento'); 
    }
    
    public function getProvincias(): array { return $this->mapearCampoMascota($this->queryBuilder->obtenerUbicacionUnicaRefugio('refugio', 'provincia'), 'provincia'); }
    public function getCiudades(): array { return $this->mapearCampoMascota($this->queryBuilder->obtenerUbicacionUnicaRefugio('refugio', 'ciudad'), 'ciudad'); }

    public function buscar(string $termino): array
    {
        $resultados = $this->queryBuilder->buscarMascotasPorTermino($this->table, $termino);
        return $this->mapMascotas($resultados);
    }

    public function getAdopcionesByAdoptante(int $adoptanteId): array
    {
        $resultados = $this->queryBuilder->obtenerAdopcionesPorAdoptante('solicitud_de_adopcion', $adoptanteId);
        return $this->mapMascotas($resultados);
    }

    public function buscarPaginated(string $termino, int $pagina, int $porPagina = 6): array
    {
        $total = $this->queryBuilder->buscarMascotasPorTermino($this->table, $termino, true); 

        $paginacion = new Pagination($pagina, $porPagina, $total);

        $resultados = $this->queryBuilder->buscarMascotasPorTermino($this->table, $termino, false, $paginacion->perPage, $paginacion->offset);

        return [
            'items' => $this->mapMascotas($resultados),
            'pagination' => $paginacion,
        ];
    }

    public function count(array $filtros = []): int
    {
        return $this->queryBuilder->obtenerMascotasFiltradas($filtros, true);
    }

    public function getPaginated(array $filtros, int $pagina, int $porPagina = 6): array
    {
        $total = $this->count($filtros);
        $paginacion = new Pagination($pagina, $porPagina, $total);
        
        $mascotas = $this->queryBuilder->obtenerMascotasFiltradas($filtros, false, $paginacion->perPage, $paginacion->offset);

        return [
            'items' => $this->mapMascotas($mascotas), 'pagination' => $paginacion,
        ];
    }

    private function mapearCampoMascota(array $filas, string $campo): array
    {
        $mascotas = [];
        foreach ($filas as $fila) {
            $mascota = new Mascota();
            $mascota->fields[$campo] = $fila[$campo];
            $mascotas[] = $mascota;
        }
        return $mascotas;
    }

    private function mapMascotas(array $filas): array
    {
        $coleccion = [];
        foreach ($filas as $fila) {
            $mascota = new Mascota();
            $mascota->set($fila);
            $coleccion[] = $mascota;
        }
        return $coleccion;
    }
    
    public function getByRefugioId(int $refugioId): array
    {
        $mascotas = $this->queryBuilder->selectByRefugioId($this->table, $refugioId);
        return $this->mapMascotas($mascotas);
    }

    public function verificarPermisosLibreta(int $mascotaId, int $usuarioId, string $rol): bool
    {
        $mascota = $this->get($mascotaId);
        
        if (!$mascota || empty($mascota->fields['id'])) {
            return false;
        }

        $estadoAdopcion = $mascota->fields['estado_adopcion'] ?? 'DISPONIBLE';

        if ($estadoAdopcion === 'ADOPTADO') {
            if ($rol === 'refugio') {
                return (int)$mascota->fields['refugio_id'] === $usuarioId;
            }
            if ($rol !== 'adoptante') {
                return false;
            }
            
            // Verificar si este usuario tiene la solicitud aprobada (APROBADO o APROBADA)
            return $this->queryBuilder->exists('solicitud_de_adopcion', [
                'mascota_id' => $mascotaId,
                'adoptante_id' => $usuarioId,
                'estado' => 'APROBADO'
            ]) || $this->queryBuilder->exists('solicitud_de_adopcion', [
                'mascota_id' => $mascotaId,
                'adoptante_id' => $usuarioId,
                'estado' => 'APROBADA'
            ]);
        } else {
            if ($rol !== 'refugio') {
                return false;
            }
            return (int)$mascota->fields['refugio_id'] === $usuarioId;
        }
    }

    public function obtenerPermisosLibreta(int $mascotaId, int $usuarioId, string $rol): array
    {
        $permisos = [
            'puedeModificar' => false,
            'puedeAgregar'   => false
        ];

        if (!$this->verificarPermisosLibreta($mascotaId, $usuarioId, $rol)) {
            return $permisos;
        }

        $mascota = $this->get($mascotaId);
        $estadoAdopcion = $mascota->fields['estado_adopcion'] ?? 'DISPONIBLE';

        if ($estadoAdopcion === 'ADOPTADO') {
            if ($rol === 'adoptante') {
                $permisos['puedeModificar'] = true;
                $permisos['puedeAgregar'] = false;
            } elseif ($rol === 'refugio') {
                $permisos['puedeModificar'] = false;
                $permisos['puedeAgregar'] = true;
            }
        } else {
            if ($rol === 'refugio') {
                $permisos['puedeModificar'] = true;
                $permisos['puedeAgregar'] = true;
            }
        }

        return $permisos;
    }

    public function obtenerMascotasApiData(array $favoritosIds = []): array
    {
        $rows = $this->queryBuilder->obtenerMascotasDisponiblesConUbicacion();

        $mascotasData = [];
        foreach ($rows as $row) {
            $mascotasData[] = [
                'id'           => $row['id'],
                'nombre'       => $row['nombre'],
                'imagen'       => $row['imagen'],
                'edad'         => $row['edad'],
                'tamano'       => $row['tamano'],
                'temperamento' => $row['temperamento'],
                'especie'      => $row['especie'],
                'refugio_id'   => $row['refugio_id'],
                'provincia'    => $row['provincia'],
                'ciudad'       => $row['ciudad'],
                'es_favorito'  => in_array($row['id'], $favoritosIds)
            ];
        }

        return $mascotasData;
    }

    public function getAdoptanteDeMascota(int $mascotaId): ?array
    {
        return $this->queryBuilder->obtenerAdoptantePorMascota($mascotaId);
    }

    public function importarMascotasCsv(string $rutaTemporalCsv, int $idUsuario): array
    {
        $valoresDeCampo = static function (array $items, string $campo): array {
            return array_map(
                static fn ($item) => strtolower((string) ($item->fields[$campo] ?? '')),
                $items
            );
        };
        $especiesPermitidas = $valoresDeCampo($this->getEspecies(), 'especie');
        $tamanosPermitidos = $valoresDeCampo($this->getTamanos(), 'tamano');
        $temperamentosPermitidos = $valoresDeCampo($this->getTemperamentos(), 'temperamento');

        $archivo = fopen($rutaTemporalCsv, 'r');
        $esPrimeraLinea = true;
        
        $erroresImportacion = [];
        $cantidadImportadas = 0;
        $filaNum = 1;

        if ($archivo !== false) {
            while (($data = fgetcsv($archivo, 1000, ",", "\"", "\\")) !== false) {
                if ($esPrimeraLinea) {
                    $esPrimeraLinea = false;
                    $filaNum++;
                    continue;
                }
                
                if (count($data) < 8) {
                    $erroresImportacion[] = "Fila $filaNum: Faltan columnas esperadas.";
                    $filaNum++;
                    continue;
                }

                $nombre = trim($data[0] ?? '');
                $especie = trim($data[1] ?? '');
                $tamanio = trim($data[2] ?? '');
                $temperamento = trim($data[3] ?? '');
                $sexo = trim($data[4] ?? '');
                $castradoStr = trim($data[5] ?? '');
                $descripcion = trim($data[6] ?? '');
                
                $largoNombre = function_exists('mb_strlen') ? mb_strlen($nombre) : strlen($nombre);
                $nombreValido = filter_var($nombre, FILTER_VALIDATE_REGEXP, [
                    "options" => ["regexp" => "/^[\\p{L}\\s'-]+$/u"]
                ]);
                
                if ($nombre === '' || $largoNombre < 2 || $largoNombre > 60 || !$nombreValido) {
                    $erroresImportacion[] = "Fila $filaNum: Nombre '$nombre' inválido.";
                    $filaNum++; continue;
                }

                if (empty($especie)) {
                    $erroresImportacion[] = "Fila $filaNum: Especie no puede estar vacía.";
                    $filaNum++; continue;
                }
                if (!in_array(strtolower($tamanio), ['pequeño', 'mediano', 'grande'], true)) {
                    $erroresImportacion[] = "Fila $filaNum: Tamaño '$tamanio' no válido (use pequeño, mediano o grande).";
                    $filaNum++; continue;
                }
                if (empty($temperamento)) {
                    $erroresImportacion[] = "Fila $filaNum: Temperamento no puede estar vacío.";
                    $filaNum++; continue;
                }
                if (!in_array(strtolower($sexo), ['macho', 'hembra'], true)) {
                    $erroresImportacion[] = "Fila $filaNum: Sexo '$sexo' debe ser macho o hembra.";
                    $filaNum++; continue;
                }
                if (!in_array(strtolower($castradoStr), ['si', 'no'], true)) {
                    $erroresImportacion[] = "Fila $filaNum: Castrado '$castradoStr' debe ser si o no.";
                    $filaNum++; continue;
                }
                
                $fechaNac = trim($data[7] ?? '');
                $fechaValida = filter_var($fechaNac, FILTER_VALIDATE_REGEXP, [
                    "options" => ["regexp" => "/^\d{4}-\d{2}-\d{2}$/"]
                ]);
                
                $edadValida = null;
                if (!empty($fechaNac)) {
                    if (!$fechaValida) {
                        $erroresImportacion[] = "Fila $filaNum: Fecha de nacimiento '$fechaNac' tiene formato inválido (use AAAA-MM-DD).";
                        $filaNum++; continue;
                    }
                    $d = \DateTime::createFromFormat('Y-m-d', $fechaNac);
                    $minDate = (new \DateTime())->modify('-30 years');
                    if (!$d || $d->format('Y-m-d') !== $fechaNac) {
                        $erroresImportacion[] = "Fila $filaNum: Fecha de nacimiento '$fechaNac' inválida (use AAAA-MM-DD).";
                        $filaNum++; continue;
                    } elseif ($d > new \DateTime()) {
                        $erroresImportacion[] = "Fila $filaNum: La fecha de nacimiento no puede ser futura.";
                        $filaNum++; continue;
                    } elseif ($d < $minDate) {
                        $erroresImportacion[] = "Fila $filaNum: La edad máxima permitida es de 30 años.";
                        $filaNum++; continue;
                    } else {
                        $edadValida = filter_var((int) $d->diff(new \DateTime())->y, FILTER_VALIDATE_INT);
                    }
                } else {
                    $erroresImportacion[] = "Fila $filaNum: Falta la fecha de nacimiento.";
                    $filaNum++; continue;
                }

                $largoDescripcion = function_exists('mb_strlen') ? mb_strlen($descripcion) : strlen($descripcion);
                if ($largoDescripcion > 500) {
                    $erroresImportacion[] = "Fila $filaNum: Descripción muy larga.";
                    $filaNum++; continue;
                }
                
                $nombreSeguro = trim($nombre);
                $especieSegura = trim($especie);
                $sexoSeguro = trim(strtolower($sexo));
                $tamanoSeguro = trim($tamanio);
                $temperamentoSeguro = trim($temperamento);
                $descripcionSegura = trim($descripcion);

                $dicc = new DiccionarioCollection();
                $dicc->setQueryBuilder($this->queryBuilder);

                $duplicados = $this->queryBuilder->select('mascota', [
                    'refugio_id' => $idUsuario,
                    'nombre' => $nombreSeguro,
                    'especie_id' => $dicc->obtenerOCrearId('especie', $especieSegura),
                    'sexo' => $sexoSeguro,
                    'tamano_id' => $dicc->obtenerOCrearId('tamano', $tamanoSeguro),
                    'edad' => $edadValida
                ]);
                
                if (!empty($duplicados)) {
                    $erroresImportacion[] = "Fila $filaNum: Mascota '$nombre' ya está registrada exactamente igual (duplicada).";
                    $filaNum++; continue;
                }

                try {
                    $this->queryBuilder->insert('mascota', [
                        'refugio_id'      => $idUsuario,
                        'nombre'          => $nombreSeguro,
                        'especie_id'      => $dicc->obtenerOCrearId('especie', $especieSegura),
                        'descripcion'     => $descripcionSegura,
                        'edad'            => $edadValida,
                        'tamano_id'       => $dicc->obtenerOCrearId('tamano', $tamanoSeguro),
                        'sexo'            => $sexoSeguro,
                        'temperamento_id' => $dicc->obtenerOCrearId('temperamento', $temperamentoSeguro),
                        'castrado'        => (strtolower($castradoStr) === 'si') ? 1 : 0,
                        'vacunado'        => 0,
                        'estado_adopcion' => 'DISPONIBLE',
                        'imagen'          => 'default-pet.jpg',
                        'svg'             => null,
                    ]);
                    $cantidadImportadas++;
                } catch (\Exception $e) {
                    $erroresImportacion[] = "Fila $filaNum: Error al guardar - " . $e->getMessage();
                }
                
                $filaNum++;
            }
            fclose($archivo);
        }

        return [
            'cantidad_exito' => $cantidadImportadas,
            'errores' => $erroresImportacion
        ];
    }

    public function guardarMascotaIndividual(array $post, ?array $foto, ?array $svg, int $userId): array
    {
        $erroresMascota = [];

        $valoresDeCampo = static function (array $items, string $campo): array {
            return array_map(
                static fn ($item) => strtolower((string) ($item->fields[$campo] ?? '')),
                $items
            );
        };
        $especiesPermitidas = $valoresDeCampo($this->getEspecies(), 'especie');
        $tamanosPermitidos = $valoresDeCampo($this->getTamanos(), 'tamano');
        $temperamentosPermitidos = $valoresDeCampo($this->getTemperamentos(), 'temperamento');

        // --- Validaciones ---
        $nombre = trim($post['nombre'] ?? '');
        $largoNombre = function_exists('mb_strlen') ? mb_strlen($nombre) : strlen($nombre);
        $nombreValido = filter_var($nombre, FILTER_VALIDATE_REGEXP, [
            "options" => ["regexp" => "/^[\\p{L}\\s'-]+$/u"]
        ]);

        if ($nombre === '') {
            $erroresMascota['nombre'] = 'El nombre es obligatorio.';
        } elseif ($largoNombre < 2 || $largoNombre > 60) {
            $erroresMascota['nombre'] = 'El nombre debe tener entre 2 y 60 caracteres.';
        } elseif (!$nombreValido) {
            $erroresMascota['nombre'] = 'Solo se permiten letras, espacios, apóstrofes y guiones.';
        }

        $especie = trim($post['especie'] ?? '');
        if (strtolower($especie) === 'otro') {
            $especie = trim($post['nueva_especie'] ?? '');
            if ($especie === '') {
                $erroresMascota['especie'] = 'Debe especificar la nueva especie.';
            } else {
                $especieValida = filter_var($especie, FILTER_VALIDATE_REGEXP, [
                    'options' => ['regexp' => "/^[\\p{L}\\s]+$/u"]
                ]);
                if (!$especieValida) {
                    $erroresMascota['especie'] = 'La nueva especie solo puede contener letras.';
                }
            }
        } elseif ($especie === '') {
            $erroresMascota['especie'] = 'Debe seleccionar una especie.';
        }

        $tamanio = trim($post['tamanio'] ?? '');
        if ($tamanio === '') {
            $erroresMascota['tamanio'] = 'Debe seleccionar un tamaño.';
        } elseif (!in_array(strtolower($tamanio), ['pequeño', 'mediano', 'grande'], true)) {
            $erroresMascota['tamanio'] = 'El tamaño seleccionado no es válido.';
        }

        $temperamento = trim($post['temperamento'] ?? '');
        if (strtolower($temperamento) === 'otro') {
            $temperamento = trim($post['nuevo_temperamento'] ?? '');
            if ($temperamento === '') {
                $erroresMascota['temperamento'] = 'Debe especificar el nuevo temperamento.';
            } else {
                $temperamentoValido = filter_var($temperamento, FILTER_VALIDATE_REGEXP, [
                    'options' => ['regexp' => "/^[\\p{L}\\s]+$/u"]
                ]);
                if (!$temperamentoValido) {
                    $erroresMascota['temperamento'] = 'El nuevo temperamento solo puede contener letras.';
                }
            }
        } elseif ($temperamento === '') {
            $erroresMascota['temperamento'] = 'Debe seleccionar un temperamento.';
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
        $fechaValida = filter_var($fechaNac, FILTER_VALIDATE_REGEXP, [
            "options" => ["regexp" => "/^\d{4}-\d{2}-\d{2}$/"]
        ]);
        $edad = null;
        if (!empty($fechaNac)) {
            if (!$fechaValida) {
                $erroresMascota['fecha_nacimiento'] = 'Fecha de nacimiento inválida.';
            } else {
                $d = \DateTime::createFromFormat('Y-m-d', $fechaNac);
                $minDate = (new \DateTime())->modify('-30 years');
                if (!$d || $d->format('Y-m-d') !== $fechaNac) {
                    $erroresMascota['fecha_nacimiento'] = 'Fecha de nacimiento inválida.';
                } elseif ($d > new \DateTime()) {
                    $erroresMascota['fecha_nacimiento'] = 'La fecha de nacimiento no puede ser futura.';
                } elseif ($d < $minDate) {
                    $erroresMascota['fecha_nacimiento'] = 'La edad máxima permitida es de 30 años.';
                } else {
                    $edad = filter_var((int) $d->diff(new \DateTime())->y, FILTER_VALIDATE_INT);
                }
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

        if (!empty($erroresMascota)) {
            return $erroresMascota;
        }

        if ($svgValidoParaMover) {
            try {
                $svgRelativa = \Paw\App\Helpers\GCSHelper::subir($svg, 'mascotas_svg');
            } catch (\Exception $e) {
                $erroresMascota['svg'] = 'No se pudo guardar el SVG: ' . $e->getMessage();
                return $erroresMascota;
            }
        }

        if ($fotoValidaParaMover) {
            try {
                $imagenRelativa = \Paw\App\Helpers\GCSHelper::subir($foto, 'mascotas');
            } catch (\Exception $e) {
                $erroresMascota['foto'] = 'No se pudo guardar la imagen: ' . $e->getMessage();
                return $erroresMascota;
            }
        }

        $nombreSeguro = trim($nombre);
        $especieSegura = trim($especie);
        $sexoSeguro = trim($sexo);
        $tamanoSeguro = trim($tamanio);
        $temperamentoSeguro = trim($temperamento);
        $descripcionSegura = trim($descripcionMascota);

        // Insertar en la BD
        $dicc = new DiccionarioCollection();
        $dicc->setQueryBuilder($this->queryBuilder);

        $mascotaId = $this->queryBuilder->insert('mascota', [
            'refugio_id'      => $userId,
            'nombre'          => $nombreSeguro,
            'especie_id'      => $dicc->obtenerOCrearId('especie', $especieSegura),
            'descripcion'     => $descripcionSegura,
            'edad'            => $edad,
            'fecha_nacimiento'=> $fechaNac ?: null,
            'tamano_id'       => $dicc->obtenerOCrearId('tamano', $tamanoSeguro),
            'sexo'            => $sexoSeguro,
            'temperamento_id' => $dicc->obtenerOCrearId('temperamento', $temperamentoSeguro),
            'castrado'        => ($esterilizado === 'si') ? 1 : 0,
            'vacunado'        => 0,
            'estado_adopcion' => 'DISPONIBLE',
            'imagen'          => $imagenRelativa ?? 'default-pet.jpg',
            'svg'             => $svgRelativa,
        ]);

        if ($mascotaId) {
            try {
                // Obtener datos del refugio para saber la ubicación de la mascota
                $refugios = $this->queryBuilder->select('refugio', ['usuario_id' => $userId]);
                $refugio = $refugios[0] ?? [];
                
                $colaCollection = new \Paw\App\Models\ColaEsperaCollection();
                $colaCollection->setQueryBuilder($this->queryBuilder);
                $colaCollection->verificarMatches((int)$mascotaId, [
                    'nombre'       => $nombreSeguro,
                    'especie'      => $especieSegura,
                    'tamano'       => $tamanoSeguro,
                    'temperamento' => $temperamentoSeguro,
                    'provincia'    => $refugio['provincia'] ?? '',
                    'ciudad'       => $refugio['ciudad'] ?? '',
                ]);
            } catch (\Exception $e) {
                error_log("Error en Cola de Espera: " . $e->getMessage());
            }
        }

        return [];
    }

    public function actualizarMascotaConArchivos(int $id, array $datosMascota, array $archivos): void
    {
        $db = $this->getQueryBuilder();
        $mascota = $this->get($id);
        
        $imagenRelativa = $mascota->fields['imagen'] ?? 'default-pet.jpg';
        $fotoValidaParaMover = false;
        
        // Manejar subida de Foto Principal
        $foto = $archivos['foto'] ?? null;
        if ($foto && isset($foto['error']) && $foto['error'] === UPLOAD_ERR_OK) {
            $fotoValidaParaMover = true;
        }

        $svgRelativa = $mascota->fields['svg'] ?? null;
        $svgValidoParaMover = false;
        $svg = $archivos['svg'] ?? null;
        if ($svg && isset($svg['error']) && $svg['error'] === UPLOAD_ERR_OK) {
            $svgValidoParaMover = true;
        } elseif (!empty($datosMascota['eliminar_svg'])) {
            $svgRelativa = null;
        }

        if ($svgValidoParaMover) {
            $svgRelativa = GCSHelper::subir($svg, 'mascotas_svg');
        }

        if ($fotoValidaParaMover) {
            $imagenRelativa = GCSHelper::subir($foto, 'mascotas');
        }

        $edad = null;
        if (!empty($datosMascota['fecha_nacimiento'])) {
            $d = \DateTime::createFromFormat('Y-m-d', $datosMascota['fecha_nacimiento']);
            if ($d) {
                $edad = (int) $d->diff(new \DateTime())->y;
            }
        } else {
            $edad = $mascota->fields['edad'] ?? null;
        }

        $dicc = new DiccionarioCollection();
        $dicc->setQueryBuilder($this->queryBuilder);

        $especie = $datosMascota['especie'] ?? '';
        if (strtolower($especie) === 'otro') {
            $especie = $datosMascota['nueva_especie'] ?? '';
        }
        
        $temperamento = $datosMascota['temperamento'] ?? '';
        if (strtolower($temperamento) === 'otro') {
            $temperamento = $datosMascota['nuevo_temperamento'] ?? '';
        }
        
        $datosUpdate = [
            'nombre'          => htmlspecialchars($datosMascota['nombre'] ?? '', ENT_QUOTES, 'UTF-8'),
            'especie_id'      => $dicc->obtenerOCrearId('especie', $especie),
            'descripcion'     => htmlspecialchars($datosMascota['descripcion_mascota'] ?? '', ENT_QUOTES, 'UTF-8'),
            'edad'            => $edad,
            'fecha_nacimiento'=> !empty($datosMascota['fecha_nacimiento']) ? $datosMascota['fecha_nacimiento'] : null,
            'tamano_id'       => $dicc->obtenerOCrearId('tamano', $datosMascota['tamanio'] ?? ''),
            'sexo'            => htmlspecialchars($datosMascota['sexo'] ?? '', ENT_QUOTES, 'UTF-8'),
            'temperamento_id' => $dicc->obtenerOCrearId('temperamento', $temperamento),
            'castrado'     => (($datosMascota['esterilizado'] ?? '') === 'si') ? 1 : 0,
            'imagen'       => $imagenRelativa,
            'svg'          => $svgRelativa,
        ];

        $db->update('mascota', $datosUpdate, ['id' => $id]);

        // Guardar fotos adicionales
        $archivosExtra = $archivos['archivo_multimedia'] ?? null;
        if ($archivosExtra) {
            if (isset($archivosExtra['name']) && is_array($archivosExtra['name'])) {
                $count = count($archivosExtra['name']);
                for ($i = 0; $i < $count; $i++) {
                    if ($archivosExtra['error'][$i] === UPLOAD_ERR_OK) {
                        $archivoIndividual = [
                            'name'     => $archivosExtra['name'][$i],
                            'type'     => $archivosExtra['type'][$i],
                            'tmp_name' => $archivosExtra['tmp_name'][$i],
                            'error'    => $archivosExtra['error'][$i],
                            'size'     => $archivosExtra['size'][$i],
                        ];
                        try {
                            $urlExtra = GCSHelper::subir($archivoIndividual, 'media_mascotas');
                            $esVideo = str_starts_with($archivoIndividual['type'] ?? '', 'video/');
                            $db->insert('media_mascota', [
                                'mascota_id' => $id,
                                'tipo'       => $esVideo ? 'video' : 'foto',
                                'url'        => $urlExtra
                            ]);
                        } catch (\Exception $e) {
                            error_log("Error guardando foto extra múltiple: " . $e->getMessage());
                        }
                    }
                }
            } elseif (isset($archivosExtra['error']) && $archivosExtra['error'] === UPLOAD_ERR_OK) {
                try {
                    $urlExtra = GCSHelper::subir($archivosExtra, 'media_mascotas');
                    $esVideo = str_starts_with($archivosExtra['type'] ?? '', 'video/');
                    $db->insert('media_mascota', [
                        'mascota_id' => $id,
                        'tipo'       => $esVideo ? 'video' : 'foto',
                        'url'        => $urlExtra
                    ]);
                } catch (\Exception $e) {
                    error_log("Error guardando foto extra individual: " . $e->getMessage());
                }
            }
        }
    }

    public function eliminarSvg(int $id): void
    {
        $mascota = $this->get($id);
        $svgRelativo = $mascota->fields['svg'] ?? '';
        if ($svgRelativo !== '') {
            if (GCSHelper::esUrlBucket($svgRelativo)) {
                GCSHelper::borrar($svgRelativo);
            } else {
                $path = __DIR__ . '/../../../../public/assets/svg/' . ltrim($svgRelativo, '/');
                if (file_exists($path) && strpos($svgRelativo, '..') === false && is_file($path)) {
                    @unlink($path);
                }
            }
            $db = $this->getQueryBuilder();
            $db->update('mascota', ['svg' => null], ['id' => $id]);
        }
    }

    public function eliminarFotoPrincipal(int $id): void
    {
        $mascota = $this->get($id);
        $fotoRelativo = $mascota->fields['imagen'] ?? '';
        if ($fotoRelativo !== '' && $fotoRelativo !== 'default-pet.jpg') {
            if (GCSHelper::esUrlBucket($fotoRelativo)) {
                GCSHelper::borrar($fotoRelativo);
            } else {
                $path = __DIR__ . '/../../../../public/assets/img/' . ltrim($fotoRelativo, '/');
                if (file_exists($path) && strpos($fotoRelativo, '..') === false && is_file($path)) {
                    @unlink($path);
                }
            }
            $db = $this->getQueryBuilder();
            $db->update('mascota', ['imagen' => 'default-pet.jpg'], ['id' => $id]);
        }
    }

    public function eliminar(int $id): void
    {
        $db = $this->getQueryBuilder();
        $db->delete($this->table, ['id' => $id]);
    }

    public function incrementarVisitas(int $id): void
    {
        $this->getQueryBuilder()->incrementarVisitas($this->table, $id);
    }

    public function getMascotasInvisibles(int $limite = 8): array
    {
        $resultados = $this->getQueryBuilder()->getMascotasInvisibles($this->table, $limite);
        return $this->mapMascotas($resultados);
    }
}
