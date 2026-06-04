<?php

namespace Paw\App\Models;
use Paw\Core\Model;
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

        if (count($errores) > 0) {
            return $errores;
        }

        $fieldsUsuario = [
            'nombre_usuario' => htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'),
            'email'          => filter_var($email, FILTER_SANITIZE_EMAIL),
            'contacto'       => htmlspecialchars(trim($postData['contacto'] ?? ''), ENT_QUOTES, 'UTF-8'),
        ];

        $newPassword = $postData['contrasena'] ?? '';
        if (!empty($newPassword)) {
            $fieldsUsuario['contrasena'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $eliminarFoto = ($postData['eliminar_foto'] ?? '0') === '1';
        $pathFisicoBase = __DIR__ . '/../../../public/assets/img/';
        
        if ($eliminarFoto) {
            $currentUser = $this->findById($userId);
            if ($currentUser && !empty($currentUser['foto_perfil'])) {
                $pathFisico = $pathFisicoBase . $currentUser['foto_perfil'];
                if (file_exists($pathFisico) && is_file($pathFisico)) {
                    unlink($pathFisico);
                }
            }
            $fieldsUsuario['foto_perfil'] = null;
        } else {
            if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
                $extension = pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION);
                if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $errores['foto_perfil_o_logo'] = 'La imagen debe ser JPG, PNG, WEBP o GIF.';
                    return $errores;
                }
                
                $nombreFinal = uniqid('perfil_', true) . '.' . $extension;
                $directorioDestino = $pathFisicoBase . 'uploads/';
                
                if (!is_dir($directorioDestino)) {
                    mkdir($directorioDestino, 0777, true);
                }

                $rutaAbsoluta = $directorioDestino . $nombreFinal;
                $rutaRelativa = 'uploads/' . $nombreFinal;

                if (move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
                    $currentUser = $this->findById($userId);
                    if ($currentUser && !empty($currentUser['foto_perfil'])) {
                        $pathFisico = $pathFisicoBase . $currentUser['foto_perfil'];
                        if (file_exists($pathFisico) && is_file($pathFisico)) {
                            unlink($pathFisico);
                        }
                    }
                    $fieldsUsuario['foto_perfil'] = $rutaRelativa;
                } else {
                    $errores['foto_perfil_o_logo'] = 'No se pudo guardar la imagen de perfil.';
                    return $errores;
                }
            }
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
}