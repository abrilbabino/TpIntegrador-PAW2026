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
            header("Location: /");
            exit;
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

            header("Location: /?resena_guardada=1");
            exit;
        } catch (InvalidValueFormatException $e) {
            $errorUrl = urlencode($e->getMessage());
            header("Location: /?error_resena={$errorUrl}");
            exit;
        }
    }
}
