<?php

namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\MascotaCollection;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;

class QrController extends Controller
{
    public ?string $modelName = MascotaCollection::class;

    public function index()
    {
        $userSession = $this->request->session('user');

        // Requiere login
        if (empty($userSession)) {
            header('Location: /?auth=login');
            exit;
        }

        $titulo = "Generar codigo QR - PawMap";
        $metaDescription = "Genera el codigo QR de tu mascota para imprimir su chapita de identificacion inteligente.";
        $menu   = $this->menu;
        $redes  = $this->redes;

        $rolUsuario = $userSession['rol'] ?? '';
        $userId     = (int)($userSession['id'] ?? 0);
        $mascotas   = [];

        if ($rolUsuario === 'refugio') {
            // Mascotas DISPONIBLES publicadas por este refugio
            $mascotas = $this->model->getByRefugioId($userId);
        } elseif ($rolUsuario === 'adoptante') {
            // Mascotas que este adoptante adopto (solicitud APROBADA)
            $mascotas = $this->model->getAdopcionesByAdoptante($userId);
        }

        echo $this->twig->render('generar-qr.html.twig', get_defined_vars());
    }

    public function generarImagen()
    {
        $data = $this->request->get('data');
        if (empty($data)) {
            http_response_code(400);
            echo "Falta el parámetro 'data'";
            exit;
        }

        $options = new QROptions([
            'version'      => Version::AUTO,
            'outputType'   => QRMarkupSVG::class,
            'outputBase64' => false,
            'eccLevel'     => EccLevel::L,
            'scale'        => 5,
            'addQuietzone' => true,
            'quietzoneSize'=> 2,
            'cssClass'     => 'qr-code-img',
        ]);

        $qrcode = new QRCode($options);
        $svg = $qrcode->render($data);

        header('Content-Type: image/svg+xml');
        echo $svg;
        exit;
    }
}