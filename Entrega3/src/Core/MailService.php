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

            $mail->isHTML(true);
            $mail->Subject = 'Nueva Solicitud de Adopción - PawMap';
            $mail->Body    = "
                <h2>Nueva Solicitud de Adopción</h2>
                <p><strong>Mascota:</strong> {$datosAdopcion['nombre_mascota']}</p>
                <p><strong>Adoptante:</strong> {$datosAdopcion['nombre']} {$datosAdopcion['apellido']}</p>
                <p><strong>Email:</strong> {$datosAdopcion['email']}</p>
                <p>Por favor, contactá al adoptante a la brevedad.</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            $log->error("Error SMTP: {$mail->ErrorInfo}");
            return false;
        }
    }
}
