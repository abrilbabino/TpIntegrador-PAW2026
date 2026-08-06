<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\ResenaCollection;
use Paw\App\Models\Resena;
use Paw\Core\Exceptions\InvalidValueFormatException;

class ResenaController extends Controller
{
    public ?string $modelName = ResenaCollection::class;

    public function guardar()
    {
        $request = $this->request;
        $userSession = $request->session('user');

        if (!$userSession || !isset($userSession['id'])) {
            $this->redireccionarALogin();
        }

        try {
            $resena = new Resena();
            $resena->set([
                'adoptante_id' => $userSession['id'],
                'mascota_id'   => $request->get('mascota_id'),
                'refugio_id'   => $request->get('refugio_id'),
                'calificacion' => $request->get('calificacion'),
                'comentario'   => $request->get('comentario'),
            ]);

            $this->model->guardarResena($resena);

            header("Location: /?resena_guardada=1#seccion-resenas");
            exit;
        } catch (InvalidValueFormatException $e) {
            $errorUrl = urlencode($e->getMessage());
            header("Location: /?error_resena={$errorUrl}#seccion-resenas");
            exit;
        }
    }

    public function editar()
    {
        $request = $this->request;
        $userSession = $request->session('user');

        if (!$userSession || !isset($userSession['id'])) {
            $this->redireccionarALogin();
        }

        $id = (int) $request->get('id');
        $calificacion = (int) $request->get('calificacion');
        $comentario = $request->get('comentario');

        try {
            $this->model->actualizarResena($id, $userSession['id'], $calificacion, $comentario);
            header("Location: /?resena_editada=1#seccion-resenas");
            exit;
        } catch (InvalidValueFormatException $e) {
            $errorUrl = urlencode($e->getMessage());
            header("Location: /?error_resena={$errorUrl}#seccion-resenas");
            exit;
        }
    }

    public function eliminar()
    {
        $request = $this->request;
        $userSession = $request->session('user');

        if (!$userSession || !isset($userSession['id'])) {
            $this->redireccionarALogin();
        }

        $id = (int) $request->get('id');

        try {
            $this->model->eliminarResena($id, $userSession['id']);
            header("Location: /?resena_eliminada=1#seccion-resenas");
            exit;
        } catch (\Exception $e) {
            $errorUrl = urlencode("Error al eliminar la reseña.");
            header("Location: /?error_resena={$errorUrl}#seccion-resenas");
            exit;
        }
    }
}
