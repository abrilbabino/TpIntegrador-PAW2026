<?php 

namespace Paw\Core;

use Paw\Core\Model;
use Paw\Core\Database\QueryBuilder;

class Controller
{
    public string $viewsDir;
    protected $menu;
    protected $redes;
    protected $model;

    public ?string $modelName = null; 
    
    protected $request;
    protected $log;

    public function __construct($request, $log, $connection)
    {
        $this->request = $request;
        $this->log = $log;
        $this -> viewsDir = __DIR__ . "/../App/Views";

        // Iniciar sesión para verificar si hay usuario autenticado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this -> menu = [
            [
                "href" => "/",
                "name" => "Inicio",
                "icon" => "home",
                "type" => "link",
            ],
            [
                "href" => "/adoptar",
                "name" => "Adoptar",
                "icon" => "pets",
                "type" => "link",
            ],
            [
                "href" => "/mapa",
                "name" => "Mapa",
                "icon" => "map",
                "type" => "link",
            ],
            [
                "href" => "/test-de-compatibilidad",
                "name" => "Test de Compatibilidad",
                "icon" => "quiz",
                "type" => "link",
            ],
            [
                "href" => "/como-adoptar",
                "name" => "Cómo Adoptar?",
                "icon" => "help",
                "type" => "link",
            ],
            [
                "href" => "/donar",
                "name" => "Donar",
                "icon" => "volunteer_activism",
                "type" => "link",
            ],
            [
                "href" => "/refugios",
                "name" => "Refugios",
                "icon" => "location_city",
                "type" => "link",
            ],
            [
                "href"  => "/contacto",
                "name"  => "Contacto",
                "icon"  => "mail",
                "type"  => "link"
            ],
            (isset($_SESSION['user']))
            ? [
                "href" => "/perfil",
                "name" => "Mi Perfil",
                "icon" => "account_circle",
                "type" => "link"
            ]
            : [
                "href" => "/iniciar-sesion",
                "name" => "Iniciar sesión",
                "icon" => "account_circle",
                "type" => "link"
            ]
        ];

        $this->redes = [
            [
                'name' => 'TikTok', 
                'url' => 'https://tiktok.com', 
                'img' => 'tiktok.png'
            ],
            [
                'name' => 'Instagram', 
                'url' => 'https://www.instagram.com/pawmap.ar/', 
                'img' => 'instagram.png'
            ],
        ];

        if (!is_null($this ->modelName)){
            $qb = new QueryBuilder($connection, $log);
            $model = new $this->modelName;
            $model -> setQueryBuilder($qb);
            $this -> setModel($model);
        }
    }

    public function setModel(Model $model)
    {
        $this -> model = $model;
    }
}