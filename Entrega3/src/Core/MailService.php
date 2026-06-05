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
            $mail->CharSet = 'UTF-8';
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

    public function enviarContacto($destinatario, $datosContacto) {
        global $config;
        global $log;

        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host       = $config->get('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = $config->get('MAIL_USER');
            $mail->Password   = $config->get('MAIL_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $config->get('MAIL_PORT');

            $mail->setFrom($config->get('MAIL_USER'), 'PawMap Contacto');
            $mail->addAddress($destinatario);

            $mail->isHTML(false);
            $mail->Subject = "Nuevo mensaje de contacto: " . ($datosContacto['asunto'] ?? 'Sin Asunto');
            $mail->Body    = "Nombre: " . ($datosContacto['nombre'] ?? '') . "\n" .
                             "Email: " . ($datosContacto['email'] ?? '') . "\n" .
                             "Asunto: " . ($datosContacto['asunto'] ?? '') . "\n" .
                             "Mensaje: \n" . ($datosContacto['mensaje'] ?? '');
            $mail->send();
            return true;
        } catch (Exception $e) {
            $log->error("Error SMTP Contacto: {$mail->ErrorInfo}");
            return false;
        }
    }
  
  public function send($destinatario, $subject, $body) {
        global $config;
        global $log;

        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->SMTPDebug = 0; // Desactivar debug en el cron por defecto
            $mail->Host       = $config->get('MAIL_HOST');
            $mail->SMTPAuth   = true;
            $mail->Username   = $config->get('MAIL_USER');
            $mail->Password   = $config->get('MAIL_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $config->get('MAIL_PORT');

            $mail->setFrom($config->get('MAIL_USER'), 'PawMap');
            $mail->addAddress($destinatario);

            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            if (isset($log)) {
                $log->error("Error SMTP al enviar recordatorio: {$mail->ErrorInfo}");
            } else {
                error_log("Error SMTP al enviar recordatorio: {$mail->ErrorInfo}");
            }
            return false;
        }
    }

    public function enviarComprobanteDonacion($destinatario, $datosDonacion, $archivoTmpPath, $archivoNombre) {
        global $config;
        global $log;

        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host       = $config->get('MAIL_HOST');
            $mail->SMTPAuth   =   true;
            $mail->Username   = $config->get('MAIL_USER');
            $mail->Password   = $config->get('MAIL_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $config->get('MAIL_PORT');

            $mail->setFrom($config->get('MAIL_USER'), 'PawMap Donaciones');
            $mail->addAddress($destinatario);

            if ($archivoTmpPath && file_exists($archivoTmpPath)) {
                $mail->addAttachment($archivoTmpPath, $archivoNombre);
            }

            $mail->isHTML(true);
            $mail->Subject = "Nuevo Comprobante de Donación Recibido";
            
            $monto = htmlspecialchars($datosDonacion['monto'] ?? '0');
            $refugioNombre = htmlspecialchars($datosDonacion['refugio'] ?? 'Refugio');
            $mail->Body    = "<h2>¡Hola! Has recibido un nuevo comprobante de donación.</h2>" .
                             "<p>Un usuario ha enviado un comprobante de transferencia para tu refugio: <strong>$refugioNombre</strong>.</p>" .
                             "<p><strong>Monto registrado de la donación:</strong> $$monto</p>" .
                             "<p>El archivo adjunto contiene el comprobante correspondiente.</p>" .
                             "<br><hr><p>Este es un correo automático enviado por PawMap.</p>";

            $mail->send();
            return true;
        } catch (Exception $e) {
            if (isset($log)) {
                $log->error("Error SMTP al enviar comprobante de donación: {$mail->ErrorInfo}");
            }
            return false;
        }
    }
}

