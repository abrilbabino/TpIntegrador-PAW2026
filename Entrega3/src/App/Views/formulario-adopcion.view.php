<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/pawcarousel.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>Formulario de Adopción - PawMap</title>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main>
        <header class="hero-formulario">
            <h1>Formulario de Adopción</h1>
            <p>Completá los datos para iniciar el proceso</p>
        </header>

        <article class="seccion-detalle-mascota">
            <section class="galeria-mascota">
            <figure class="imagen-principal-desktop">
                <img src="/assets/img/<?= htmlspecialchars($mascota->fields['imagen'] ?? 'default-pet.jpg', ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?>">
            </figure>

            <?php if (!empty($mediaExtras)): ?>
                <h4>GALERÍA</h4>
            <?php endif; ?>

            <div class="carrusel-mascota" data-paw-carousel data-paw-effect="zoom" data-paw-miniaturas="false">
                
                <figure class="carrusel-slide-item slide-principal-movil">
                    <img src="/assets/img/<?= htmlspecialchars($mascota->fields['imagen'] ?? 'default-pet.jpg', ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?>">
                </figure>

                <?php if (!empty($mediaExtras)): ?>
                    <?php foreach ($mediaExtras as $media): ?>
                        <?php if ($media->tipo === 'video'): ?>
                            <figure class="carrusel-slide-item carrusel-slide--video">
                                <video controls autoplay loop preload="metadata" playsinline muted
                                    poster="<?= htmlspecialchars($media->poster ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <source src="/<?= htmlspecialchars($media->url, ENT_QUOTES, 'UTF-8') ?>" type="video/mp4">
                                </video>
                            </figure>
                        <?php else: ?>
                            <figure class="carrusel-slide-item">
                                <img src="/<?= htmlspecialchars($media->url, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            </figure>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </section>

            <aside class="info-formulario">
                <div class="drag-handle"></div>
                <header class="form-header">
                    <h2>Formulario de Adopción</h2>
                    <p class="form-subtitulo">Completá para adoptar</p>
                </header>

                <?php if (isset($errores['solicitud_duplicada'])): ?>
                    <aside class="alerta-error" role="alert">
                        <span class="material-symbols-outlined">error</span>
                        <section>
                            <strong>Solicitud duplicada</strong>
                            <p><?= htmlspecialchars($errores['solicitud_duplicada']) ?></p>
                        </section>
                    </aside>
                <?php endif; ?>

                <form method="POST" action="/formulario-adopcion/enviar" class="form-adopcion">
                    <input type="hidden" name="mascota_id" value="<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                    <fieldset>
                        <legend>Datos Personales</legend>

                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required
                               value="<?= htmlspecialchars($_POST['nombre'] ?? $adoptanteData['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= isset($errores['nombre']) ? 'data-server-error="' . htmlspecialchars($errores['nombre'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>

                        <label for="apellido">Apellido</label>
                        <input type="text" id="apellido" name="apellido" required
                               value="<?= htmlspecialchars($_POST['apellido'] ?? $adoptanteData['apellido'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= isset($errores['apellido']) ? 'data-server-error="' . htmlspecialchars($errores['apellido'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>

                        <label for="email">Mail</label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? $userData['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= isset($errores['email']) ? 'data-server-error="' . htmlspecialchars($errores['email'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                    </fieldset>

                    <fieldset>
                        <legend>Contacto</legend>

                        <label for="telefono">Teléfono celular</label>
                        <input type="tel" id="telefono" name="telefono" required
                               value="<?= htmlspecialchars($_POST['telefono'] ?? $userData['contacto'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        
                        <label for="fecha_nacimiento">Fecha de nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required
                               max="<?= date('Y-m-d') ?>" data-no-future="true" data-future-message="La fecha de nacimiento no puede ser futura."
                               value="<?= htmlspecialchars($_POST['fecha_nacimiento'] ?? $adoptanteData['fecha_de_nacimiento'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </fieldset>

                    <fieldset class="fieldset-contrato">
                        <label for="acepta_contrato" class="checkbox-label">
                            <input type="checkbox" id="acepta_contrato" name="acepta_contrato" required
                                   <?= isset($_POST['acepta_contrato']) ? 'checked' : '' ?> <?= isset($errores['acepta_contrato']) ? 'data-server-error="' . htmlspecialchars($errores['acepta_contrato'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                            Acepto el <button type="button" class="btn-link" onclick="event.preventDefault(); document.getElementById('modal-contrato').showModal();">contrato de adopción y seguimiento sanitario</button>
                        </label>
                    </fieldset>

                    <button type="submit" class="btn-submit-adoptar" <?= isset($errores['solicitud_duplicada']) ? 'disabled' : '' ?>>¡QUIERO ADOPTAR!</button>
                </form>
            </aside>
        </article>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>

    <dialog id="modal-contrato" class="modal-nativo">
        <header>
            <h2>Contrato de Adopción y Seguimiento Sanitario</h2>
        </header>
        <article class="contenido-contrato">
            <p>Al adoptar a esta mascota, te comprometes a:</p>
            <ul>
                <li>Brindarle un hogar seguro, alimento adecuado y atención veterinaria cuando lo requiera.</li>
                <li>Mantener al día su libreta sanitaria, aplicando las vacunas correspondientes.</li>
                <li>Notificar al refugio ante cualquier cambio de domicilio o pérdida.</li>
                <li>Permitir y facilitar el seguimiento de la mascota por parte del refugio, respondiendo a encuestas o visitas programadas para verificar su bienestar.</li>
            </ul>
            <p>El incumplimiento de este contrato puede resultar en la revocación de la adopción.</p>
        </article>
        <form method="dialog" class="acciones-modal">
            <button type="submit" class="btn-cerrar-modal">Entendido y Cerrar</button>
        </form>
    </dialog>

    <script src="/assets/js/mascota.js"></script>
</body>
</html>
