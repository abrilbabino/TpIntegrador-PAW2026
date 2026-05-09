<?php

namespace Paw\Core;

class Config
{
    private array $configs = [];

    public function __construct()
    {
        $this->configs["LOG_LEVEL"] = getenv("LOG_LEVEL", "INFO");
        $path = getenv("LOG_PATH", "/logs/app.log");

        $this->configs["LOG_PATH"] = $this->joinPaths(__DIR__ . '/../../', $path);

        $this->configs["DB_ADAPTER"]  = getenv("DB_ADAPTER")  ?? 'pgsql';
        $this->configs["DB_HOSTNAME"] = getenv("DB_HOSTNAME") ?? 'localhost';
        $this->configs["DB_DBNAME"]   = getenv("DB_DBNAME")   ?? 'pawmap';
        $this->configs["DB_USERNAME"] = getenv("DB_USERNAME") ?? 'root';
        $this->configs["DB_PASSWORD"] = getenv("DB_PASSWORD") ?? '';
        $this->configs["DB_PORT"]     = getenv("DB_PORT")     ?? '5432';
        $this->configs["DB_CHARSET"]  = getenv("DB_CHARSET")  ?? 'utf8';

        $this->configs["MAIL_PERSONAL"] = getenv("MAIL_PERSONAL") ?? 'pawmap@gmail.com';
        $this->configs["MAIL_HOST"]     = getenv("MAIL_HOST")     ?? 'smtp.gmail.com';
        $this->configs["MAIL_USER"]     = getenv("MAIL_USER")     ?? '';
        $this->configs["MAIL_PASS"]     = getenv("MAIL_PASS")     ?? '';
        $this->configs["MAIL_PORT"]     = getenv("MAIL_PORT")     ?? '587';
        $this->configs["MAIL_PASS"]     = getenv("MAIL_PASS")     ?? '';

        $this->configs["GOOGLE_MAPS_KEY"] = getenv("GOOGLE_MAPS_KEY") ?? '';
    }

    public function joinPaths()
    {
        $paths = [];
        foreach (func_get_args() as $arg) {
            if ($arg != '') {
                $paths[] = $arg;
            }
        }
        return preg_replace('#/+#', '/', join('/', $paths));
    }

    public function get($name)
    {
        return $this->configs[$name] ?? null;
    }
}