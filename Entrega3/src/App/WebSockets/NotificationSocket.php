<?php

namespace Paw\App\WebSockets;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class NotificationSocket implements MessageComponentInterface
{
    protected $clientes;
    protected $conexionesUsuarios; // Mapa: usuario_id -> SplObjectStorage de conexiones

    public function __construct()
    {
        $this->clientes = new \SplObjectStorage;
        $this->conexionesUsuarios = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clientes->attach($conn);
        echo "¡Nueva conexión! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $datos = json_decode($msg, true);
        if ($datos && isset($datos['type']) && $datos['type'] === 'auth' && isset($datos['usuario_id'])) {
            $usuarioId = (int)$datos['usuario_id'];
            if (!isset($this->conexionesUsuarios[$usuarioId])) {
                $this->conexionesUsuarios[$usuarioId] = new \SplObjectStorage;
            }
            $this->conexionesUsuarios[$usuarioId]->attach($from);
            // Guardar usuarioId en el objeto de conexión para limpiar al desconectar
            $from->usuarioId = $usuarioId;
            echo "Conexión {$from->resourceId} autenticada para el usuario {$usuarioId}\n";
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clientes->detach($conn);
        if (isset($conn->usuarioId) && isset($this->conexionesUsuarios[$conn->usuarioId])) {
            $this->conexionesUsuarios[$conn->usuarioId]->detach($conn);
            if ($this->conexionesUsuarios[$conn->usuarioId]->count() === 0) {
                unset($this->conexionesUsuarios[$conn->usuarioId]);
            }
        }
        echo "La conexión {$conn->resourceId} se ha desconectado\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Ha ocurrido un error: {$e->getMessage()}\n";
        $conn->close();
    }

    public function notificarUsuario(int $usuarioId, string $payloadMensaje)
    {
        if (isset($this->conexionesUsuarios[$usuarioId])) {
            foreach ($this->conexionesUsuarios[$usuarioId] as $cliente) {
                $cliente->send($payloadMensaje);
            }
        }
    }
}
