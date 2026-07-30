<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\MascotaCollection;
use Paw\App\Models\Favorito;
use Paw\App\Models\ResenaCollection;
use Paw\App\Models\RefugioCollection;
use Paw\Core\MailService;

class PageController extends Controller
{
    public ?string $modelName = MascotaCollection::class;

    public function index()
    {
        $titulo = htmlspecialchars($this->request->get("nombre") ?? "Inicio-PawMap");
        $metaDescription = "Descubrí en PawMap a tu compañero ideal. Buscá entre cientos de perros y gatos en adopción de los mejores refugios.";
        $menu = $this->menu;
        $redes = $this->redes;
        
        // Algoritmo de Mascotas Invisibles: Obtener las 8 mascotas más olvidadas
        $mascotas = $this->model->getMascotasInvisibles(8);

        $refugioCollection = $this->loadCollection(RefugioCollection::class);
        $refugiosMapa = $refugioCollection->getRefugiosConUbicacion([]);

        $favoritoModel = $this->loadModel(Favorito::class);
        $favoritosIds = $favoritoModel->getFavoritosIds($this->request->session('user'));

        $resenaCollection = $this->loadCollection(ResenaCollection::class);
        $resenas = $resenaCollection->getResenasDestacadas(5);
        
        $adopcionesSinResena = [];
        $userSession = $this->request->session('user');
        if ($userSession && isset($userSession['id'])) {
            $adopcionesSinResena = $resenaCollection->getAdopcionesSinResena($userSession['id']);
        }

        echo $this->twig->render('index.html.twig', get_defined_vars());
    }

    public function mapa()
    {
        $titulo = "Mapa Interactivo - PawMap";
        $metaDescription = "Explorá nuestro mapa interactivo de PawMap y encontrá refugios y mascotas en adopción cerca de tu ubicación.";
        $menu = $this->menu;
        $redes = $this->redes;
        $request = $this->request;

        $filtros = [
            'especie' => $request->get('especie'),
            'tamano' => $request->get('tamano'),
            'temperamento' => $request->get('temperamento'),
            'edad_min' => $request->get('edad_min'),
            'edad_max' => $request->get('edad_max'),
            'ubicacion' => $request->get('ubicacion'),
            'lat_usuario' => $request->get('lat_usuario'),
            'lng_usuario' => $request->get('lng_usuario')
        ];

        // Obtener refugios con ubicaciones para el mapa
        $refugioCollection = $this->loadCollection(RefugioCollection::class);
        $refugiosMapa = $refugioCollection->getRefugiosConUbicacion($filtros);

        $mascotas = $this->model->getFiltered($filtros);

        $favoritoModel = $this->loadModel(Favorito::class);
        $favoritosIds = $favoritoModel->getFavoritosIds($this->request->session('user'));

        echo $this->twig->render('mapa.html.twig', get_defined_vars());
    }

    public function buscar()
    {
        $titulo = "Resultados de búsqueda - PawMap";
        $metaDescription = "Buscá mascotas en adopción y refugios de animales en PawMap. Encontrá resultados adaptados a tus preferencias.";
        $menu = $this->menu;
        $redes = $this->redes;
        $request = $this->request;
        $q = $request->get('busqueda'); // Viene del input con name="busqueda"

        $resultados_mixtos = [];

        // Mascotas
        $mascotas = $this->model->buscar($q ?? '');
        foreach ($mascotas as $m) {
            $item = $m->fields ?? [];
            $item['tipo_entidad'] = 'mascota';
            $resultados_mixtos[] = $item;
        }

        // Refugios
        $refugioCollection = $this->loadCollection(RefugioCollection::class);
        $refugios = $refugioCollection->buscar($q ?? '');
        foreach ($refugios as $r) {
            $item = $r->fields ?? [];
            $item['tipo_entidad'] = 'refugio';
            $resultados_mixtos[] = $item;
        }

        echo $this->twig->render('busqueda.html.twig', get_defined_vars());
    }

    public function comoAdoptar()
    {
        $titulo = "Como Adoptar - PawMap";
        $metaDescription = "Conocé el paso a paso de cómo adoptar una mascota en PawMap. Te guiamos en todo el proceso para encontrar a tu mejor amigo.";
        $menu = $this->menu;
        $redes = $this->redes;
        echo $this->twig->render('como-adoptar.html.twig', get_defined_vars());
    }


    public function contacto()
    {
        $titulo = "Contacto - PawMap";
        $metaDescription = "Contactate con PawMap. Estamos para resolver tus dudas y ayudarte en el proceso de adopción de mascotas.";
        $menu = $this->menu;
        $redes = $this->redes;
        echo $this->twig->render('contacto.html.twig', get_defined_vars());
    }

    public function contactoEnviar()
    {
        global $config;
        $titulo = "Contacto - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;
        $mailService = new MailService;
        $mailService->enviarContacto(
            $config->get('MAIL_PERSONAL'),
            [
                'nombre' => $this->request->get('nombre'),
                'email' => $this->request->get('email'),
                'asunto' => $this->request->get('asunto'),
                'mensaje' => $this->request->get('mensaje'),
            ]
        );
        echo $this->twig->render('contacto.html.twig', [
            'titulo' => 'Contacto - PawMap',
            'menu' => $this->menu,
            'redes' => $this->redes,
            'flash_type' => 'contacto'
        ]);
        exit;
    }
    public function donacion()
    {
        $titulo = "Donaciones - PawMap";
        $metaDescription = "Apoyá a los refugios de animales haciendo una donación a través de PawMap. Tu aporte ayuda a salvar vidas.";
        $menu = $this->menu;
        $redes = $this->redes;
        echo $this->twig->render('donacion.html.twig', get_defined_vars());
    }

    public function sitemap()
    {
        // Obtener mascotas disponibles
        $mascotas = $this->model->getAll(['estado_adopcion' => 'DISPONIBLE']);

        // Obtener refugios
        $refugioCollection = $this->loadCollection(RefugioCollection::class);
        $refugios = $refugioCollection->getAll();

        // Configurar el Content-Type para XML
        header('Content-Type: application/xml; charset=utf-8');

        // El view se encargará de renderizar el XML puro
        echo $this->twig->render('sitemap.html.twig', get_defined_vars());
        exit;
    }
}