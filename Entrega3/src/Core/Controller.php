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

        $this -> menu = [
            [
                "href" => "/",
                "name" => "Inicio",
                "icon" => "home",
                "type" => "link",
            ],
            [
                "name" => "Adoptar",
            ],
            [
                "name" => "Mapa",
            ],
            [
                "name" => "Test de Compatibilidad",
            ],
            [
                "name" => "Cómo Adoptar?",
            ],
            [
                "name" => "Donar",
            ],
            [
                "name" => "Refigios",
            ],
            [
                "href" => "/sobreNosotros",
                "name" => "Sobre Nosotros",
                "icon"  => "groups",
                "type"  => "link"
            ],
            [
                "name" => "Iniciar Sesión",
                "for"   => "mostrar-login",
                "icon"  => "login",
                "type"  => "label"
            ]
        ];

        $this->redes = [
            [
                'name' => 'Facebook', 
                'url' => 'https://facebook.com', 
                'img' => 'facebook.png'
            ],
            [
                'name' => 'Twitter', 
                'url' => 'https://twitter.com', 
                'img' => 'twitter.png'
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