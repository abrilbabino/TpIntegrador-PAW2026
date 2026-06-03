<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/donacion-exito.css" />
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <title>Donación Procesada - PawMap</title>
</head>
<body>
<?php require __DIR__ . '/barra-navegacion.view.php'; ?>
 
<main class="contenedor-exito">
    <span style="font-size: 4rem;">🎉</span>
    <h2>¡Muchas gracias por tu compromiso!</h2>
    <p>Tu intención de donar <strong>$<?= $monto ?></strong> a <strong><?= htmlspecialchars($refugio->getNombre(), ENT_QUOTES, 'UTF-8') ?></strong> fue registrada.</p>
    <hr>
 
    <?php if ($valores['metodo_pago'] === 'transferencia'): ?>
        <section class="info-transferencia">
            <h3>Próximo paso: Realizá la transferencia</h3>
            <p>Para completar tu donación, transferí el monto desde tu home banking o billetera virtual (Mercado Pago, MODO, Ualá, etc.) a la cuenta del refugio:</p>
            <p><strong>Refugio:</strong> <?= htmlspecialchars($refugio->getNombre(), ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>ALIAS:</strong> <span class="codigo-inline"><?= htmlspecialchars((string)$refugio->getAlias(), ENT_QUOTES, 'UTF-8') ?></span></p>
            <p><strong>CVU:</strong> <span class="codigo-inline"><?= htmlspecialchars((string)$refugio->getCvu(), ENT_QUOTES, 'UTF-8') ?></span></p>
            <?php if (isset($comprobanteStatus)): ?>
                <div class="status-comprobante <?= $comprobanteStatus['success'] ? 'status-exito' : 'status-error' ?>" style="margin-top: 20px; padding: 15px; border-radius: 8px; text-align: center; font-weight: 600; <?= $comprobanteStatus['success'] ? 'background-color: var(--color-verde-claro); color: var(--color-verde); border: 1px solid var(--color-verde);' : 'background-color: #ffeaea; color: var(--color-rojo); border: 1px solid var(--color-rojo);' ?>">
                    <?php if ($comprobanteStatus['success']): ?>
                        <span>¡Comprobante enviado al email del refugio (<?= htmlspecialchars($refugio->getEmail(), ENT_QUOTES, 'UTF-8') ?>) con éxito!</span>
                    <?php else: ?>
                        <span>Error al enviar el comprobante: <?= htmlspecialchars($comprobanteStatus['error'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <form action="/enviar-comprobante" method="POST" enctype="multipart/form-data" style="margin: 0; padding: 0;">
                    <input type="hidden" name="refugio_id" value="<?= $refugio->getId() ?>">
                    <input type="hidden" name="monto" value="<?= htmlspecialchars((string)($valores['monto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <fieldset class="comprobante-wrapper">
                        <legend>¿Ya transferiste? Adjuntá el comprobante (Opcional):</legend>
                        <input type="file" name="comprobante" accept="image/*,.pdf" required>
                        <button type="submit" class="btn-comprobante">Enviar Comprobante</button>
                    </fieldset> 
                </form>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <section class="info-mp">
            <p>El pago fue procesado a través de Mercado Pago. Deberías recibir el correo de confirmación de la plataforma en unos instantes.</p>
        </section>
    <?php endif; ?>
 
    <a href="/donar">← Volver a donaciones</a>
</main>
 
<?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>