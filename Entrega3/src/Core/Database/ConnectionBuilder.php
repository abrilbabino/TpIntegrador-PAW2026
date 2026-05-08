<?php
namespace Paw\Core\Database;

use PDO;
use PDOException;
use Paw\Core\Config;
use Paw\Core\Traits\Loggable;

class ConnectionBuilder
{
	use Loggable;
	
	public function make(Config $config): PDO
	{
		try{
            $hostname = $config->get('DB_HOSTNAME');
            $dbname = $config->get('DB_DBNAME');
            $port = $config->get('DB_PORT');
            $username = $config->get('DB_USERNAME');
            $password = $config->get('DB_PASSWORD');

            $dsn = "pgsql:host={$hostname};port={$port};dbname={$dbname}";

            return new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
		} catch(PDOException $e){
			$this->logger->error('Internal Server Error', ["Error" => $e]);
            die("Error Interno - Consulte al administrador");
        }
	}
}
