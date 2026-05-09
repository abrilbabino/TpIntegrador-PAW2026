<?php

namespace Paw\Core\DataBase;

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

    private function bindValues(PDOStatement $sentencia, array $binds): void
    {
        foreach ($binds as $key => $val) {
            if (is_array($val) && isset($val['value'], $val['type'])) {
                $sentencia->bindValue($key, $val['value'], $val['type']);
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
        $sql = "SELECT DISTINCT {$campo} FROM {$tabla} WHERE {$campo} IS NOT NULL AND {$campo} != '' ORDER BY {$campo}";
        return $this->rawQuery($sql);
    }

    public function buscarMascotasPorTermino(string $tabla, string $termino, bool $esConteo = false, ?int $limite = null, ?int $offset = null)
    {
        $select = $esConteo ? "COUNT(*)" : "*";
        $sql = "SELECT {$select} FROM {$tabla} WHERE estado_adopcion = 'DISPONIBLE' 
                AND (nombre LIKE :term1 OR especie LIKE :term2 OR descripcion LIKE :term3)";
        
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
        $sql = "SELECT s.estado, m.nombre, m.edad, m.tamano, m.temperamento
                FROM {$tabla} s
                JOIN mascota m ON s.mascota_id = m.id
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
        
        foreach ($data as $key => $value) {
            $sentencia->bindValue(":{$key}", $value);
        }
        
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
                JOIN solicitud_de_adopcion s ON m.id = s.mascota_id AND s.estado = 'APROBADO'
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
}