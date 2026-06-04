<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\MascotaCollection;
use Paw\Core\MailService;

class PageController extends Controller
{
    public ?string $modelName = MascotaCollection::class;

    public function index()
    {
        $titulo = htmlspecialchars($_GET["nombre"] ?? "Inicio-PawMap");
        $metaDescription = "Descubrí en PawMap a tu compañero ideal. Buscá entre cientos de perros y gatos en adopción de los mejores refugios.";
        $menu = $this->menu;
        $redes = $this->redes;
        
        $mascotas = $this->model->getAll(['estado_adopcion' => 'DISPONIBLE']);

        $refugioCollection = new \Paw\App\Models\RefugioCollection();
        $refugioCollection->setQueryBuilder($this->model->getQueryBuilder());
        $refugiosMapa = $refugioCollection->getRefugiosConUbicacion([]);

        require $this->viewsDir . '/index.view.php';
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
        $refugioCollection = new \Paw\App\Models\RefugioCollection();
        $refugioCollection->setQueryBuilder($this->model->getQueryBuilder());
        $refugiosMapa = $refugioCollection->getRefugiosConUbicacion($filtros);

        $mascotas = $this->model->getFiltered($filtros);

        require $this->viewsDir . '/mapa.view.php';
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
        $refugioCollection = new \Paw\App\Models\RefugioCollection();
        $refugioCollection->setQueryBuilder($this->model->getQueryBuilder());
        $refugios = $refugioCollection->buscar($q ?? '');
        foreach ($refugios as $r) {
            $item = $r->fields ?? [];
            $item['tipo_entidad'] = 'refugio';
            $resultados_mixtos[] = $item;
        }

        require $this->viewsDir . '/busqueda.view.php';
    }

    public function iniciarSesion()
    {
        $titulo = "Iniciar Sesión";
        $menu = $this->menu;
        $redes = $this->redes;
        $request = $this->request;
        require $this->viewsDir . '/iniciar-sesion.view.php';
    }

    public function comoAdoptar()
    {
        $titulo = "Como Adoptar - PawMap";
        $metaDescription = "Conocé el paso a paso de cómo adoptar una mascota en PawMap. Te guiamos en todo el proceso para encontrar a tu mejor amigo.";
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/como-adoptar.view.php';
    }

    public function adopcionExitosa()
    {
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/adopcion-exitosa.view.php';
    }

    public function contacto()
    {
        $titulo = "Contacto - PawMap";
        $metaDescription = "Contactate con PawMap. Estamos para resolver tus dudas y ayudarte en el proceso de adopción de mascotas.";
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/contacto.view.php';
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
        header("Location: /contacto-exitoso");
    }
    public function contactoExitoso()
    {
        $titulo = "Contacto Exitoso - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/contacto-exitoso.view.php';
    }
    public function donacion()
    {
        $titulo = "Donaciones - PawMap";
        $metaDescription = "Apoyá a los refugios de animales haciendo una donación a través de PawMap. Tu aporte ayuda a salvar vidas.";
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/donacion.view.php';
    }
}