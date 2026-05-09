<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\MascotaCollection;
use Paw\App\Models\TestDeCompatibilidad;
use Paw\App\Models\TestCompatibilidadPreguntaCollection;

class TestController extends Controller
{
    public function test()
    {
        $menu = $this->menu;
        $redes = $this->redes;

        $qb = $this->model->getQueryBuilder();
        $preguntaCollection = new TestCompatibilidadPreguntaCollection();
        $preguntaCollection->setQueryBuilder($qb);
        $preguntas = $preguntaCollection->getAll();

        require $this->viewsDir . '/test-compatibilidad.view.php';
    }

    public function resultado()
    {
        $respuestas = [
            'pregunta1' => $_POST['pregunta1'] ?? null,
            'pregunta2' => $_POST['pregunta2'] ?? null,
            'pregunta3' => $_POST['pregunta3'] ?? null,
            'pregunta4' => $_POST['pregunta4'] ?? null,
            'pregunta5' => $_POST['pregunta5'] ?? null
        ];

        $test = new TestDeCompatibilidad();
        $test->setRespuestas(json_encode($respuestas));
        $filtrosSQL = $test->construirFiltrosBusqueda();

        $qb = $this->model->getQueryBuilder();
        $mascotaCollection = new MascotaCollection();
        $mascotaCollection->setQueryBuilder($qb);

        $mascotas = $mascotaCollection->buscarCompatibles($filtrosSQL);

        if (empty($mascotas)) {
            $soloEspecie = array_intersect_key($filtrosSQL, array_flip(['especie', 'estado_adopcion']));
            $mascotas = $mascotaCollection->buscarCompatibles($soloEspecie);
        }

        $resultadoTest = json_decode($test->getResultado(), true);

        $titulo = "Resultados del Test - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;
        require $this->viewsDir . '/resultado-test.view.php';
    }
}
