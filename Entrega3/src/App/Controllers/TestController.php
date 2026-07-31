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
        $diccionario = new \Paw\App\Models\DiccionarioCollection();
        $diccionario->setQueryBuilder($mascotaCollection->getQueryBuilder());

        // Mapear filtros de texto a IDs
        $filtrosDB = [];
        if (isset($filtrosSQL['estado_adopcion'])) {
            $filtrosDB['estado_adopcion'] = $filtrosSQL['estado_adopcion'];
        }

        if (isset($filtrosSQL['especie'])) {
            $id = $diccionario->obtenerOCrearId('especie', $filtrosSQL['especie']);
            if ($id) $filtrosDB['especie_id'] = $id;
        }

        if (isset($filtrosSQL['tamano'])) {
            $ids = [];
            foreach ((array)$filtrosSQL['tamano'] as $t) {
                $id = $diccionario->obtenerOCrearId('tamano', $t);
                if ($id) $ids[] = $id;
            }
            if (!empty($ids)) $filtrosDB['tamano_id'] = $ids;
        }

        if (isset($filtrosSQL['temperamento'])) {
            $ids = [];
            foreach ((array)$filtrosSQL['temperamento'] as $t) {
                $id = $diccionario->obtenerOCrearId('temperamento', $t);
                if ($id) $ids[] = $id;
            }
            if (!empty($ids)) $filtrosDB['temperamento_id'] = $ids;
        }

        $mascotas = $mascotaCollection->buscarCompatibles($filtrosDB);

        if (empty($mascotas)) {
            $soloEspecie = array_intersect_key($filtrosDB, array_flip(['especie_id', 'estado_adopcion']));
            $mascotas = $mascotaCollection->buscarCompatibles($soloEspecie);
        }

        $resultadoTest = json_decode($test->getResultado(), true);

        $titulo = "Resultados del Test - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;
        echo $this->twig->render('resultado-test.html.twig', get_defined_vars());
    }
}
