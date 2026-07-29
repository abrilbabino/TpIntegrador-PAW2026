<?php

namespace Paw\Core;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    /**
     * Configura y genera un PDF a partir de código HTML.
     * Devuelve el string binario del archivo PDF generado.
     */
    public function generarDesdeHtml(string $html, string $orientacion = 'portrait'): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientacion);
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Resuelve la ruta de la imagen (local o remota) y la convierte a Base64
     * para incrustarla de forma segura y robusta en el PDF.
     */
    public function imageToBase64(string $imgUrl, string $publicDir): string
    {
        if (empty($imgUrl)) {
            return '';
        }

        if (str_starts_with($imgUrl, 'http')) {
            return $imgUrl; // Si es remota (Ej. GCS), DomPDF se encarga al estar isRemoteEnabled=true
        }

        $relativePath = str_starts_with($imgUrl, '/assets/img/') ? $imgUrl : '/assets/img/' . ltrim($imgUrl, '/');
        $pathLocal = realpath($publicDir . $relativePath);
        
        if ($pathLocal && file_exists($pathLocal)) {
            $type = pathinfo($pathLocal, PATHINFO_EXTENSION);
            $data = file_get_contents($pathLocal);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        return '';
    }
}
