<?php

namespace Paw\Core\Database;

use PDO;
use Monolog\Logger;

class QueryBuilder
{
    private $pdo;
    private $logger;

    public function __construct(PDO $pdo, Logger $logger)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

   public function select($table, $params = [], $precios = [], int $limit = 0, int $offset = 0)
    {
        $conditions = [];
        $binds = [];
        foreach ($params as $campo => $valor) {
            $conditions[] = "{$campo} = :{$campo}";
            $binds[":{$campo}"] = $valor;
        }
        if (!empty($precios['min'])) {
            $conditions[] = "precio >= :pmin";
            $binds[":pmin"] = $precios['min'];
        }
        if (!empty($precios['max'])) {
            $conditions[] = "precio <= :pmax";
            $binds[":pmax"] = $precios['max'];
        }
        $where = !empty($conditions) ? implode(" AND ", $conditions) : "1=1";
        $query = "SELECT * FROM {$table} WHERE {$where}";
 
        // Solo agrega LIMIT/OFFSET si se piden
        if ($limit > 0) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        $sentencia = $this->pdo->prepare($query);
        foreach ($binds as $key => $val) {
            $sentencia->bindValue($key, $val);
        }

        if ($limit > 0) {
            $sentencia->bindValue(':limit',  $limit,  PDO::PARAM_INT);
            $sentencia->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $sentencia->execute();
        return $sentencia->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectRelated($table, $params = [])
    {
        $conditions = [];
        $binds = [];

        foreach ($params as $campo => $valor) {
            $conditions[] = "{$campo} = :{$campo}";
            $binds[":{$campo}"] = $valor;
        }

        $where = !empty($conditions) ? implode(" OR ", $conditions) : "1=1";
        $query = "SELECT * FROM {$table} WHERE {$where}";

        $sentencia = $this->pdo->prepare($query);
        foreach ($binds as $key => $val) {
            $sentencia->bindValue($key, $val);
        }
        $sentencia->execute();
        return $sentencia->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(){
    }

    public function update(){
    }

    public function delete(){
    }

    public function count($table, $params = [])
    {
        $conditions = [];
        foreach ($params as $campo => $valor) {
            $conditions[] = "{$campo} = :{$campo}";
        }

        $where = !empty($conditions) ? implode(" AND ", $conditions) : "1=1";
        $query = "SELECT COUNT(*) as total FROM {$table} WHERE {$where}";

        $sentencia = $this->pdo->prepare($query);

        foreach ($params as $campo => $valor) {
            $sentencia->bindValue(":{$campo}", $valor);
        }

        $sentencia->setFetchMode(PDO::FETCH_ASSOC);
        $sentencia->execute();
        return $sentencia->fetch();
    }

        public function buscar($termino, int $limit = 0, int $offset = 0)
    {
        $query = "SELECT * FROM libro WHERE titulo LIKE :termino OR descripcion LIKE :termino";
        if ($limit > 0) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
 
        $sentencia = $this->pdo->prepare($query);
        $sentencia->bindValue(':termino', "%{$termino}%");
 
        if ($limit > 0) {
            $sentencia->bindValue(':limit',  $limit,  PDO::PARAM_INT);
            $sentencia->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
 
        $sentencia->setFetchMode(PDO::FETCH_ASSOC);
        $sentencia->execute();
        return $sentencia->fetchAll();
    }

    public function buscarCount($termino): int
    {
        $query = "SELECT COUNT(*) as total FROM libro WHERE titulo LIKE :termino OR descripcion LIKE :termino";
        $sentencia = $this->pdo->prepare($query);
        $sentencia->bindValue(':termino', "%{$termino}%");
        $sentencia->execute();
        return (int) $sentencia->fetchColumn();
    }


}