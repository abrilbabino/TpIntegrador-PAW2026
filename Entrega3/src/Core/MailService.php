<?php

namespace Paw\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService {
    public function enviarConfirmacionAdopcion($destinatario, $datosAdopcion) {
        global $config;
        global $log;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) {
                error_log("SMTP DEBUG [$level]: $str");
            };
            $mail->Host       = $config->get('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = $config->get('MAIL_USER');
            $mail->Password   = $config->get('MAIL_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $config->get('MAIL_PORT');

            $mail->setFrom($config->get('MAIL_USER'), 'PawMap');
            $mail->addAddress($destinatario);

            $mail->isHTML(false);
            $mail->Subject = "Solicitud de Adopción: " . ($datosAdopcion['nombre_mascota'] ?? 'Mascota');
            $mail->Body    = "Solicitante: " . ($datosAdopcion['nombre'] ?? '') . " " . ($datosAdopcion['apellido'] ?? '') . "\n" .
                             "Email: " . ($datosAdopcion['email'] ?? '') . "\n" .
                             "Mascota: " . ($datosAdopcion['nombre_mascota'] ?? '');

            $mail->send();
            return true;
        } catch (Exception $e) {
            $log->error("Error SMTP: {$mail->ErrorInfo}");
            return false;
        }
    }
}
