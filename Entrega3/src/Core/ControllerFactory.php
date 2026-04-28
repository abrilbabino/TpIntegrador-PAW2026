<?php

namespace Paw\Core;

class ControllerFactory
{
    private $request;
    private $log;
    private $connection;

    public function __construct($request, $log, $connection)
    {
        $this->request = $request;
        $this->log = $log;
        $this->connection = $connection;
    }

    public function create(string $controllerName)
    {
        $className = "Paw\\App\\Controllers\\{$controllerName}";
        return new $className($this->request, $this->log, $this->connection);
    }
}