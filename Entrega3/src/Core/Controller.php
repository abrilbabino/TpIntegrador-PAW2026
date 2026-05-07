<?php 

namespace Paw\Core;

use Paw\Core\Model;
use Paw\Core\Database\QueryBuilder;

class Controller
{
    public $viewsDir;
    protected $menu;
    protected $redes;
    protected $model;
    protected $request;
    protected $log;
    protected $connection;

    public ?string $modelName = null; 
    
    public function __construct($request, $log, $connection)
    {
        $this->request = $request;
        $this->log = $log;
        $this->connection = $connection;
        $this->viewsDir = __DIR__ . "/../App/Views";

        $this->menu = [
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

        if (!is_null($this->modelName)){
            $qb = new QueryBuilder($connection, $log);
            $model = new $this->modelName;
            $model->setQueryBuilder($qb);
            $this->setModel($model);
        }
    }

    public function getQueryBuilder()
    {
        return new QueryBuilder($this->connection, $this->log);
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
    }
}
