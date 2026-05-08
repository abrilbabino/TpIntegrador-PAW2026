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

    private function buildWhere(array $params, string $operator = 'AND'): array
    {
        $conditions = [];
        $binds = [];

        $edadMin = $params['edad_min'] ?? null;
        $edadMax = $params['edad_max'] ?? null;

        unset($params['edad_min'], $params['edad_max'], $params['ubicacion'], $params['page']);

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

        $where = !empty($conditions) ? implode(" {$operator} ", $conditions) : "1=1";

        return [$where, $binds];
    }

    private function bindValues(PDOStatement $sentencia, array $binds): void
    {
        foreach ($binds as $key => $val) {
            $sentencia->bindValue($key, $val);
        }
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
}