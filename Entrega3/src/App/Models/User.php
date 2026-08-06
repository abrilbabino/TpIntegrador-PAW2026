<?php

namespace Paw\App\Models;
use Paw\Core\Model;
use Paw\App\Helpers\GCSHelper;
use Paw\Core\Exceptions\InvalidValueFormatException;
use Exception;
class User extends Model
{
    protected $table = 'usuario';
    protected $fields = [
        'nombre_usuario' => null,
        'email' => null,
        'contrasena' => null,
        'contacto' => null,
        'foto_perfil' => null,
    ];

    public function getFotoPerfil(): ?string
    {
        return $this->fields['foto_perfil'] ?? null;
    }

    public function set(array $datos): void
    {
        $this->fields['nombre_usuario'] = $datos['nombre_usuario'] ?? null;
        $this->fields['email']          = $datos['email'] ?? null;
        $this->fields['contacto']       = $datos['contacto'] ?? null;
    }

    public function validarDatosPerfil(array $datos): array
    {
        $errores = [];

        if (empty($datos['nombre_usuario'])) {
            $errores['nombre_usuario'] = "El nombre de usuario es obligatorio.";
        } elseif (strlen($datos['nombre_usuario']) < 4 || strlen($datos['nombre_usuario']) > 20) {
            $errores['nombre_usuario'] = "El nombre de usuario debe tener entre 4 y 20 caracteres.";
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $datos['nombre_usuario'])) {
            $errores['nombre_usuario'] = "El nombre de usuario contiene caracteres no válidos.";
        }

        if (empty($datos['email']) || !filter_var($datos['email'], FILTER_VALIDATE_EMAIL) || !preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $datos['email'])) {
            $errores['email'] = "Debe ingresar un email válido.";
        }

        if (empty($datos['nombre'])) {
            $errores['nombre'] = "El nombre es obligatorio.";
        } elseif (strlen($datos['nombre']) < 2 || strlen($datos['nombre']) > 50) {
            $errores['nombre'] = "El nombre debe tener entre 2 y 50 caracteres.";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['nombre'])) {
            $errores['nombre'] = "El nombre solo puede contener letras y espacios.";
        }

        if (empty($datos['apellido'])) {
            $errores['apellido'] = "El apellido es obligatorio.";
        } elseif (strlen($datos['apellido']) < 2 || strlen($datos['apellido']) > 50) {
            $errores['apellido'] = "El apellido debe tener entre 2 y 50 caracteres.";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $datos['apellido'])) {
            $errores['apellido'] = "El apellido solo puede contener letras y espacios.";
        }

        if (empty($datos['dni'])) {
            $errores['dni'] = "El DNI es obligatorio.";
        } else {
            $dniLimpio = preg_replace('/[^0-9]/', '', $datos['dni']);
            if (strlen($dniLimpio) < 7 || strlen($dniLimpio) > 8) {
                $errores['dni'] = "El DNI debe tener exactamente 7 u 8 números.";
            } elseif (!preg_match('/^[0-9\.]{7,10}$/', $datos['dni'])) {
                $errores['dni'] = "El DNI solo puede contener números y puntos.";
            }
        }

        if (!empty($datos['fecha_de_nacimiento'])) {
            $d = \DateTime::createFromFormat('Y-m-d', $datos['fecha_de_nacimiento']);
            if (!$d || $d->format('Y-m-d') !== $datos['fecha_de_nacimiento']) {
                $errores['fecha_de_nacimiento'] = "La fecha de nacimiento no tiene un formato válido.";
            } elseif ($d > new \DateTime()) {
                $errores['fecha_de_nacimiento'] = "La fecha de nacimiento no puede ser futura.";
            } else {
                $hace16Anios = (new \DateTime())->modify('-16 years');
                if ($d > $hace16Anios) {
                    $errores['fecha_de_nacimiento'] = "Debes tener al menos 16 años de edad.";
                }
            }
        }

        if (!empty($datos['contacto'])) {
            $contactoLimpio = preg_replace('/[^0-9]/', '', $datos['contacto']);
            if (strlen($contactoLimpio) < 8 || strlen($contactoLimpio) > 15) {
                $errores['contacto'] = "El teléfono debe tener entre 8 y 15 números.";
            } elseif (!preg_match('/^\+?[0-9\s\-]{6,20}$/', $datos['contacto'])) {
                $errores['contacto'] = "El teléfono contiene caracteres no válidos.";
            }
        }

        if (!empty($datos['contrasena']) && strlen($datos['contrasena']) < 6) {
            $errores['contrasena'] = "La contraseña debe tener al menos 6 caracteres.";
        }

        return $errores;
    }

    public function setFotoPerfil(?string $path): void
    {
        $this->fields['foto_perfil'] = $path;
    }

    public function crearUsuario($fields)
    {
        return $this->queryBuilder->insert('usuario', $fields);
    }

    public function crearAdoptante($fields)
    {
        return $this->queryBuilder->insert('adoptante', $fields);
    }

    public function crearRefugio($fields)
    {
        return $this->queryBuilder->insert('refugio', $fields);
    }

    public function findByNombreUsuario($nombreUsuario)
    {
        return $this->queryBuilder->select('usuario', [
            'nombre_usuario' => $nombreUsuario
        ]);
    }

    /**
     * Busca un usuario por nombre_usuario y retorna un solo registro.
     */
    public function findByUsername(string $username): array|false
    {
        return $this->queryBuilder->selectOne($this->table, [
            'nombre_usuario' => $username,
        ]);
    }

    /**
     * Registra un nuevo usuario (y su perfil asociado) realizando las validaciones necesarias.
     */
    public function registrar(array $datos): array
    {
        $name     = trim($datos['name'] ?? '');
        $email    = trim($datos['email'] ?? '');
        $username = trim($datos['username'] ?? '');
        $password = $datos['password'] ?? '';
        $rol      = $datos['rol'] ?? '';

        $apellido         = trim($datos['apellido'] ?? '');
        $dni              = trim($datos['dni'] ?? '');
        $fecha_nacimiento = trim($datos['fecha_nacimiento'] ?? '');

        // Validación básica
        if (!$name || !$email || !$username || !$password) {
            throw new InvalidValueFormatException('campos');
        }

        if ($rol === 'adoptante' || $rol === '') {
            if (!$apellido || !$dni || !$fecha_nacimiento) {
                throw new InvalidValueFormatException('campos');
            }
            if (strlen($name) < 2 || strlen($name) > 50 || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $name)) {
                throw new InvalidValueFormatException('nombre_invalido');
            }
            if (strlen($apellido) < 2 || strlen($apellido) > 50 || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $apellido)) {
                throw new InvalidValueFormatException('apellido_invalido');
            }
            
            $dniLimpio = preg_replace('/[^0-9]/', '', $dni);
            if (strlen($dniLimpio) < 7 || strlen($dniLimpio) > 8 || !preg_match('/^[0-9\.]{7,10}$/', $dni)) {
                throw new InvalidValueFormatException('dni_invalido');
            }

            $d = \DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
            if (!$d || $d->format('Y-m-d') !== $fecha_nacimiento || $d > new \DateTime()) {
                throw new InvalidValueFormatException('fecha_invalida');
            }
            $hace16Anios = (new \DateTime())->modify('-16 years');
            if ($d > $hace16Anios) {
                throw new InvalidValueFormatException('edad_invalida');
            }
        }

        // Sanitizar
        $name     = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $apellido = htmlspecialchars($apellido, ENT_QUOTES, 'UTF-8');
        $dni      = htmlspecialchars($dni, ENT_QUOTES, 'UTF-8');
        $email    = filter_var($email, FILTER_SANITIZE_EMAIL);
        $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

        $existente = $this->findByUsername($username);
        if ($existente) {
            throw new Exception('usuario_existente');
        }

        $existenteEmail = $this->findByEmail($email);
        if ($existenteEmail) {
            throw new Exception('email_existente');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $userId = $this->crearUsuario([
            'nombre_usuario' => $username,
            'email'          => $email,
            'contrasena'     => $passwordHash,
            'contacto'       => null,
        ]);

        if ($rol === 'refugio') {
            $this->crearRefugio([
                'usuario_id'         => $userId,
                'nombre_institucion' => $name,
                'cuit'               => null,
                'email'              => $email,
            ]);

            $refugio = $this->getRefugio((int) $userId);
            return [
                'id'             => $userId,
                'nombre_usuario' => $username,
                'email'          => $email,
                'rol'            => 'refugio',
                'refugio_id'     => $refugio ? $refugio['usuario_id'] : null,
            ];
        } else {
            $this->crearAdoptante([
                'usuario_id'          => $userId,
                'nombre'              => $name,
                'apellido'            => $apellido,
                'dni'                 => $dni,
                'fecha_de_nacimiento' => $fecha_nacimiento,
            ]);

            $adoptante = $this->getAdoptante((int) $userId);
            return [
                'id'             => $userId,
                'nombre_usuario' => $username,
                'email'          => $email,
                'rol'            => 'adoptante',
                'adoptante_id'   => $adoptante ? $adoptante['usuario_id'] : null,
                'foto_perfil'    => null,
                'contacto'       => null,
            ];
        }
    }

    /**
     * Busca un usuario por email y retorna un solo registro.
     */
    public function findByEmail(string $email): array|false
    {
        return $this->queryBuilder->selectOne($this->table, [
            'email' => $email,
        ]);
    }

    /**
     * Busca un usuario por ID.
     */
    public function findById(int $id): array|false
    {
        return $this->queryBuilder->selectOne($this->table, [
            'id' => $id,
        ]);
    }

    /**
     * Obtiene el adoptante vinculado a un usuario.
     */
    public function getAdoptante(int $usuarioId): array|false
    {
        return $this->queryBuilder->selectOne('adoptante', [
            'usuario_id' => $usuarioId,
        ]);
    }
    public function getRefugio(int $usuarioId): array|false
    {
        return $this->queryBuilder->selectOne('refugio', [
            'usuario_id' => $usuarioId,
        ]);
    }

    public function updateUsuario(int $id, array $fields)
    {
        return $this->queryBuilder->update('usuario', $fields, ['id' => $id]);
    }

    public function updateAdoptante(int $usuarioId, array $fields)
    {
        return $this->queryBuilder->update('adoptante', $fields, ['usuario_id' => $usuarioId]);
    }

    public function actualizarPerfilCompleto(int $userId, array $postData, ?array $archivo, array $sessionUser): array
    {
        $errores = $this->validarDatosPerfil($postData);
        
        $nombreUsuario = trim($postData['nombre_usuario'] ?? '');
        $email = trim($postData['email'] ?? '');
        
        if ($nombreUsuario !== $sessionUser['nombre_usuario']) {
            $existente = $this->findByUsername($nombreUsuario);
            if ($existente && (int)$existente['id'] !== $userId) {
                $errores['nombre_usuario'] = "El nombre de usuario elegido ya se encuentra en uso.";
            }
        }

        if ($email !== $sessionUser['email']) {
            $existenteEmail = $this->findByEmail($email);
            if ($existenteEmail && (int)$existenteEmail['id'] !== $userId) {
                $errores['email'] = "El email ingresado ya se encuentra registrado.";
            }
        }

        $newPassword = $postData['contrasena'] ?? '';
        if (!empty($newPassword)) {
            $oldPassword = $postData['contrasena_actual'] ?? '';
            if (empty($oldPassword)) {
                $errores['contrasena_actual'] = "Debes ingresar tu contraseña actual para cambiarla.";
            } else {
                $currentUser = $this->findById($userId);
                if (!$currentUser || !password_verify($oldPassword, $currentUser['contrasena'])) {
                    $errores['contrasena_actual'] = "La contraseña actual es incorrecta.";
                }
            }
        }

        if (count($errores) > 0) {
            return $errores;
        }

        $fieldsUsuario = [
            'nombre_usuario' => htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'),
            'email'          => filter_var($email, FILTER_SANITIZE_EMAIL),
            'contacto'       => htmlspecialchars(trim($postData['contacto'] ?? ''), ENT_QUOTES, 'UTF-8'),
        ];

        if (!empty($newPassword)) {
            $fieldsUsuario['contrasena'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $erroresFoto = $this->procesarFotoPerfil($userId, $postData, $archivo, $fieldsUsuario);
        if (!empty($erroresFoto)) {
            return array_merge($errores, $erroresFoto);
        }

        $this->updateUsuario($userId, $fieldsUsuario);

        $nombre   = trim($postData['nombre'] ?? '');
        $apellido = trim($postData['apellido'] ?? '');
        $dni      = trim($postData['dni'] ?? '');
        $fechaNac = trim($postData['fecha_de_nacimiento'] ?? '');

        $fieldsAdoptante = [
            'nombre'              => htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'),
            'apellido'            => htmlspecialchars($apellido, ENT_QUOTES, 'UTF-8'),
            'dni'                 => htmlspecialchars($dni, ENT_QUOTES, 'UTF-8'),
            'fecha_de_nacimiento' => empty($fechaNac) ? null : $fechaNac,
        ];

        if ($this->getAdoptante($userId)) {
            $this->updateAdoptante($userId, $fieldsAdoptante);
        } else {
            $fieldsAdoptante['usuario_id'] = $userId;
            $this->crearAdoptante($fieldsAdoptante);
        }

        return $errores;
    }

    public function actualizarPerfilRefugio(int $userId, array $postData, ?array $archivo, array $sessionUser): array
    {
        $errores = [];

        // Validar nombre_institucion
        if (empty($postData['nombre_institucion'])) {
            $errores['nombre_institucion'] = "El nombre de la institución es obligatorio.";
        } elseif (strlen($postData['nombre_institucion']) < 2 || strlen($postData['nombre_institucion']) > 100) {
            $errores['nombre_institucion'] = "El nombre de la institución debe tener entre 2 y 100 caracteres.";
        }

        // Validar email
        $email = trim($postData['email'] ?? '');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = "Debe ingresar un email válido.";
        } elseif ($email !== $sessionUser['email']) {
            $existenteEmail = $this->findByEmail($email);
            if ($existenteEmail && (int)$existenteEmail['id'] !== $userId) {
                $errores['email'] = "El email ingresado ya se encuentra registrado.";
            }
        }

        // Validar telefono
        if (!empty($postData['telefono'])) {
            $telefonoLimpio = preg_replace('/[^0-9]/', '', $postData['telefono']);
            if (strlen($telefonoLimpio) < 8 || strlen($telefonoLimpio) > 15) {
                $errores['telefono'] = "El teléfono debe tener entre 8 y 15 números.";
            } elseif (!preg_match('/^\+?[0-9\s\-]{6,20}$/', $postData['telefono'])) {
                $errores['telefono'] = "El teléfono contiene caracteres no válidos.";
            }
        }

        // Validar cvu (exactamente 22 números si se ingresa)
        if (!empty($postData['cvu'])) {
            $cvuLimpio = preg_replace('/[^0-9]/', '', $postData['cvu']);
            if (strlen($cvuLimpio) !== 22) {
                $errores['cvu'] = "El CVU debe tener exactamente 22 dígitos numéricos.";
            }
        }

        // Validar alias (opcional, de 4 a 40 caracteres)
        if (!empty($postData['alias'])) {
            if (strlen($postData['alias']) < 4 || strlen($postData['alias']) > 40) {
                $errores['alias'] = "El alias debe tener entre 4 y 40 caracteres.";
            }
        }

        if (count($errores) > 0) {
            return $errores;
        }

        // Actualizar usuario (email)
        $fieldsUsuario = [
            'email' => filter_var($email, FILTER_SANITIZE_EMAIL),
        ];

        $erroresFoto = $this->procesarFotoPerfil($userId, $postData, $archivo, $fieldsUsuario);
        if (!empty($erroresFoto)) {
            return array_merge($errores, $erroresFoto);
        }

        $refugioExistente = $this->getRefugio($userId);
        
        // Logica de sincronización bidireccional (preservar foto de refugio si existe y usuario no tiene)
        $imagenRefugio = $refugioExistente ? ($refugioExistente['imagen'] ?? 'default-refugio.jpg') : 'default-refugio.jpg';
        $fotoUsuario = $sessionUser['foto_perfil'] ?? null;

        if (array_key_exists('foto_perfil', $fieldsUsuario)) {
            // Se subió o eliminó explícitamente la foto
            $fieldsRefugioSync = $fieldsUsuario['foto_perfil'] ?? 'default-refugio.jpg';
        } else {
            // No se modificó la foto en esta petición. Conservamos lo que haya.
            if ($imagenRefugio !== 'default-refugio.jpg' && empty($fotoUsuario)) {
                // Preservar la del refugio y asignarla al usuario
                $fieldsUsuario['foto_perfil'] = $imagenRefugio;
                $fieldsRefugioSync = $imagenRefugio;
            } else {
                // Por defecto, mandamos la del usuario al refugio para mantenerlos sincronizados
                $fieldsRefugioSync = $fotoUsuario ?? 'default-refugio.jpg';
            }
        }

        $this->updateUsuario($userId, $fieldsUsuario);

        // Actualizar refugio
        $fieldsRefugio = [
            'nombre_institucion' => htmlspecialchars(trim($postData['nombre_institucion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'descripcion' => htmlspecialchars(trim($postData['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'telefono' => htmlspecialchars(trim($postData['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'alias' => htmlspecialchars(trim($postData['alias'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'cvu' => htmlspecialchars(trim($postData['cvu'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'imagen' => $fieldsRefugioSync,
            'email' => filter_var($email, FILTER_SANITIZE_EMAIL),
        ];

        // Verificar si existe el refugio en la DB
        if ($refugioExistente) {
            $this->queryBuilder->update('refugio', $fieldsRefugio, ['usuario_id' => $userId]);
        } else {
            $fieldsRefugio['usuario_id'] = $userId;
            $this->crearRefugio($fieldsRefugio);
        }

        return $errores;
    }

    public function actualizarUbicacionRefugio(int $userId, array $postData): array
    {
        $errores = [];

        if (empty($postData['latitud']) || empty($postData['longitud'])) {
            $errores['ubicacion'] = "Debe seleccionar una ubicación válida del buscador.";
            return $errores;
        }

        $fields = [
            'refugio_id' => $userId,
            'latitud'    => (float) $postData['latitud'],
            'longitud'   => (float) $postData['longitud'],
            'ciudad'     => htmlspecialchars(trim($postData['ciudad'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'provincia'  => htmlspecialchars(trim($postData['provincia'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'pais'       => htmlspecialchars(trim($postData['pais'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'direccion'  => htmlspecialchars(trim($postData['direccion'] ?? ''), ENT_QUOTES, 'UTF-8'),
        ];

        $existing = $this->queryBuilder->select('ubicacion', ['refugio_id' => $userId]);

        if (!empty($existing)) {
            $this->queryBuilder->update('ubicacion', $fields, ['refugio_id' => $userId]);
        } else {
            $this->queryBuilder->insert('ubicacion', $fields);
        }

        return $errores;
    }

    private function procesarFotoPerfil(int $userId, array $postData, ?array $archivo, array &$fieldsUsuario): array
    {
        $errores = [];
        $eliminarFoto = ($postData['eliminar_foto'] ?? '0') === '1';

        if ($eliminarFoto) {
            $currentUser = $this->findById($userId);
            if ($currentUser && !empty($currentUser['foto_perfil'])) {
                GCSHelper::borrar($currentUser['foto_perfil']);
            }
            $fieldsUsuario['foto_perfil'] = null;

        } else {
            if ($archivo) {
                if ($archivo['error'] === UPLOAD_ERR_INI_SIZE || $archivo['error'] === UPLOAD_ERR_FORM_SIZE) {
                    $errores['foto_perfil_o_logo'] = 'La imagen supera el límite máximo permitido por el servidor.';
                    return $errores;
                }

                if ($archivo['error'] === UPLOAD_ERR_OK) {
                    $maxBytes = 2 * 1024 * 1024; // 2 MB
                    if ($archivo['size'] > $maxBytes) {
                        $errores['foto_perfil_o_logo'] = 'La imagen no puede superar los 2 MB.';
                        return $errores;
                    }

                    $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));

                    // Validar MIME
                    $esImagenValida = false;
                    if (file_exists($archivo['tmp_name'])) {
                        $mime = mime_content_type($archivo['tmp_name']);
                        $info = getimagesize($archivo['tmp_name']);
                        if ($info !== false && strpos($mime, 'image/') === 0) {
                            $esImagenValida = true;
                        }
                    }

                    if (!$esImagenValida || !in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $errores['foto_perfil_o_logo'] = 'El archivo subido no es una imagen válida o su formato no es compatible.';
                        return $errores;
                    }

                    try {
                        // Borrar foto anterior si existe
                        $currentUser = $this->findById($userId);
                        if ($currentUser && !empty($currentUser['foto_perfil'])) {
                            GCSHelper::borrar($currentUser['foto_perfil']);
                        }

                        // Subir nueva foto
                        $url = GCSHelper::subir($archivo, 'perfil');
                        $fieldsUsuario['foto_perfil'] = $url;

                    } catch (\Exception $e) {
                        if ($this->logger) {
                            $this->logger->error('[GCS] Error al subir foto de perfil', ['userId' => $userId, 'error' => $e->getMessage()]);
                        }
                        $errores['foto_perfil_o_logo'] = 'No se pudo guardar la imagen de perfil. Por favor, intentá de nuevo más tarde.';
                    }
                } elseif ($archivo['error'] !== UPLOAD_ERR_NO_FILE) {
                    $errores['foto_perfil_o_logo'] = 'Ocurrió un error al subir la imagen (Código: ' . $archivo['error'] . ').';
                }
            }
        }

        return $errores;
    }
}