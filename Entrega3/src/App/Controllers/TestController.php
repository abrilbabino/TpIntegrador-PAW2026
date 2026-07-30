<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\MascotaCollection;
use Paw\App\Models\TestDeCompatibilidad;
use Paw\App\Models\TestCompatibilidadPreguntaCollection;

class TestController extends Controller
{
    public ?string $modelName = TestDeCompatibilidad::class;
    public function test()
    {
        $menu = $this->menu;
        $redes = $this->redes;
        $metaDescription = "Hacé el test de compatibilidad de PawMap para descubrir qué tipo de mascota (perro o gato) se adapta mejor a tu estilo de vida y hogar.";

        $preguntaCollection = $this->loadCollection(TestCompatibilidadPreguntaCollection::class);
        $preguntas = $preguntaCollection->getAll();

        echo $this->twig->render('test-compatibilidad.html.twig', get_defined_vars());
    }

    public function resultado()
    {
        $postData = $this->request->post();
        $respuestas = [
            'pregunta1' => $postData['pregunta1'] ?? null,
            'pregunta2' => $postData['pregunta2'] ?? null,
            'pregunta3' => $postData['pregunta3'] ?? null,
            'pregunta4' => $postData['pregunta4'] ?? null,
            'pregunta5' => $postData['pregunta5'] ?? null
        ];

        $test = new TestDeCompatibilidad();
        $test->setRespuestas(json_encode($respuestas));
        $filtrosSQL = $test->construirFiltrosBusqueda();

        $mascotaCollection = $this->loadCollection(MascotaCollection::class);

        $mascotas = $mascotaCollection->buscarCompatibles($filtrosSQL);

        if (empty($mascotas)) {
            $soloEspecie = array_intersect_key($filtrosSQL, array_flip(['especie', 'estado_adopcion']));
            $mascotas = $mascotaCollection->buscarCompatibles($soloEspecie);
        }

        $resultadoTest = json_decode($test->getResultado(), true);

        $titulo = "Resultados del Test - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;
        echo $this->twig->render('resultado-test.html.twig', get_defined_vars());
    }
}
