<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\RegistroSanitario;
use Paw\App\Models\EncuestaAdaptacion;
use Paw\App\Models\MascotaCollection;
use Paw\App\Models\RegistroSanitarioCollection;
use Paw\Core\Database\QueryBuilder;
use Exception;

class SeguimientoController extends Controller
{
    public ?string $modelName = EncuestaAdaptacion::class;

    public function index()
    {
        $user = $this->request->session('user');
        if (empty($user)) {
            header('Location: /iniciar-sesion');
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

        $qb = $this->model->getQueryBuilder();

        // Usamos la colección de mascotas para obtener objetos completos
        $mascotaCol = new MascotaCollection();
        $mascotaCol->setQueryBuilder($qb);
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
                $registroCol = new RegistroSanitarioCollection();
                $registroCol->setQueryBuilder($qb);
                $registros = $registroCol->getByMascota($mascotaId);
                
                // LÓGICA DEL BANNER DINÁMICO
                $estadoGlobal = 'dia'; // Asumimos 'Todo al día'
                $registrosPendientes = [];

                foreach ($registros as $r) {
                    if (strtolower($r->fields['estado']) === 'pendiente') {
                        $registrosPendientes[] = $r;
                        // Si está pendiente y la fecha ya pasó, alertamos
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

                // LÓGICA DE ENCUESTAS ESCALONADAS (ROADMAP)
                $fechaAdopcion = $mascotaSeleccionada->fields['fecha_adopcion'];
                $diasDesdeAdopcion = $fechaAdopcion ? floor((time() - strtotime($fechaAdopcion)) / 86400) : 0;
                
                // Buscar qué encuestas ya completó
                $sqlEncuestas = "SELECT etapa FROM encuesta_adopcion WHERE mascota_id = :mid AND adoptante_id = :aid";
                $encuestasRealizadas = $qb->rawQuery($sqlEncuestas, [':mid' => $mascotaId, ':aid' => $adoptanteId]);
                $etapasRealizadas = array_column($encuestasRealizadas, 'etapa');

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

        require $this->viewsDir . '/seguimiento.view.php';
    }

    public function subirArchivo()
    {
        $user = $this->request->session('user');
        if (empty($user) || $this->request->method() !== 'POST') {
            header('Location: /iniciar-sesion');
            exit;
        }

        $registroId = $this->request->get('registro_id');
        $tipoArchivo = $this->request->get('tipo_archivo') ?? 'comprobante';

        if (!$mascotaId || !$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
            header('Location: /seguimiento?id=' . $mascotaId);
            exit;
        }

        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreFinal = 'seguimiento_' . $mascotaId . '_' . time() . '.' . $extension;
        
        $directorioDestino = __DIR__ . '/../../../public/assets/img/uploads/';
        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        $rutaAbsoluta = $directorioDestino . $nombreFinal;
        $rutaRelativa = '/assets/img/uploads/' . $nombreFinal;

        if (move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
            $qb = $this->model->getQueryBuilder();
            
            if ($tipoArchivo === 'comprobante' && $registroId) {
                // Comprobante de registro sanitario
                $qb->actualizarArchivoRegistroSanitario($registroId, $rutaRelativa, date('Y-m-d'));
            } else {
                // Foto o Certificado General
                $qb->insert('media_mascota', [
                    'mascota_id' => $mascotaId,
                    'tipo' => $tipoArchivo === 'foto' ? 'foto_seguimiento' : 'certificado_med',
                    'url' => $rutaRelativa
                ]);
            }
        }

        header('Location: /seguimiento?id=' . $mascotaId);
        exit;
    }

    public function guardarEncuesta()
    {
        $user = $this->request->session('user');
        if (empty($user) || $this->request->method() !== 'POST') {
            header('Location: /iniciar-sesion');
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

        $encuesta = new EncuestaAdaptacion();
        $encuesta->setQueryBuilder($this->model->getQueryBuilder());
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
        $this->model->getQueryBuilder()->insert($encuesta->table, [
            'mascota_id' => $encuesta->fields['mascota_id'],
            'adoptante_id' => $encuesta->fields['adoptante_id'],
            'fecha_encuesta' => $encuesta->fields['fecha_encuesta'],
            'etapa' => $encuesta->fields['etapa'],
            'conducta' => $encuesta->fields['conducta'],
            'sueno' => $encuesta->fields['sueno'],
            'alimentacion' => $encuesta->fields['alimentacion'],
            'progreso_general' => $encuesta->fields['progreso_general'],
            'comentarios' => $encuesta->fields['comentarios'],
            'alerta_generada' => $encuesta->fields['alerta_generada'] ? 1 : 0
        ]);

        header('Location: /seguimiento?id=' . $mascotaId);
        exit;
    }
}
