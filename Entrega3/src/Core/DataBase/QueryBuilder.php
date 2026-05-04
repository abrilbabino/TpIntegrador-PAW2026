<?php

namespace Paw\Core\Database;

use PDO;
use Monolog\Logger;

class QueryBuilder
{
    protected $connection;
    protected $log;

    public function __construct($connection, Logger $log = null)
    {
        $this->connection = $connection;
        $this->log = $log;
    }

    public function select(string $table, array $conditions = [])
    {
        $sql = "SELECT * FROM {$table}";
        $params = [];
        
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= implode(" AND ", $whereClauses);
        }
        
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            if ($this->log) {
                $this->log->error("Error en consulta: " . $e->getMessage());
            }
            return [];
        }
    }
}