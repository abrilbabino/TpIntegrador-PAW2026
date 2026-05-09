<?php

namespace Paw\Core;

use Paw\Core\DataBase\QueryBuilder;
use Paw\Core\Traits\Loggable;

class Model
{
    protected $queryBuilder;
    use Loggable;

    public function setQueryBuilder(QueryBuilder $qb)
    {
        $this->queryBuilder = $qb;
    }

    public function getQueryBuilder(){
        return $this->queryBuilder;
    }
}