<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/chat.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>Coordinación de Adopción - PawMap</title>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="chat-main-container" data-user-id="<?= htmlspecialchars($usuario['id']) ?>">
        <section class="chat-box">
            <header class="chat-header">
                <figure class="chat-header-info">
                    <span class="material-symbols-outlined icon-header">account_circle</span>
                    <figcaption class="chat-header-text">
                        <h2>Coordinación de Adopción</h2>
                        <p>Solicitud #<?= htmlspecialchars($solicitudId) ?> - <?= htmlspecialchars($solicitud['estado']) ?></p>
                    </figcaption>
                </figure>
                <a href="/perfil" class="btn-volver-chat">
                    <span class="material-symbols-outlined">close</span>
                </a>
            </header>

            <ol class="chat-messages" id="chat-messages-container">
                <?php if (empty($mensajes)): ?>
                    <article class="chat-empty-state">
                        <span class="material-symbols-outlined">forum</span>
                        <p>¡El chat ya está habilitado!</p>
                        <span>Escribe tu primer mensaje para coordinar la adopción.</span>
                    </article>
                <?php else: ?>
                    <?php foreach ($mensajes as $msg): ?>
                        <?php 
                            $isMine = ($msg->fields['remitente_id'] == $usuario['id']);
                            $bubbleClass = $isMine ? 'message-mine' : 'message-other';
                            $time = date('H:i', strtotime($msg->fields['fecha_envio']));
                        ?>
                        <li class="chat-message-row <?= $bubbleClass ?>">
                            <article class="chat-bubble">
                                <?php if (!$isMine): ?>
                                    <span class="message-sender-name"><?= htmlspecialchars($msg->fields['remitente_nombre']) ?></span>
                                <?php endif; ?>
                                <p class="message-content"><?= nl2br(htmlspecialchars($msg->fields['contenido'])) ?></p>
                                <span class="message-time"><?= $time ?></span>
                            </article>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ol>

            <footer class="chat-input-area">
                <form id="chat-form" class="chat-form" action="/chat/enviar" method="POST">
                    <input type="hidden" id="solicitud_id" name="solicitud_id" value="<?= htmlspecialchars($solicitudId) ?>">
                    <textarea id="chat-input" class="chat-input" name="contenido" placeholder="Escribe un mensaje..." required rows="1"></textarea>
                    <button type="submit" class="btn-send-message" aria-label="Enviar mensaje">
                        <span class="material-symbols-outlined">send</span>
                    </button>
                </form>
            </footer>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
