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
}