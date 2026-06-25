<?php

namespace Paw\App\Models;

use Paw\Core\Model;

class Refugio extends Model
{
    public $table = 'refugio';
    public $fields = [
        'usuario_id' => null,
        'ubicacion_id' => null,
        'nombre_institucion' => '',
        'cuit' => '',
        'cvu' => null,
        'alias' => null,
        'imagen' => 'default-refugio.jpg',
        'telefono' => '',
        'email' => null,
        'ciudad' => null,
        'provincia' => null,
        'direccion' => null,
        'descripcion' => null,
        'adoptables_disponibles' => 0,
    ];

    public function set(array $values)
    {
        foreach (array_keys($this->fields) as $field) {
            if (!isset($values[$field])) {
                continue;
            }
            $this->fields[$field] = $values[$field];
        }
    }

    public function load($id)
    {
        if (!is_numeric($id) || $id < 0) {
            throw new \Exception("El ID del refugio debe ser un entero mayor a 0");
        }

        $sql = "SELECT r.*, u.ciudad, u.provincia, u.direccion 
                FROM refugio r 
                LEFT JOIN ubicacion u ON r.usuario_id = u.refugio_id 
                WHERE r.usuario_id = :id";
        
        $stmt = $this->queryBuilder->getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($record) {
            $this->set($record);
        } else {
            throw new \Exception("No se encontró un refugio con el ID proporcionado");
        }
    }

    public function getId(): int
    {
        return (int) $this->fields['usuario_id'];
    }

    public function getNombre(): string
    {
        return $this->fields['nombre_institucion'];
    }

    public function getAlias(): ?string
    {
        return $this->fields['alias'];
    }

    public function getDescripcion(): ?string
    {
        return $this->fields['descripcion'] ?? 'Este refugio aún no tiene una descripción detallada, pero trabaja arduamente día a día para rescatar y cuidar a los animales que más lo necesitan.';
    }

    public function getCvu(): ?string
    {
        return $this->fields['cvu'];
    }

    public function getEmail(): ?string
    {
        return $this->fields['email'];
    }

    public function getEncuestas(): array
    {
        $id = $this->getId();
        $sqlEncuestas = "
            SELECT e.*, m.nombre as mascota_nombre, COALESCE(NULLIF(TRIM(CONCAT(a.nombre, ' ', a.apellido)), ''), u.nombre_usuario) as adoptante_nombre, u.contacto as adoptante_contacto 
            FROM encuesta_adopcion e 
            JOIN mascota m ON e.mascota_id = m.id 
            JOIN usuario u ON e.adoptante_id = u.id 
            LEFT JOIN adoptante a ON a.usuario_id = u.id
            WHERE m.refugio_id = :rid 
            ORDER BY e.fecha_encuesta DESC
        ";
        return $this->queryBuilder->rawQuery($sqlEncuestas, [':rid' => $id]);
    }

    public function getFotosSeguimiento(): array
    {
        $id = $this->getId();
        $sqlFotos = "
            SELECT 
                md.id, md.tipo, md.url, 
                m.id as mascota_id, m.nombre as mascota_nombre, COALESCE(NULLIF(TRIM(CONCAT(a.nombre, ' ', a.apellido)), ''), u.nombre_usuario) as adoptante_nombre
            FROM media_mascota md 
            JOIN mascota m ON md.mascota_id = m.id 
            LEFT JOIN solicitud_de_adopcion s ON s.mascota_id = m.id AND s.estado = 'APROBADA'
            LEFT JOIN usuario u ON s.adoptante_id = u.id
            LEFT JOIN adoptante a ON a.usuario_id = u.id
            WHERE m.refugio_id = :rid AND md.tipo IN ('foto_seguimiento', 'certificado_med', 'certificado_vac')

            UNION ALL

            SELECT 
                rs.id, CASE WHEN LOWER(rs.tipo) = 'vacuna' THEN 'certificado_vac' ELSE 'certificado_med' END as tipo, rs.archivo_adjunto as url,
                m.id as mascota_id, m.nombre as mascota_nombre, COALESCE(NULLIF(TRIM(CONCAT(a.nombre, ' ', a.apellido)), ''), u.nombre_usuario) as adoptante_nombre
            FROM registro_sanitario rs
            JOIN mascota m ON rs.mascota_id = m.id 
            LEFT JOIN solicitud_de_adopcion s ON s.mascota_id = m.id AND s.estado = 'APROBADA'
            LEFT JOIN usuario u ON s.adoptante_id = u.id
            LEFT JOIN adoptante a ON a.usuario_id = u.id
            WHERE m.refugio_id = :rid AND rs.archivo_adjunto IS NOT NULL

            ORDER BY id DESC
        ";
        return $this->queryBuilder->rawQuery($sqlFotos, [':rid' => $id]);
    }

    public function getSeguimientoAgrupado(): array
    {
        $encuestas = $this->getEncuestas();
        $fotosSeguimiento = $this->getFotosSeguimiento();
        
        $seguimientoAgrupado = [];
        foreach ($encuestas as $enc) {
            $mId = $enc['mascota_id'];
            if (!isset($seguimientoAgrupado[$mId])) {
                $seguimientoAgrupado[$mId] = [
                    'mascota_nombre' => $enc['mascota_nombre'],
                    'adoptante_nombre' => $enc['adoptante_nombre'] ?? 'Desconocido',
                    'encuestas' => [],
                    'fotos' => []
                ];
            }
            $seguimientoAgrupado[$mId]['encuestas'][] = $enc;
        }
        
        foreach ($fotosSeguimiento as $foto) {
            $mId = $foto['mascota_id'];
            if (!isset($seguimientoAgrupado[$mId])) {
                $seguimientoAgrupado[$mId] = [
                    'mascota_nombre' => $foto['mascota_nombre'],
                    'adoptante_nombre' => $foto['adoptante_nombre'] ?? 'Desconocido',
                    'encuestas' => [],
                    'fotos' => []
                ];
            }
            $seguimientoAgrupado[$mId]['fotos'][] = $foto;
        }
        
        return $seguimientoAgrupado;
    }
}
