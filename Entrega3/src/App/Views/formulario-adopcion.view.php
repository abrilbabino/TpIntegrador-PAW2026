<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>Formulario de Adopción - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main>
        <header>
            <h1>Formulario de Adopción</h1>
            <p>Completá los datos para iniciar el proceso</p>
        </header>

        <article class="formulario-adopcion">
            <section class="seccion-mascota">
                <figure class="imagen-principal">
                    <img src="/assets/img/<?= htmlspecialchars($mascota->fields['imagen'] ?? 'default-pet.jpg', ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?>">
                </figure>
                <nav class="carrusel-indicadores" aria-label="Controles de galería">
                    <?php 
                    $cantidadTotalFotos = 1 + count($fotosExtras ?? []); 
                    if ($cantidadTotalFotos > 1):
                        for ($i = 0; $i < $cantidadTotalFotos; $i++): 
                    ?>
                            <span class="item-carrusel <?= $i === 0 ? 'active' : '' ?>"></span>
                    <?php 
                        endfor; 
                    endif; 
                    ?>
                </nav>

                <section class="mas-fotos">
                    <h4>MÁS FOTOS</h4>
                    <ul class="grid-miniaturas">
                        <?php if (!empty($fotosExtras)): ?>
                            <?php foreach ($fotosExtras as $foto): ?>
                                <li>
                                    <img src="/assets/img/<?= htmlspecialchars($foto->fields['ruta_imagen'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto extra">
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay fotos adicionales.</p>
                        <?php endif; ?>
                    </ul>
                </section>
            </section>

            <aside class="info-formulario">
                <div class="drag-handle"></div>
                <header class="form-header">
                    <h2>Formulario de Adopción</h2>
                    <p class="form-subtitulo">Completá para adoptar</p>
                </header>

                <form method="POST" action="/formulario-adopcion/enviar" class="form-adopcion">
                    <input type="hidden" name="mascota_id" value="<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                    <fieldset>
                        <legend>Datos Personales</legend>

                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required
                               value="<?= htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errores['nombre'])): ?>
                            <span class="error-inline"><?= htmlspecialchars($errores['nombre']) ?></span>
                        <?php endif; ?>

                        <label for="apellido">Apellido</label>
                        <input type="text" id="apellido" name="apellido" required
                               value="<?= htmlspecialchars($_POST['apellido'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errores['apellido'])): ?>
                            <span class="error-inline"><?= htmlspecialchars($errores['apellido']) ?></span>
                        <?php endif; ?>

                        <label for="email">Mail</label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errores['email'])): ?>
                            <span class="error-inline"><?= htmlspecialchars($errores['email']) ?></span>
                        <?php endif; ?>
                    </fieldset>

                    <fieldset>
                        <legend>Contacto</legend>

                        <label for="telefono">Teléfono celular</label>
                        <input type="tel" id="telefono" name="telefono" required
                               value="<?= htmlspecialchars($_POST['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                        <label for="fecha_nacimiento">Fecha de nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    </fieldset>

                    <fieldset class="fieldset-contrato">
                        <label for="acepta_contrato" class="checkbox-label">
                            <input type="checkbox" id="acepta_contrato" name="acepta_contrato" required
                                   <?= isset($_POST['acepta_contrato']) ? 'checked' : '' ?>>
                            Acepto el <button type="button" class="btn-link" onclick="event.preventDefault(); document.getElementById('modal-contrato').showModal();">contrato de adopción y seguimiento sanitario</button>
                        </label>
                        <?php if (isset($errores['acepta_contrato'])): ?>
                            <span class="error-inline"><?= htmlspecialchars($errores['acepta_contrato']) ?></span>
                        <?php endif; ?>
                    </fieldset>

                    <button type="submit" class="btn-submit-adoptar">¡QUIERO ADOPTAR!</button>
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
</body>
</html>
