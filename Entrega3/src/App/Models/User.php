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
    ];

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
}