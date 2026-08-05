<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\Mensaje;
use Paw\App\Models\MensajeCollection;
use Paw\App\Models\SolicitudAdopcionCollection;
use Paw\Core\MailService;

class ChatController extends Controller
{
    public ?string $modelName = MensajeCollection::class;

    public function verBandejaEntrada()
    {
        $usuario = $this->request->session('user');
        if (!$usuario) {
            header("Location: /perfil");
            exit;
        }

        $titulo = "Mensajes - PawMap";
        $mostrarChatWidget = false;
        echo $this->twig->render('mensajes.html.twig', get_defined_vars());
    }

    public function verChat()
    {
        $solicitudId = (int) $this->request->get('solicitud_id');
        $usuario = $this->request->session('user');
        if (!$solicitudId || !$usuario) {
            header("Location: /perfil");
            exit;
        }

        $solicitudesDb = $this->loadCollection(SolicitudAdopcionCollection::class);
        $solicitud = $solicitudesDb->getById($solicitudId);

        if (!$solicitud || $solicitud['estado'] !== 'APROBADA') {
            header("Location: /perfil");
            exit;
        }

        // Check if the current user is part of the request
        if ($usuario['id'] != $solicitud['adoptante_id'] && $usuario['id'] != $solicitud['refugio_id']) {
            header("Location: /perfil");
            exit;
        }

        $otroUsuarioId = ($usuario['id'] == $solicitud['adoptante_id']) ? $solicitud['refugio_id'] : $solicitud['adoptante_id'];

        $mensajesDb = $this->loadCollection(MensajeCollection::class);
        
        // Marcar leidos
        $mensajesDb->marcarComoLeidos($solicitudId, $usuario['id']);
        
        $mensajes = $mensajesDb->getMensajesPorSolicitud($solicitudId);

        $titulo = "Chat de Adopción - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;

        echo $this->twig->render('chat.html.twig', get_defined_vars());
    }

    public function enviarMensaje()
    {
        $usuario = $this->request->session('user');
        if (!$usuario) {
            http_response_code(403);
            echo json_encode(["error" => "No autorizado"]);
            exit;
        }

        $solicitudId = (int) $this->request->get('solicitud_id');
        $contenido = trim((string) $this->request->get('contenido'));

        if (!$solicitudId || !$contenido) {
            http_response_code(400);
            echo json_encode(["error" => "Datos inválidos"]);
            exit;
        }

        $solicitudesDb = $this->loadCollection(SolicitudAdopcionCollection::class);
        $solicitud = $solicitudesDb->getById($solicitudId);

        if (!$solicitud || $solicitud['estado'] !== 'APROBADA') {
            http_response_code(403);
            echo json_encode(["error" => "Chat no habilitado"]);
            exit;
        }

        if ($usuario['id'] != $solicitud['adoptante_id'] && $usuario['id'] != $solicitud['refugio_id']) {
            http_response_code(403);
            echo json_encode(["error" => "No autorizado"]);
            exit;
        }

        $destinatarioId = ($usuario['id'] == $solicitud['adoptante_id']) ? $solicitud['refugio_id'] : $solicitud['adoptante_id'];

        $mensaje = new Mensaje();
        $mensaje->set([
            'solicitud_id' => $solicitudId,
            'remitente_id' => $usuario['id'],
            'destinatario_id' => $destinatarioId,
            'contenido' => $contenido
        ]);

        $mensajesDb = $this->loadCollection(MensajeCollection::class);
        $resultado = $mensajesDb->guardar($mensaje);

        if ($resultado === true) {
            http_response_code(200);
            echo json_encode(["success" => true]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => $resultado]);
        }
        exit;
    }

    public function apiMensajes()
    {
        $usuario = $this->request->session('user');
        if (!$usuario) {
            http_response_code(403);
            exit;
        }

        $solicitudId = (int) $this->request->get('solicitud_id');

        $solicitudesDb = $this->loadCollection(SolicitudAdopcionCollection::class);
        $solicitud = $solicitudesDb->getById($solicitudId);

        if (!$solicitud || ($usuario['id'] != $solicitud['adoptante_id'] && $usuario['id'] != $solicitud['refugio_id'])) {
            http_response_code(403);
            exit;
        }

        $mensajesDb = $this->loadCollection(MensajeCollection::class);
        $mensajesDb->marcarComoLeidos($solicitudId, $usuario['id']);
        $mensajes = $mensajesDb->getMensajesPorSolicitud($solicitudId);

        $data = [];
        foreach ($mensajes as $m) {
            $data[] = [
                'id' => $m->fields['id'],
                'remitente_id' => $m->fields['remitente_id'],
                'remitente_nombre' => $m->fields['remitente_nombre'],
                'remitente_rol' => $m->fields['remitente_rol'],
                'contenido' => $m->fields['contenido'],
                'fecha_envio' => $m->fields['fecha_envio']
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function apiListarChatsActivos()
    {
        $usuario = $this->request->session('user');
        if (!$usuario) {
            http_response_code(403);
            exit;
        }

        $rol = $usuario['rol'] ?? 'adoptante';
        
        $solicitudesDb = $this->loadCollection(SolicitudAdopcionCollection::class);
        
        if ($rol === 'refugio') {
            $solicitudes = $solicitudesDb->getSolicitudesRefugio((int) $usuario['id']);
        } else {
            $solicitudes = $solicitudesDb->getSolicitudesAdoptante((int) $usuario['id']);
        }

        $mensajesDb = $this->loadCollection(MensajeCollection::class);

        $chats = [];
        foreach ($solicitudes as $s) {
            if ($s['estado'] === 'APROBADA') {
                $unread = $mensajesDb->getUnreadCountBySolicitud($s['id'], $usuario['id']);
                
                $nombreInterlocutor = ($rol === 'refugio') 
                    ? $s['adoptante_nombre'] . ' ' . $s['adoptante_apellido']
                    : $s['refugio_nombre'] ?? 'Refugio';
                
                $fotoInterlocutor = ($rol === 'refugio')
                    ? $s['adoptante_foto']
                    : $s['refugio_foto'];
                
                $chats[] = [
                    'solicitud_id' => $s['id'],
                    'mascota_nombre' => $s['mascota_nombre'],
                    'interlocutor' => $nombreInterlocutor,
                    'foto_interlocutor' => $fotoInterlocutor,
                    'unread' => $unread
                ];
            }
        }

        header('Content-Type: application/json');
        echo json_encode($chats);
        exit;
    }
}
