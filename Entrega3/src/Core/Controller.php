<?php 

namespace Paw\Core;

use Paw\Core\Model;
use Paw\Core\Database\QueryBuilder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Controller
{
    public $viewsDir;
    protected $menu;
    protected $redes;
    protected $notificaciones;
    protected $model;
    protected Environment $twig;
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

        // Iniciar sesión para verificar si hay usuario autenticado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->notificaciones = 0;
        if (isset($_SESSION['user']['id']) && class_exists('\Paw\App\Models\MensajeCollection')) {
            $qbMensajes = new QueryBuilder($connection, $log);
            $mensajesCol = new \Paw\App\Models\MensajeCollection();
            $mensajesCol->setQueryBuilder($qbMensajes);
            $this->notificaciones = $mensajesCol->getUnreadCount($_SESSION['user']['id']);
        }

        // Configuración de Twig 
        $loader = new FilesystemLoader($this->viewsDir);
        
        $cacheDir = $this->viewsDir . '/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $this->twig = new Environment($loader, [
            'cache' => $cacheDir,
            'auto_reload' => true, // Para desarrollo, recarga automática de plantillas
        ]);

        $this->twig->addGlobal('session', $_SESSION ?? []);


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
                "name" => "Test",
                "icon" => "quiz",
                "type" => "link",
            ],
            [
                "href" => "/como-adoptar",
                "name" => "¿Cómo Adoptar?",
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
                "href" => "/generar-qr",
                "name" => "Generar QR",
                "icon" => "qr_code_2",
                "type" => "link",
            ],
            [
                "href"  => "/contacto",
                "name"  => "Contacto",
                "icon"  => "mail",
                "type"  => "link"
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

        if (!is_null($this->modelName)){
            $qb = new QueryBuilder($connection, $log);
            $model = new $this->modelName;
            $model->setQueryBuilder($qb);
            $this->setModel($model);
        }

        // Exponer globales manuales en Twig 
        $this->twig->addGlobal('menu', $this->menu);
        $this->twig->addGlobal('redes', $this->redes);
        $this->twig->addGlobal('notificaciones', $this->notificaciones);
        $this->twig->addGlobal('request', $this->request);
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    protected function loadCollection($className)
    {
        $model = new $className;
        $model->setQueryBuilder($this->model->getQueryBuilder());
        return $model;
    }

}
