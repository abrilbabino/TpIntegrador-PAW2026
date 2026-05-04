<?php

require __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->safeLoad();

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => getenv("DB_ADAPTER") ?: 'pgsql',
            'host' => getenv("DB_HOSTNAME") ?: 'localhost',
            'name' => getenv("DB_DBNAME") ?: 'pawmap_prod',
            'user' => getenv("DB_USERNAME") ?: 'postgres',
            'pass' => getenv("DB_PASSWORD") ?: '',
            'port' => getenv("DB_PORT") ?: '5432',
            'charset' => getenv("DB_CHARSET") ?: 'utf8'
        ],
        'development' => [
            'adapter' => getenv("DB_ADAPTER") ?: 'pgsql',
            'host' => getenv("DB_HOSTNAME") ?: 'localhost',
            'name' => getenv("DB_DBNAME") ?: 'pawmap_db',
            'user' => getenv("DB_USERNAME") ?: 'postgres',
            'pass' => getenv("DB_PASSWORD") ?: '',
            'port' => getenv("DB_PORT") ?: '5432',
            'charset' => getenv("DB_CHARSET") ?: 'utf8'
        ],
        'testing' => [
            'adapter' => 'pgsql',
            'host' => 'localhost',
            'name' => 'pawmap_test',
            'user' => 'postgres',
            'pass' => '',
            'port' => '5432',
            'charset' => 'utf8'
        ]
    ],
    'version_order' => 'creation'
];
