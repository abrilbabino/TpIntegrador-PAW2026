<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\Core\MailService;

class AdopcionController extends Controller
{
    public ?string $modelName = \Paw\App\Models\MascotaCollection::class;

    public function formulario()
    {
        $request = $this->request;
        $menu = $this->menu;
        $redes = $this->redes;
        $errores = $_SESSION['errores'] ?? [];
        unset($_SESSION['errores']);

        $id = $request->get('id');
        if (!$id) {
            header('Location: /adoptar');
            exit;
        }

        try {
            $mascota = $this->model->get($id);
        } catch (\Exception $e) {
            error_log("ERROR formulario(): " . $e->getMessage());
            header('Location: /adoptar');
            exit;
        }

        require $this->viewsDir . '/formulario-adopcion.view.php';
    }

    public function enviar()
    {
        $request = $this->request;
        $mascota_id = $request->get('mascota_id');

        if (!$mascota_id) {
            header('Location: /adoptar');
            exit;
        }

        try {
            $mascota = $this->model->get($mascota_id);
        } catch (\Exception $e) {
            error_log("ERROR enviar() al cargar mascota: " . $e->getMessage());
            header('Location: /formulario-adopcion?id=' . $mascota_id);
            exit;
        }

        $nombre = trim($request->get('nombre') ?? '');
        $apellido = trim($request->get('apellido') ?? '');
        $email = trim($request->get('email') ?? '');

        if (!$nombre || !$apellido || !$email) {
            header('Location: /formulario-adopcion?id=' . $mascota_id);
            exit;
        }

        // Enviar mail primero
        try {
            global $config;
            $mailService = new MailService();
            $datosAdopcion = [
                'nombre_mascota' => $mascota->fields['nombre'],
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $email,
            ];
            error_log("DEBUG enviar() - Intentando enviar mail");
            $result = $mailService->enviarConfirmacionAdopcion($config->get('MAIL_PERSONAL'), $datosAdopcion);
            error_log("DEBUG enviar() - Mail enviado: " . var_export($result, true));
        } catch (\Exception $e) {
            error_log("ERROR enviar() al enviar mail: " . $e->getMessage());
        }

        try {
            $stmt = $this->model->getQueryBuilder()->getConnection()->prepare(
                "INSERT INTO solicitud_de_adopcion (mascota_id, refugio_id, fecha, estado) 
                 VALUES (:mascota_id, :refugio_id, CURRENT_TIMESTAMP, 'PENDIENTE')"
            );
            $stmt->bindValue(':mascota_id', $mascota_id, \PDO::PARAM_INT);
            $stmt->bindValue(':refugio_id', $mascota->fields['refugio_id'], \PDO::PARAM_INT);
            $stmt->execute();
            error_log("DEBUG enviar() - Solicitud insertada OK");
        } catch (\Exception $e) {
            error_log("ERROR enviar() al insertar: " . $e->getMessage());
        }

        header('Location: /adopcion-exitosa');
        exit;
    }
}
