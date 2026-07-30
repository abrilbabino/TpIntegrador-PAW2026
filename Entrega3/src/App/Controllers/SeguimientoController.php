<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\EncuestaAdaptacion;
use Paw\App\Models\EncuestaAdaptacionCollection;
use Paw\App\Models\MascotaCollection;
use Paw\App\Models\RegistroSanitarioCollection;
use Paw\App\Models\MediaMascotaCollection;
use Paw\App\Helpers\GCSHelper;

class SeguimientoController extends Controller
{
    public ?string $modelName = EncuestaAdaptacion::class;

    public function index()
    {
        $user = $this->request->session('user');
        if (empty($user)) {
            header('Location: /?auth=login');
            exit;
        }

        $rol  = $user['rol'] ?? 'adoptante';

        if ($rol === 'refugio') {
            header('Location: /perfil');
            exit;
        }

        $menu  = $this->menu;
        $redes = $this->redes;
        $titulo = "Seguimiento Post-Adopción - PawMap";

        $errorUpload = $this->request->session('error_upload');
        if ($errorUpload !== null) {
            $this->request->unsetSession('error_upload');
        }

        $qb = $this->model->getQueryBuilder();

        // Usamos la colección de mascotas para obtener objetos completos
        $mascotaCol = $this->loadCollection(MascotaCollection::class);
        $adoptanteId = (int) $user['id'];
        $adopciones = $mascotaCol->getAdopcionesByAdoptante($adoptanteId);

        $mascotaSeleccionada = null;
        $registros = [];
        $proximoTurno = null;

        $mascotaIdGet = $this->request->get('id');
        if (!empty($mascotaIdGet) && is_numeric($mascotaIdGet)) {
            $mascotaId = (int) $mascotaIdGet;

            // Verificar si el adoptante realmente adoptó esta mascota iterando objetos
            $adopcionValida = array_filter($adopciones, function($ad) use ($mascotaId) {
                return $ad->fields['id'] == $mascotaId;
            });

            if (!empty($adopcionValida)) {
                $mascotaSeleccionada = $mascotaCol->get($mascotaId);
                
                // Usamos la colección de registros sanitarios para obtener objetos
                $registroCol = $this->loadCollection(RegistroSanitarioCollection::class);
                $registros = $registroCol->getByMascota($mascotaId);
                
                // LÓGICA DEL BANNER DINÁMICO
                $estadoGlobal = 'dia';
                $registrosPendientes = [];

                foreach ($registros as $r) {
                    if (strtolower($r->fields['estado']) === 'pendiente') {
                        $registrosPendientes[] = $r;
                        if (strtotime($r->fields['fecha_programada']) < time()) {
                            $estadoGlobal = 'alerta';
                        }
                    }
                }
                
                if (!empty($registrosPendientes)) {
                    usort($registrosPendientes, function($a, $b) {
                        return strtotime($a->fields['fecha_programada']) - strtotime($b->fields['fecha_programada']);
                    });
                    $proximoTurno = reset($registrosPendientes);
                }

                $fechaAdopcion = $mascotaSeleccionada->fields['fecha_adopcion'];
                $diasDesdeAdopcion = $fechaAdopcion ? floor((time() - strtotime($fechaAdopcion)) / 86400) : 0;
                
                $etapasRealizadas = $qb->obtenerEtapasEncuestasCompletadas($mascotaId, $adoptanteId);

                $encuestasConfig = [
                    ['id' => '3_dias', 'titulo' => 'Alimentación y Sueño', 'dias' => 3],
                    ['id' => '7_dias', 'titulo' => 'Conducta General', 'dias' => 7],
                    ['id' => '14_dias', 'titulo' => 'Progreso General', 'dias' => 14],
                ];

                $estadoEncuestas = [];
                foreach ($encuestasConfig as $enc) {
                    $id = $enc['id'];
                    $diasReq = $enc['dias'];

                    if (in_array($id, $etapasRealizadas)) {
                        $estado = 'COMPLETADA';
                        $faltan = 0;
                    } elseif ($diasDesdeAdopcion >= $diasReq) {
                        $estado = 'HABILITADA';
                        $faltan = 0;
                    } else {
                        $estado = 'BLOQUEADA';
                        $faltan = $diasReq - $diasDesdeAdopcion;
                    }

                    $estadoEncuestas[] = [
                        'id' => $id,
                        'titulo' => $enc['titulo'],
                        'dias_requeridos' => $diasReq,
                        'estado' => $estado,
                        'faltan' => $faltan
                    ];
                }
            }
        }

        echo $this->twig->render('seguimiento.html.twig', get_defined_vars());
    }

    public function subirArchivo()
    {
        $user = $this->request->session('user');
        if (empty($user) || $this->request->method() !== 'POST') {
            header('Location: /?auth=login');
            exit;
        }

        $registroId = $this->request->get('registro_id');
        $tipoArchivo = $this->request->get('tipo_archivo') ?? 'comprobante';
        $mascotaId = $this->request->get('mascota_id');
        $archivo = $this->request->file('archivo');

        if (!$mascotaId || !$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
            $this->request->setSession('error_upload', 'El archivo es demasiado pesado (límite de 2MB) o hubo un error al subirlo.');
            header('Location: /seguimiento?id=' . $mascotaId);
            exit;
        }

        try {
            $url = GCSHelper::subir($archivo, 'seguimiento_mascotas');
            $qb = $this->model->getQueryBuilder();
            $mediaCol = $this->loadCollection(MediaMascotaCollection::class);
            $mediaCol->procesarArchivoSeguimiento((int)$mascotaId, $tipoArchivo, $registroId ? (int)$registroId : null, $url);
        } catch (\Exception $e) {
            $this->request->setSession('error_upload', 'Hubo un error al subir el archivo al servidor.');
        }

        header('Location: /seguimiento?id=' . $mascotaId);
        exit;
    }

    public function guardarEncuesta()
    {
        $user = $this->request->session('user');
        if (empty($user) || $this->request->method() !== 'POST') {
            header('Location: /?auth=login');
            exit;
        }

        $adoptanteId = $user['id'];
        $mascotaId = $this->request->get('mascota_id');

        if (!$mascotaId) {
            header('Location: /perfil');
            exit;
        }

        $etapa = $this->request->get('etapa') ?? 'inicial';
        $conducta = $this->request->get('conducta');
        $sueno = $this->request->get('sueno');
        $alimentacion = $this->request->get('alimentacion');
        $progreso = $this->request->get('progreso_general');
        $comentarios = $this->request->get('comentarios');

        $encuesta = $this->loadModel(EncuestaAdaptacion::class);
        $encuesta->set([
            'mascota_id' => $mascotaId,
            'adoptante_id' => $adoptanteId,
            'fecha_encuesta' => date('Y-m-d H:i:s'),
            'etapa' => $etapa,
            'conducta' => $conducta,
            'sueno' => $sueno,
            'alimentacion' => $alimentacion,
            'progreso_general' => $progreso,
            'comentarios' => $comentarios
        ]);

        $encuesta->evaluarAlerta();

        // Insertar en la BD
        $encuestaCollection = $this->loadCollection(EncuestaAdaptacionCollection::class);
        $encuestaCollection->guardar($encuesta);

        header('Location: /seguimiento?id=' . $mascotaId);
        exit;
    }
}
