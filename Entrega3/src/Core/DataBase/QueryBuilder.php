<?php

namespace Paw\Core\Database;

use PDO;
use PDOStatement;
use Monolog\Logger;

class QueryBuilder
{
    protected PDO $connection;
    protected ?Logger $log;
    protected $pdo;

    public function __construct(PDO $connection, ?Logger $log = null)
    {
        $this->pdo = $connection;
        $this->log = $log;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function select(string $table, array $conditions = [], array $precios = [], ?int $limit = null, ?int $offset = null): array
    {
        [$where, $binds] = $this->buildWhere($conditions, 'AND', $precios);
        
        $query = "SELECT * FROM {$table} WHERE {$where}";
        $query = $this->addPagination($query, $limit);

        $sentencia = $this->pdo->prepare($query);
        $this->bindValues($sentencia, $binds);
        $this->bindPagination($sentencia, $limit, $offset);
        $sentencia->execute();

        return $sentencia->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(string $table, array $conditions = [], array $precios = []): array
    {
        [$where, $binds] = $this->buildWhere($conditions, 'AND', $precios);
        
        $query = "SELECT COUNT(*) as total FROM {$table} WHERE {$where}";
            
        $sentencia = $this->pdo->prepare($query);
        $this->bindValues($sentencia, $binds);
        $sentencia->execute();
        $result = $sentencia->fetch(PDO::FETCH_ASSOC);

        return $result ?: ['total' => 0];
    }

    public function obtenerMascotasFiltradas(array $filtros, bool $esConteo = false, ?int $limite = null, ?int $offset = null)
    {
        $sql = $esConteo
            ? "SELECT COUNT(*) FROM mascota m WHERE m.estado_adopcion = 'DISPONIBLE'"
            : "SELECT m.* FROM mascota m WHERE m.estado_adopcion = 'DISPONIBLE'";

        $binds = [];

        if (!empty($filtros['especie'])) {
            $sql .= " AND m.especie = :especie";
            $binds[':especie'] = $filtros['especie'];
        }

        if (!empty($filtros['tamano'])) {
            $sql .= " AND m.tamano = :tamano";
            $binds[':tamano'] = $filtros['tamano'];
        }

        if (!empty($filtros['sexo'])) {
            $sql .= " AND m.sexo = :sexo";
            $binds[':sexo'] = $filtros['sexo'];
        }

        if (!empty($filtros['edad_min'])) {
            $sql .= " AND m.edad >= :emin";
            $binds[':emin'] = $filtros['edad_min'];
        }

        if (!empty($filtros['edad_max'])) {
            $sql .= " AND m.edad <= :emax";
            $binds[':emax'] = $filtros['edad_max'];
        }

        if (!empty($filtros['provincia'])) {
            $sql .= " AND EXISTS (SELECT 1 FROM ubicacion u WHERE u.refugio_id = m.refugio_id AND u.provincia = :provincia)";
            $binds[':provincia'] = $filtros['provincia'];
        }

        if (!empty($filtros['ciudad'])) {
            $sql .= " AND EXISTS (SELECT 1 FROM ubicacion u WHERE u.refugio_id = m.refugio_id AND u.ciudad = :ciudad)";
            $binds[':ciudad'] = $filtros['ciudad'];
        }

        if (!empty($filtros['temperamento'])) {
            $sql .= " AND m.temperamento = :temperamento";
            $binds[':temperamento'] = $filtros['temperamento'];
        }

        if (!empty($filtros['refugio_id'])) {
            $sql .= " AND m.refugio_id = :refugio_id";
            $binds[':refugio_id'] = $filtros['refugio_id'];
        }

        if (!empty($filtros['ubicacion'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM ubicacion u 
                WHERE u.refugio_id = m.refugio_id 
                AND (u.ciudad ILIKE :ubicacion OR u.provincia ILIKE :ubicacion)
            )";
            $binds[':ubicacion'] = '%' . $filtros['ubicacion'] . '%';
        }

        if (!empty($filtros['lat_usuario']) && !empty($filtros['lng_usuario'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM ubicacion u 
                WHERE u.refugio_id = m.refugio_id 
                AND (SQRT(POWER(u.latitud - :lat, 2) + POWER(u.longitud - :lng, 2)) * 111.32) <= 50
            )";
            $binds[':lat'] = (float)$filtros['lat_usuario'];
            $binds[':lng'] = (float)$filtros['lng_usuario'];
        }

        if ($esConteo) {
            return (int) $this->rawQueryValue($sql, $binds);
        }

        if ($limite !== null && $offset !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $binds[':limit'] = ['value' => $limite, 'type' => PDO::PARAM_INT];
            $binds[':offset'] = ['value' => $offset, 'type' => PDO::PARAM_INT];
        }

        return $this->rawQuery($sql, $binds);
    }

    private function buildWhere(array $params, string $operator = 'AND', array $precios = [], string $table = ''): array
    {
        $conditions = [];
        $binds = [];

        $edadMin = $params['edad_min'] ?? null;
        $edadMax = $params['edad_max'] ?? null;
        $provincia = $params['provincia'] ?? null;
        $ciudad = $params['ciudad'] ?? null;

        unset($params['edad_min'], $params['edad_max'], $params['ubicacion'], $params['page'], $params['provincia'], $params['ciudad']);

        foreach ($params as $column => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }

            $conditions[] = "{$column} = :{$column}";
            $binds[":{$column}"] = $value;
        }

        if ($edadMin !== null && $edadMin !== '') {
            $conditions[] = "edad >= :emin";
            $binds[':emin'] = $edadMin;
        }

        if ($edadMax !== null && $edadMax !== '') {
            $conditions[] = "edad <= :emax";
            $binds[':emax'] = $edadMax;
        }

        if ($table === 'mascota') {
            if (!empty($provincia)) {
                $conditions[] = "EXISTS (SELECT 1 FROM ubicacion u WHERE u.refugio_id = mascota.refugio_id AND u.provincia = :provincia)";
                $binds[':provincia'] = $provincia;
            }

            if (!empty($ciudad)) {
                $conditions[] = "EXISTS (SELECT 1 FROM ubicacion u WHERE u.refugio_id = mascota.refugio_id AND u.ciudad = :ciudad)";
                $binds[':ciudad'] = $ciudad;
            }
        }

        $where = !empty($conditions) ? implode(" {$operator} ", $conditions) : "1=1";

        return [$where, $binds];
    }

    private function bindValues(\PDOStatement $sentencia, array $binds): void
    {
        foreach ($binds as $key => $val) {
            if (is_array($val) && isset($val['value'], $val['type'])) {
                $sentencia->bindValue($key, $val['value'], $val['type']);
            } elseif (is_bool($val)) {
                $sentencia->bindValue($key, $val, \PDO::PARAM_BOOL);
            } elseif (is_null($val)) {
                $sentencia->bindValue($key, $val, \PDO::PARAM_NULL);
            } else {
                $sentencia->bindValue($key, $val);
            }
        }
    }

    public function rawQuery(string $sql, array $binds = []): array
    {
        $sentencia = $this->pdo->prepare($sql);
        $this->bindValues($sentencia, $binds);
        $sentencia->execute();
        return $sentencia->fetchAll(PDO::FETCH_ASSOC);
    }

    public function rawQueryValue(string $sql, array $binds = [])
    {
        $sentencia = $this->pdo->prepare($sql);
        $this->bindValues($sentencia, $binds);
        $sentencia->execute();
        return $sentencia->fetchColumn();
    }

    public function rawExecute(string $sql, array $binds = []): void
    {
        $sentencia = $this->pdo->prepare($sql);
        $this->bindValues($sentencia, $binds);
        $sentencia->execute();
    }

    public function incrementarVisitas(string $table, int $id): void
    {
        $sql = "UPDATE {$table} SET visitas = visitas + 1 WHERE id = :id";
        $this->rawExecute($sql, ['id' => $id]);
    }

    public function getMascotasInvisibles(string $table, int $limite): array
    {
        $sql = "
            SELECT m.*, ((CURRENT_DATE - m.fecha_publicacion::date) / (m.visitas + 1.0)) AS puntaje_invisibilidad
            FROM {$table} m WHERE m.estado_adopcion = 'DISPONIBLE'
            ORDER BY puntaje_invisibilidad DESC, m.visitas ASC, m.fecha_publicacion ASC
            LIMIT :limite
        ";
        
        return $this->rawQuery($sql, ['limite' => $limite]);
    }

    private function addPagination(string $query, ?int $limit): string
    {
        if (is_null($limit) || $limit <= 0) {
            return $query;
        }

        return "{$query} LIMIT :limit OFFSET :offset";
    }

    private function bindPagination(PDOStatement $sentencia, ?int $limit, ?int $offset): void
    {
        if (is_null($limit) || $limit <= 0) {
            return;
        }

        $offset = $offset ?? 0;

        $sentencia->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sentencia->bindValue(':offset', $offset, PDO::PARAM_INT);
    }

    public function obtenerValoresUnicos(string $tabla, string $campo): array
    {
        $sql = "SELECT DISTINCT LOWER(TRIM({$campo})) AS {$campo} FROM {$tabla} WHERE {$campo} IS NOT NULL AND TRIM({$campo}) != '' ORDER BY {$campo}";
        return $this->rawQuery($sql);
    }

    public function buscarMascotasPorTermino(string $tabla, string $termino, bool $esConteo = false, ?int $limite = null, ?int $offset = null)
    {
        $select = $esConteo ? "COUNT(*)" : "*";
        $sql = "SELECT {$select} FROM {$tabla} WHERE estado_adopcion = 'DISPONIBLE' 
                AND (nombre ILIKE :term1 OR especie ILIKE :term2 OR descripcion ILIKE :term3)";
        
        if (!$esConteo && $limite !== null && $offset !== null) {
            $sql .= " LIMIT :limite OFFSET :offset";
        }

        $terminoLike = "%{$termino}%";
        $binds = [
            ':term1' => $terminoLike,
            ':term2' => $terminoLike,
            ':term3' => $terminoLike
        ];
        
        if (!$esConteo && $limite !== null && $offset !== null) {
            $binds[':limite'] = ['value' => $limite, 'type' => \PDO::PARAM_INT];
            $binds[':offset'] = ['value' => $offset, 'type' => \PDO::PARAM_INT];
        }

        if ($esConteo) {
            return (int) $this->rawQueryValue($sql, $binds);
        } else {
            return $this->rawQuery($sql, $binds);
        }
    }

    public function buscarRefugiosPorTermino(string $tabla, string $termino, bool $esConteo = false, ?int $limite = null, ?int $offset = null)
    {
        if ($esConteo) {
            $select = "COUNT(*)";
        } else {
            $select = "*, (SELECT COUNT(*) FROM mascota m WHERE m.refugio_id = {$tabla}.usuario_id AND m.estado_adopcion = 'DISPONIBLE') as adoptables_disponibles";
        }
        $sql = "SELECT {$select} FROM {$tabla} WHERE nombre_institucion ILIKE :term1 OR descripcion ILIKE :term2 OR alias ILIKE :term3";
        
        if (!$esConteo && $limite !== null && $offset !== null) {
            $sql .= " LIMIT :limite OFFSET :offset";
        }

        $term = "%{$termino}%";
        $binds = [
            ':term1' => $term,
            ':term2' => $term,
            ':term3' => $term
        ];

        if (!$esConteo && $limite !== null && $offset !== null) {
            $binds[':limite'] = ['value' => $limite, 'type' => \PDO::PARAM_INT];
            $binds[':offset'] = ['value' => $offset, 'type' => \PDO::PARAM_INT];
        }

        if ($esConteo) {
            return (int) $this->rawQueryValue($sql, $binds);
        } else {
            return $this->rawQuery($sql, $binds);
        }
    }

    public function obtenerUbicacionUnicaRefugio(string $tabla, string $campo): array
    {
        $sql = "SELECT DISTINCT u.{$campo} FROM ubicacion u 
                INNER JOIN {$tabla} r ON u.refugio_id = r.usuario_id
                WHERE u.{$campo} IS NOT NULL AND u.{$campo} != '' 
                ORDER BY u.{$campo} ASC";
        return $this->rawQuery($sql);
    }

    public function obtenerRefugiosFiltrados(string $tabla, array $filtros, bool $esConteo = false, ?int $limite = null, ?int $offset = null)
    {
        $sqlFiltros = "";
        $params = [];

        if (!empty($filtros['provincia'])) {
            $sqlFiltros .= " AND EXISTS (SELECT 1 FROM ubicacion u2 WHERE u2.refugio_id = r.usuario_id AND u2.provincia = :provincia)";
            $params[':provincia'] = $filtros['provincia'];
        }

        if (!empty($filtros['ciudad'])) {
            $sqlFiltros .= " AND EXISTS (SELECT 1 FROM ubicacion u2 WHERE u2.refugio_id = r.usuario_id AND u2.ciudad = :ciudad)";
            $params[':ciudad'] = $filtros['ciudad'];
        }

        if ($esConteo) {
            $sql = "SELECT COUNT(*) FROM {$tabla} r WHERE 1=1 " . $sqlFiltros;
            return (int) $this->rawQueryValue($sql, $params);
        }

        $sql = "SELECT r.usuario_id, r.nombre_institucion, r.cuit, r.imagen, r.telefono,
                       STRING_AGG(DISTINCT u.ciudad, ', ' ORDER BY u.ciudad ASC) as ciudad,
                       STRING_AGG(DISTINCT u.provincia, ', ' ORDER BY u.provincia ASC) as provincia,
                       (SELECT COUNT(*) FROM mascota m WHERE m.refugio_id = r.usuario_id AND m.estado_adopcion = 'DISPONIBLE') as adoptables_disponibles
                FROM {$tabla} r LEFT JOIN ubicacion u ON r.usuario_id = u.refugio_id WHERE 1=1 " . $sqlFiltros . "
                GROUP BY r.usuario_id, r.nombre_institucion, r.cuit, r.imagen, r.telefono
                ORDER BY r.nombre_institucion ASC 
                LIMIT :limit OFFSET :offset";

        if ($limite !== null && $offset !== null) {
            $params[':limit'] = ['value' => $limite, 'type' => \PDO::PARAM_INT];
            $params[':offset'] = ['value' => $offset, 'type' => \PDO::PARAM_INT];
        }

        return $this->rawQuery($sql, $params);
    }

    public function obtenerRegistrosSanitarios(int $mascotaId, array $filtros = []): array
    {
        $sql = "SELECT * FROM registro_sanitario WHERE mascota_id = :mascota_id";
        $binds = [':mascota_id' => $mascotaId];

        if (!empty($filtros['categoria']) && strtolower($filtros['categoria']) !== 'todos') {
            $sql .= " AND tipo = :tipo";
            $binds[':tipo'] = $filtros['categoria'];
        }

        if (!empty($filtros['mes']) && strtolower($filtros['mes']) !== 'todos') {
            $sql .= " AND EXTRACT(MONTH FROM fecha_programada) = :mes";
            $binds[':mes'] = $filtros['mes'];
        }

        if (!empty($filtros['anio']) && strtolower($filtros['anio']) !== 'todos') {
            $sql .= " AND EXTRACT(YEAR FROM fecha_programada) = :anio";
            $binds[':anio'] = $filtros['anio'];
        }

        $sql .= " ORDER BY fecha_programada DESC";

        return $this->rawQuery($sql, $binds);
    }

    public function obtenerSolicitudesPorAdoptante(string $tabla, int $adoptanteId): array
    {
        $sql = "SELECT s.id, s.estado, m.nombre, m.nombre as mascota_nombre, m.edad, m.tamano, m.temperamento, r.nombre_institucion as refugio_nombre, u.foto_perfil as refugio_foto
                FROM {$tabla} s
                JOIN mascota m ON s.mascota_id = m.id
                LEFT JOIN refugio r ON m.refugio_id = r.usuario_id
                LEFT JOIN usuario u ON r.usuario_id = u.id
                WHERE s.adoptante_id = :adoptante_id";
                
        return $this->rawQuery($sql, [':adoptante_id' => $adoptanteId]);
    }

    public function obtenerAdopcionesPorAdoptante(string $tabla, int $adoptanteId): array
    {
        $sql = "SELECT m.id, m.nombre, m.edad, m.tamano, m.temperamento
                FROM {$tabla} s
                JOIN mascota m ON s.mascota_id = m.id
                WHERE s.adoptante_id = :adoptante_id AND s.estado = 'APROBADA'";
                
        return $this->rawQuery($sql, [':adoptante_id' => $adoptanteId]);
    }

    public function obtenerFavoritosPorAdoptante(string $tabla, int $adoptanteId): array
    {
        $sql = "SELECT f.id AS favorito_id, m.*
                FROM {$tabla} f
                INNER JOIN mascota m ON m.id = f.mascota_id
                WHERE f.adoptante_id = :adoptante_id
                  AND m.estado_adopcion = 'DISPONIBLE'
                ORDER BY f.id DESC";

        return $this->rawQuery($sql, [':adoptante_id' => $adoptanteId]);
    }

    public function eliminarFavorito(string $tabla, int $favoritoId, int $adoptanteId): bool
    {
        $sql = "DELETE FROM {$tabla} WHERE id = :id AND adoptante_id = :adoptante_id";
        $sentencia = $this->pdo->prepare($sql);
        $sentencia->bindValue(':id', $favoritoId, PDO::PARAM_INT);
        $sentencia->bindValue(':adoptante_id', $adoptanteId, PDO::PARAM_INT);
        $sentencia->execute();

        return $sentencia->rowCount() > 0;
    }

    public function obtenerMensajesPorSolicitud(int $solicitudId): array
    {
        $sql = "SELECT m.*, r.nombre_usuario as remitente_nombre, r.rol as remitente_rol
                FROM mensaje m
                JOIN usuario r ON m.remitente_id = r.id
                WHERE m.solicitud_id = :solicitud_id
                ORDER BY m.fecha_envio ASC";

        return $this->rawQuery($sql, [':solicitud_id' => $solicitudId]);
    }

    public function contarMensajesNoLeidos(int $usuarioId): int
    {
        $sql = "SELECT COUNT(*) as count FROM mensaje WHERE destinatario_id = :destinatario_id AND leido = false";
        return (int) $this->rawQueryValue($sql, [':destinatario_id' => $usuarioId]);
    }

    public function contarMensajesNoLeidosPorSolicitud(int $solicitudId, int $usuarioId): int
    {
        $sql = "SELECT COUNT(*) as count FROM mensaje WHERE solicitud_id = :solicitud_id AND destinatario_id = :destinatario_id AND leido = false";
        return (int) $this->rawQueryValue($sql, [
            ':solicitud_id' => $solicitudId,
            ':destinatario_id' => $usuarioId,
        ]);
    }

    public function obtenerMascotasDisponiblesConUbicacion(): array
    {
        $sql = "SELECT m.id, m.nombre, m.imagen, m.edad, m.tamano, m.temperamento, m.especie, m.refugio_id,
                       u.provincia, u.ciudad
                FROM mascota m
                LEFT JOIN ubicacion u ON m.refugio_id = u.refugio_id
                WHERE m.estado_adopcion = 'DISPONIBLE'";

        return $this->rawQuery($sql);
    }

    public function obtenerRefugioConUbicacion(int $id): array|false
    {
        $sql = "SELECT r.*, u.ciudad, u.provincia, u.direccion
                FROM refugio r
                LEFT JOIN ubicacion u ON r.usuario_id = u.refugio_id
                WHERE r.usuario_id = :id";

        $resultados = $this->rawQuery($sql, [':id' => $id]);
        return $resultados[0] ?? false;
    }

    public function obtenerEncuestasPorRefugio(int $refugioId): array
    {
        $sql = "SELECT e.*, m.nombre as mascota_nombre, COALESCE(NULLIF(TRIM(CONCAT(a.nombre, ' ', a.apellido)), ''), u.nombre_usuario) as adoptante_nombre, u.contacto as adoptante_contacto
                FROM encuesta_adopcion e
                JOIN mascota m ON e.mascota_id = m.id
                JOIN usuario u ON e.adoptante_id = u.id
                LEFT JOIN adoptante a ON a.usuario_id = u.id
                WHERE m.refugio_id = :rid
                ORDER BY e.fecha_encuesta DESC";

        return $this->rawQuery($sql, [':rid' => $refugioId]);
    }

    public function obtenerFotosSeguimientoPorRefugio(int $refugioId): array
    {
        $sql = "SELECT
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

                ORDER BY id DESC";

        return $this->rawQuery($sql, [':rid' => $refugioId]);
    }

    public function obtenerTodosRefugiosConAdoptables(string $tabla): array
    {
        $sql = "SELECT r.*, u.ciudad, u.provincia,
                       COALESCE(md.adoptables, 0) as adoptables_disponibles
                FROM {$tabla} r
                LEFT JOIN ubicacion u ON r.usuario_id = u.refugio_id
                LEFT JOIN (
                    SELECT refugio_id, COUNT(id) as adoptables
                    FROM mascota
                    WHERE estado_adopcion = 'DISPONIBLE'
                    GROUP BY refugio_id
                ) md ON r.usuario_id = md.refugio_id
                ORDER BY r.nombre_institucion ASC";

        return $this->rawQuery($sql);
    }

    public function obtenerPreguntasTestCompatibilidad(): array
    {
        $sql = "SELECT p.nombre AS pregunta_nombre, p.titulo AS pregunta_titulo, o.valor, o.etiqueta, o.subtitulo, o.emoji
                FROM test_compatibilidad_pregunta p
                LEFT JOIN test_compatibilidad_opcion o ON p.id = o.pregunta_id
                ORDER BY p.orden, o.orden";

        return $this->rawQuery($sql);
    }

    /**
     * Inserta un registro en la tabla indicada.
     * @return string|false El ID del registro insertado o false si falla.
     */
    public function insert(string $table, array $data): string|false
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($col) => ":{$col}", array_keys($data)));
        
        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $sentencia = $this->pdo->prepare($query);
        
        $binds = [];
        foreach ($data as $key => $value) {
            $binds[":{$key}"] = $value;
        }
        $this->bindValues($sentencia, $binds);
        
        $sentencia->execute();
        return $this->pdo->lastInsertId();
    }

    /**
     * Actualiza registros en la tabla indicada.
     * @param string $table    Nombre de la tabla.
     * @param array  $data     Columnas y valores a actualizar.
     * @param array  $conditions Condiciones para la cláusula WHERE (AND implícito).
     * @return int Número de filas afectadas.
     */
    public function update(string $table, array $data, array $conditions): int
    {
        $setParts  = [];
        $binds     = [];

        foreach ($data as $column => $value) {
            $setParts[]                 = "{$column} = :set_{$column}";
            $binds[":set_{$column}"]    = $value;
        }

        $whereParts = [];
        foreach ($conditions as $column => $value) {
            $whereParts[]               = "{$column} = :where_{$column}";
            $binds[":where_{$column}"]  = $value;
        }

        $setClause   = implode(', ', $setParts);
        $whereClause = implode(' AND ', $whereParts);

        $query = "UPDATE {$table} SET {$setClause} WHERE {$whereClause}";

        $sentencia = $this->pdo->prepare($query);
        $this->bindValues($sentencia, $binds);
        $sentencia->execute();

        return $sentencia->rowCount();
    }

    /**
     * Retorna un solo registro que coincida con las condiciones exactas.
     */
    public function selectOne(string $table, array $conditions = []): array|false
    {
        $where = [];
        $binds = [];
        
        foreach ($conditions as $column => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }
            $where[] = "{$column} = :{$column}";
            $binds[":{$column}"] = $value;
        }
        
        $whereClause = !empty($where) ? implode(' AND ', $where) : '1=1';
        $query = "SELECT * FROM {$table} WHERE {$whereClause} LIMIT 1";
        
        $sentencia = $this->pdo->prepare($query);
        foreach ($binds as $key => $val) {
            $sentencia->bindValue($key, $val);
        }
        
        $sentencia->execute();
        return $sentencia->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si existe un registro que coincida con las condiciones.
     */
    public function exists(string $table, array $conditions): bool
    {
        return $this->selectOne($table, $conditions) !== false;
    }

    public function actualizarArchivoRegistroSanitario(int $registroId, string $rutaRelativa, string $fecha): void
    {
        $sql = "UPDATE registro_sanitario SET archivo_adjunto = :ruta, estado = 'COMPLETADO', fecha_realizada = :fecha WHERE id = :id";
        $this->rawQuery($sql, [
            ':ruta' => $rutaRelativa,
            ':fecha' => $fecha,
            ':id' => $registroId
        ]);
    }

    public function obtenerRegistrosPendientesCercanos(): array
    {
        $sql = "SELECT rs.id as registro_id, rs.titulo, rs.fecha_programada, rs.mascota_id, 
                       m.nombre as mascota_nombre, a.usuario_id as adoptante_id, 
                       u.email as adoptante_email, u.nombre_usuario as adoptante_nombre
                FROM registro_sanitario rs
                JOIN mascota m ON rs.mascota_id = m.id
                JOIN solicitud_de_adopcion s ON m.id = s.mascota_id AND s.estado = 'APROBADA'
                JOIN adoptante a ON s.adoptante_id = a.usuario_id
                JOIN usuario u ON a.usuario_id = u.id
                WHERE rs.estado = 'PENDIENTE' 
                  AND (rs.notificado = FALSE OR rs.notificado IS NULL)
                  AND rs.fecha_programada BETWEEN CURRENT_DATE AND (CURRENT_DATE + INTERVAL '7 days')";
        return $this->rawQuery($sql);
    }

    public function marcarRegistroNotificado(int $id): void
    {
        $updateSql = "UPDATE registro_sanitario SET notificado = TRUE WHERE id = :id";
        $this->rawQuery($updateSql, [':id' => $id]);
    }

    /**
     * Lógica de automatización: Aprueba una solicitud y cambia el estado de la mascota
     * asumiendo la fecha de adopción con el timestamp actual de la base de datos.
     */
    public function aprobarSolicitudAdopcion(int $solicitudId, int $mascotaId): void
    {
        $this->pdo->beginTransaction();
        try {
            // Aprobar la solicitud
            $sqlSolicitud = "UPDATE solicitud_de_adopcion SET estado = 'APROBADO' WHERE id = :id";
            $this->rawQuery($sqlSolicitud, [':id' => $solicitudId]);

            // Cambiar estado de mascota y asentar la fecha de adopción
            $sqlMascota = "UPDATE mascota SET estado_adopcion = 'ADOPTADO', fecha_adopcion = CURRENT_TIMESTAMP WHERE id = :id";
            $this->rawQuery($sqlMascota, [':id' => $mascotaId]);

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function procesarSolicitudAdopcion(string $tabla, int $solicitudId, string $nuevoEstado, ?int $mascotaId, string $fecha): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->update($tabla, [
                'estado' => $nuevoEstado,
                'fecha_aceptacion' => $fecha
            ], ['id' => $solicitudId]);

            if ($mascotaId !== null) {
                $this->update('mascota', [
                    'estado_adopcion' => 'ADOPTADO',
                    'fecha_adopcion' => $fecha
                ], ['id' => $mascotaId]);

                // Rechazar automaticamente las demas solicitudes pendientes de la misma mascota
                $sql = "UPDATE {$tabla} SET estado = 'RECHAZADA', fecha_aceptacion = :fecha WHERE mascota_id = :mascota_id AND id != :solicitud_id AND estado = 'PENDIENTE'";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':fecha' => $fecha,
                    ':mascota_id' => $mascotaId,
                    ':solicitud_id' => $solicitudId
                ]);
            }

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function selectCompatibles(string $table, array $filtros): array
    {
        $conditions = [];
        $binds = [];

        foreach ($filtros as $column => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $valid = array_values(array_filter($value, fn($v) => !is_null($v) && $v !== ''));
                if (empty($valid)) continue;

                $keys = [];
                foreach ($valid as $i => $v) {
                    $key = ":{$column}_{$i}";
                    $keys[] = $key;
                    $binds[$key] = $v;
                }
                $conditions[] = "{$column} IN (" . implode(', ', $keys) . ")";
                
            } else {
                $conditions[] = "{$column} = :{$column}";
                $binds[":{$column}"] = $value;
            }
        }

        $where = !empty($conditions) ? implode(' AND ', $conditions) : '1=1';
        $sql = "SELECT * FROM {$table} WHERE {$where}";

        $sentencia = $this->pdo->prepare($sql);
        foreach ($binds as $key => $val) {
            $sentencia->bindValue($key, $val);
        }
        
        $sentencia->execute();

        return $sentencia->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function obtenerUbicacionesPorRefugio(int $refugioId): array
    {
        $sql = "SELECT ciudad, provincia FROM ubicacion WHERE refugio_id = :rid ORDER BY ciudad";
        return $this->rawQuery($sql, [':rid' => $refugioId]);
    }

    public function obtenerRefugiosConUbicacion(string $tabla, array $filtros = []): array
    {
        $sql = "SELECT r.usuario_id as id, r.nombre_institucion, r.telefono, r.imagen, 
                       u.latitud, u.longitud, u.ciudad, u.provincia, u.direccion
                FROM {$tabla} r
                INNER JOIN ubicacion u ON r.usuario_id = u.refugio_id
                WHERE u.latitud IS NOT NULL AND u.longitud IS NOT NULL";

        $binds = [];

        // Búsqueda por texto simple (ILIKE ignora mayúsculas/minúsculas en PostgreSQL)
        if (!empty($filtros['ubicacion'])) {
            $sql .= " AND (u.ciudad ILIKE :ubicacion OR u.provincia ILIKE :ubicacion)";
            $binds[':ubicacion'] = '%' . $filtros['ubicacion'] . '%';
        }

        // Ejecutamos la consulta y obtenemos los refugios
        $refugios = $this->rawQuery($sql, $binds);

        // Si el usuario permitió usar su GPS, calculamos la distancia y ordenamos
        if (!empty($filtros['lat_usuario']) && !empty($filtros['lng_usuario'])) {
            $latUsuario = (float) $filtros['lat_usuario'];
            $lngUsuario = (float) $filtros['lng_usuario'];

            foreach ($refugios as &$refugio) {
                // Usamos el Teorema de Pitágoras (mucho más simple de explicar que Haversine)
                $difLat = $refugio['latitud'] - $latUsuario;
                $difLng = $refugio['longitud'] - $lngUsuario;
                
                $distanciaEnGrados = sqrt(($difLat * $difLat) + ($difLng * $difLng));
                
                // 1 grado de latitud/longitud equivale a aproximadamente 111 kilómetros
                $refugio['distancia_km'] = $distanciaEnGrados * 111.32;
            }
            unset($refugio);

            // Ordenamos la lista de refugios de menor a mayor distancia
            usort($refugios, function($a, $b) {
                return $a['distancia_km'] <=> $b['distancia_km'];
            });
        }

        return $refugios;
    }
        /*solo mascotas disponibles*/
    public function selectByRefugioId(string $table, int $refugioId): array
    {
        $sql = "SELECT * FROM {$table} WHERE refugio_id = :refugio_id AND estado_adopcion = 'DISPONIBLE'" ;
        $sentencia = $this->pdo->prepare($sql);
        $sentencia->bindValue(':refugio_id', $refugioId, PDO::PARAM_INT);
        $sentencia->execute();
        return $sentencia->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerSolicitudesPorRefugio(string $table, int $refugioId): array
    {
        $sql = "SELECT s.id, s.fecha, s.estado,
        m.nombre as mascota_nombre,
        m.edad, m.tamano, m.temperamento,
        a.nombre as adoptante_nombre,
        a.apellido as adoptante_apellido,
        u.foto_perfil as adoptante_foto
        FROM solicitud_de_adopcion s
        JOIN mascota m ON s.mascota_id = m.id
        JOIN adoptante a ON s.adoptante_id = a.usuario_id
        JOIN usuario u ON a.usuario_id = u.id
        WHERE m.refugio_id = :refugio_id 
        ORDER BY s.fecha DESC";
        
        $sentencia = $this->pdo->prepare($sql);
        $sentencia->bindValue(':refugio_id', $refugioId, PDO::PARAM_INT);
        $sentencia->execute();
        
        return $sentencia->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $table, array $conditions) {
    $where = [];
    $values = [];
    foreach ($conditions as $column => $value) {
        $where[] = "$column = ?";
        $values[] = $value;
    }
    $sql = "DELETE FROM $table WHERE " . implode(' AND ', $where);
    $statement = $this->pdo->prepare($sql);
    return $statement->execute($values);
}

    public function obtenerEtapasEncuestasCompletadas(int $mascotaId, int $adoptanteId): array
    {
        $sql = "SELECT etapa FROM encuesta_adopcion WHERE mascota_id = :mid AND adoptante_id = :aid";
        $encuestasRealizadas = $this->rawQuery($sql, [':mid' => $mascotaId, ':aid' => $adoptanteId]);
        return array_column($encuestasRealizadas, 'etapa');
    }

    public function obtenerNotificacionesNoLeidas(string $table, int $usuarioId): array
    {
        $sql = "SELECT * FROM {$table} WHERE usuario_id = :usuario_id AND leida = false ORDER BY fecha_creacion DESC";
        return $this->rawQuery($sql, [':usuario_id' => $usuarioId]);
    }

    public function obtenerNotificacionesRecientes(string $table, int $usuarioId, int $limit): array
    {
        $sql = "SELECT * FROM {$table} WHERE usuario_id = :usuario_id ORDER BY fecha_creacion DESC LIMIT :limit";
        return $this->rawQuery($sql, [
            ':usuario_id' => $usuarioId,
            ':limit' => ['value' => $limit, 'type' => \PDO::PARAM_INT]
        ]);
    }

    public function obtenerAdoptantePorMascota(int $mascotaId): ?array
    {
        $sql = "SELECT a.nombre, a.apellido, s.telefono, u.email
                FROM solicitud_de_adopcion s
                JOIN adoptante a ON s.adoptante_id = a.usuario_id
                JOIN usuario u ON a.usuario_id = u.id
                WHERE s.mascota_id = :mascota_id
                  AND (s.estado = 'APROBADO' OR s.estado = 'APROBADA')
                LIMIT 1";
        $sentencia = $this->pdo->prepare($sql);
        $sentencia->bindValue(':mascota_id', $mascotaId, PDO::PARAM_INT);
        $sentencia->execute();
        $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }

    public function insertarResena(array $datos): void
    {
        $sql = "INSERT INTO resena (adoptante_id, mascota_id, refugio_id, calificacion, comentario) 
                VALUES (:adoptante_id, :mascota_id, :refugio_id, :calificacion, :comentario)";
        $this->rawExecute($sql, [
            ':adoptante_id' => $datos['adoptante_id'],
            ':mascota_id'   => $datos['mascota_id'],
            ':refugio_id'   => $datos['refugio_id'],
            ':calificacion' => $datos['calificacion'],
            ':comentario'   => $datos['comentario'],
        ]);
    }

    public function obtenerResenasDestacadas(int $limite = 5): array
    {
        $sql = "SELECT r.id, r.calificacion, r.comentario, r.fecha_creacion,
                       COALESCE(NULLIF(TRIM(CONCAT(a.nombre, ' ', a.apellido)), ''), u.nombre_usuario) as adoptante_nombre,
                       u.foto_perfil as adoptante_foto,
                       m.nombre as mascota_nombre, m.imagen as mascota_foto
                FROM resena r
                JOIN usuario u ON r.adoptante_id = u.id
                LEFT JOIN adoptante a ON a.usuario_id = u.id
                JOIN mascota m ON r.mascota_id = m.id
                ORDER BY r.fecha_creacion DESC
                LIMIT :limit";
        return $this->rawQuery($sql, [':limit' => ['value' => $limite, 'type' => \PDO::PARAM_INT]]);
    }

    public function obtenerAdopcionesSinResena(int $adoptanteId): array
    {
        $sql = "SELECT s.mascota_id, m.nombre as mascota_nombre, m.imagen as mascota_foto, m.refugio_id
                FROM solicitud_de_adopcion s
                JOIN mascota m ON s.mascota_id = m.id
                WHERE s.adoptante_id = :adoptante_id 
                  AND (s.estado = 'APROBADA' OR s.estado = 'APROBADO')
                  AND NOT EXISTS (
                      SELECT 1 FROM resena r 
                      WHERE r.adoptante_id = s.adoptante_id 
                        AND r.mascota_id = s.mascota_id
                  )";
        return $this->rawQuery($sql, [':adoptante_id' => $adoptanteId]);
    }
}
