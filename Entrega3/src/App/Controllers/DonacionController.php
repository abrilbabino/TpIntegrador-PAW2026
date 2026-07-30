<?php
namespace Paw\App\Controllers;

use Paw\Core\Controller;
use Paw\App\Models\Refugio;
use Paw\App\Models\RefugioCollection;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class DonacionController extends Controller
{
    public ?string $modelName = RefugioCollection::class;
    
    public function index()
    {
        $titulo = "Donaciones - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;
        $errores = [];
        $valores = [];
        $refugios = $this->model->getAll();

        echo $this->twig->render('donacion.html.twig', get_defined_vars());
    }

    public function enviar()
    {
        $titulo = "Donaciones - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;

        $valores = [
            'refugio_id' => $this->request->get('refugio_id'),
            'monto' => $this->request->get('monto'),
            'metodo_pago' => $this->request->get('metodo_pago'),
        ];

        $errores = $this->validar($valores);

        if (!empty($errores)) {
            $refugios = $this->model->getAll();
            echo $this->twig->render('donacion.html.twig', get_defined_vars());
            return;
        }

        try {
            $refugio = $this->model->get((int) $valores['refugio_id']);
        } catch (\Exception $e) {
            $errores[] = "No se encontro el refugio seleccionado.";
            $refugios = $this->model->getAll();
            echo $this->twig->render('donacion.html.twig', get_defined_vars());
            return;
        }

        if ($valores['metodo_pago'] === 'mp') {
            try {
                $this->redirigirACheckoutMercadoPago($refugio, (float) $valores['monto']);
            } catch (MPApiException $e) {
                $this->log->error('Error creando preferencia de Mercado Pago', [
                    'status_code' => $e->getApiResponse()->getStatusCode(),
                    'response' => $e->getApiResponse()->getContent(),
                ]);
                $errores[] = "No se pudo iniciar el pago con Mercado Pago. Intenta nuevamente.";
            } catch (\Throwable $e) {
                $this->log->error('Error iniciando checkout de Mercado Pago', [
                    'message' => $e->getMessage(),
                ]);
                $errores[] = "No se pudo iniciar el pago con Mercado Pago. Intenta nuevamente.";
            }

            $refugios = $this->model->getAll();
            echo $this->twig->render('donacion.html.twig', get_defined_vars());
            return;
        }
        if ($valores['metodo_pago'] === 'transferencia') {
            $monto = number_format((float) $valores['monto'], 2, ',', '.');
            
            echo $this->twig->render('donacion.html.twig', [
                'titulo' => 'Donaciones - PawMap',
                'flash_type' => 'donacion',
                'flash_data' => [
                    'monto' => $monto,
                    'monto_raw' => $valores['monto'],
                    'metodo_pago' => $valores['metodo_pago'],
                    'refugio_nombre' => $refugio->getNombre(),
                    'refugio_alias' => $refugio->getAlias(),
                    'refugio_cvu' => $refugio->getCvu(),
                    'refugio_email' => $refugio->getEmail(),
                    'refugio_id' => $refugio->getId()
                ]
            ]);
            return;
        }

        $monto = number_format((float) $valores['monto'], 2, ',', '.');
        $metodoPago = $valores['metodo_pago'] === 'mp' ? 'Mercado Pago' : 'Transferencia bancaria';

        echo $this->twig->render('donacion.html.twig', [
            'titulo' => 'Donaciones - PawMap',
            'flash_type' => 'donacion',
            'flash_data' => [
                'monto' => $monto,
                'monto_raw' => $valores['monto'],
                'metodo_pago' => $valores['metodo_pago'],
                'refugio_nombre' => $refugio->getNombre(),
                'refugio_alias' => $refugio->getAlias(),
                'refugio_cvu' => $refugio->getCvu(),
                'refugio_email' => $refugio->getEmail(),
                'refugio_id' => $refugio->getId()
            ]
        ]);
        exit;
    }

    public function enviarComprobante()
    {
        $titulo = "Donación Procesada - PawMap";
        $menu = $this->menu;
        $redes = $this->redes;

        $refugioId = $this->request->get('refugio_id');
        $montoRaw = $this->request->get('monto');
        $monto = number_format((float) $montoRaw, 2, ',', '.');

        try {
            $refugio = $this->model->get((int) $refugioId);
        } catch (\Exception $e) {
            header("Location: /donar");
            return;
        }

        $comprobante = $_FILES['comprobante'] ?? null;
        $envioExitoso = false;
        $errorEnvio = null;

        if ($comprobante && $comprobante['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $comprobante['tmp_name'];
            $fileName = $comprobante['name'];

            $mailService = new \Paw\Core\MailService();
            $destinatario = $refugio->getEmail();

            if (!empty($destinatario)) {
                $datosDonacion = [
                    'monto' => $monto,
                    'refugio' => $refugio->getNombre()
                ];
                $envioExitoso = $mailService->enviarComprobanteDonacion($destinatario, $datosDonacion, $tmpPath, $fileName);
                if (!$envioExitoso) {
                    $errorEnvio = "Hubo un problema al enviar el correo al refugio.";
                }
            } else {
                $errorEnvio = "El refugio no tiene una dirección de correo registrada.";
            }
        } else {
            $errorEnvio = "No se pudo cargar el archivo del comprobante.";
        }

        $valores = [
            'metodo_pago' => 'transferencia',
            'monto' => $montoRaw,
        ];
        
        $comprobanteStatus = [
            'success' => $envioExitoso,
            'error' => $errorEnvio
        ];

        $_SESSION['flash_type'] = 'donacion';
        $_SESSION['flash_data'] = [
            'monto' => $monto,
            'monto_raw' => $montoRaw,
            'metodo_pago' => 'transferencia',
            'refugio_nombre' => $refugio->getNombre(),
            'refugio_alias' => $refugio->getAlias(),
            'refugio_cvu' => $refugio->getCvu(),
            'refugio_email' => $refugio->getEmail(),
            'refugio_id' => $refugio->getId(),
            'comprobanteStatus' => $comprobanteStatus
        ];
        header("Location: /donar");
    }


    private function validar(array $valores): array
    {
        $errores = [];

        if (empty($valores['refugio_id']) || !is_numeric($valores['refugio_id']) || (int) $valores['refugio_id'] <= 0) {
            $errores[] = "Selecciona un refugio valido.";
        }

        if (empty($valores['monto']) || !is_numeric($valores['monto']) || (float) $valores['monto'] <= 0) {
            $errores[] = "Ingresa un monto valido para donar.";
        }

        if (!in_array($valores['metodo_pago'], ['mp', 'transferencia'], true)) {
            $errores[] = "Selecciona un metodo de pago valido.";
        }

        return $errores;
    }
    //por ahora viene toda la plata para mi cuenta, cuando tenga el perfil del refugio hacemos el split
    private function redirigirACheckoutMercadoPago(Refugio $refugio, float $monto): void
    {
        $accessToken = getenv('MERCADO_PAGO_ACCESS_TOKEN') ?: '';

        MercadoPagoConfig::setAccessToken($accessToken);
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);

        // 1. Guardamos la configuración de errores actual
        $erroresOriginales = error_reporting();
        
        // 2. Apagamos temporalmente los avisos "Deprecated"
        error_reporting($erroresOriginales & ~E_DEPRECATED);

        try {
            $client = new PreferenceClient();
            $preference = $client->create([
                'items' => [
                    [
                        'title' => 'Donación a ' . $refugio->getNombre(),
                        'quantity' => 1,
                        'unit_price' => $monto,
                        'currency_id' => 'ARS',
                    ],
                ],
            ]);
        } finally {
            // 3. Restauramos el manejador de errores original del framework
            error_reporting($erroresOriginales);
        }

        if (empty($preference->init_point)) {
            throw new \RuntimeException('Mercado Pago no devolvio init_point.');
        }

        header('Location: ' . $preference->init_point);
        exit;
    }
}
