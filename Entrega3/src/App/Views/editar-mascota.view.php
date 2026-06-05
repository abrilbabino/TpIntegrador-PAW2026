<?php
$mascotaFields = $mascota->fields ?? [];
$nombreActual = $oldData['nombre'] ?? $mascotaFields['nombre'] ?? '';
$especieActual = $oldData['especie'] ?? $mascotaFields['especie'] ?? '';
$tamanioActual = $oldData['tamanio'] ?? $oldData['tamano'] ?? $mascotaFields['tamano'] ?? '';
$temperamentoActual = $oldData['temperamento'] ?? $mascotaFields['temperamento'] ?? '';
$sexoActual = strtolower($oldData['sexo'] ?? $mascotaFields['sexo'] ?? '');
$esterilizadoActual = $oldData['esterilizado'] ?? (((int)($mascotaFields['castrado'] ?? 0) === 1) ? 'si' : 'no');
$descripcionActual = $oldData['descripcion_mascota'] ?? $oldData['descripcion'] ?? $mascotaFields['descripcion'] ?? '';
$fechaNacimientoActual = $oldData['fecha_nacimiento'] ?? $mascotaFields['fecha_nacimiento'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>Editar Mascota - PawMap</title>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="perfil-container is-editing">
        <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
            <aside class="alerta-exito" role="status">
                <span class="material-symbols-outlined">check_circle</span>
                <section>
                    <strong>¡Cambios guardados!</strong>
                    <p>La mascota se ha actualizado correctamente.</p>
                </section>
            </aside>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <aside class="alerta-error" role="alert">
                <span class="material-symbols-outlined">error</span>
                <section>
                    <strong>Ocurrió un inconveniente</strong>
                    <p>Ocurrió un error al guardar los cambios. Intentalo de nuevo.</p>
                </section>
            </aside>
        <?php endif; ?>

        <form id="form-editar-mascota" method="POST" action="/mascota/editar/guardar" enctype="multipart/form-data" class="perfil-datos" novalidate>
            <header class="perfil-datos-header">
                <h2>Editar Mascota</h2>
            </header>

            <input type="hidden" name="id" value="<?= htmlspecialchars($mascotaFields['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <ul class="perfil-datos-grid">
                <li class="dato-item">
                    <span class="dato-label">Nombre *</span>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($nombreActual, ENT_QUOTES, 'UTF-8') ?>" class="dato-valor-input input-value <?= isset($errores['nombre']) ? 'input-invalido' : '' ?>" minlength="2" maxlength="60" pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s'-]+$" required data-trim-required="true" title="Solo se permiten letras, espacios, apóstrofes y guiones.">
                    <?php if (isset($errores['nombre'])): ?>
                        <span class="msg-error"><?= htmlspecialchars($errores['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </li>

                <li class="dato-item">
                    <span class="dato-label">Especie *</span>
                    <select name="especie" class="dato-valor-input input-value <?= isset($errores['especie']) ? 'input-invalido' : '' ?>" required>
                        <option value="">Seleccioná una especie</option>
                        <?php foreach ($especies as $e): ?>
                            <?php $especieValor = $e->fields['especie'] ?? ''; ?>
                            <option value="<?= htmlspecialchars($especieValor, ENT_QUOTES, 'UTF-8') ?>" <?= ($especieActual == $especieValor) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($especieValor), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['especie'])): ?>
                        <span class="msg-error"><?= htmlspecialchars($errores['especie'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </li>

                <li class="dato-item">
                    <span class="dato-label">Tamaño *</span>
                    <select name="tamanio" class="dato-valor-input input-value <?= isset($errores['tamanio']) ? 'input-invalido' : '' ?>" required>
                        <option value="">Seleccioná un tamaño</option>
                        <?php foreach ($tamanos as $t): ?>
                            <?php $tamanoValor = $t->fields['tamano'] ?? ''; ?>
                            <option value="<?= htmlspecialchars($tamanoValor, ENT_QUOTES, 'UTF-8') ?>" <?= ($tamanioActual == $tamanoValor) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($tamanoValor), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['tamanio'])): ?>
                        <span class="msg-error"><?= htmlspecialchars($errores['tamanio'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </li>

                <li class="dato-item">
                    <span class="dato-label">Temperamento *</span>
                    <select name="temperamento" class="dato-valor-input input-value <?= isset($errores['temperamento']) ? 'input-invalido' : '' ?>" required>
                        <option value="">Seleccioná un temperamento</option>
                        <?php foreach ($temperamentos as $t): ?>
                            <?php $temperamentoValor = $t->fields['temperamento'] ?? ''; ?>
                            <option value="<?= htmlspecialchars($temperamentoValor, ENT_QUOTES, 'UTF-8') ?>" <?= ($temperamentoActual == $temperamentoValor) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($temperamentoValor), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['temperamento'])): ?>
                        <span class="msg-error"><?= htmlspecialchars($errores['temperamento'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </li>

                <li class="dato-item">
                    <span class="dato-label">Sexo *</span>
                    <label>
                        <input type="radio" name="sexo" value="macho" required <?= ($sexoActual === 'macho') ? 'checked' : '' ?>>
                        Macho
                    </label>
                    <label>
                        <input type="radio" name="sexo" value="hembra" required <?= ($sexoActual === 'hembra') ? 'checked' : '' ?>>
                        Hembra
                    </label>
                    <?php if (isset($errores['sexo'])): ?>
                        <span class="msg-error"><?= htmlspecialchars($errores['sexo'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </li>

                <li class="dato-item">
                    <span class="dato-label">¿Está esterilizado? *</span>
                    <label>
                        <input type="radio" name="esterilizado" value="si" required <?= ($esterilizadoActual === 'si') ? 'checked' : '' ?>>
                        Sí
                    </label>
                    <label>
                        <input type="radio" name="esterilizado" value="no" required <?= ($esterilizadoActual === 'no') ? 'checked' : '' ?>>
                        No
                    </label>
                    <?php if (isset($errores['esterilizado'])): ?>
                        <span class="msg-error"><?= htmlspecialchars($errores['esterilizado'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </li>

                <li class="dato-item">
                    <span class="dato-label">Descripción</span>
                    <textarea name="descripcion_mascota" class="dato-valor-input input-value <?= isset($errores['descripcion_mascota']) ? 'input-invalido' : '' ?>" maxlength="500"><?= htmlspecialchars($descripcionActual, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <?php if (isset($errores['descripcion_mascota'])): ?>
                        <span class="msg-error"><?= htmlspecialchars($errores['descripcion_mascota'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </li>

                <li class="dato-item">
                    <span class="dato-label">Fecha de nacimiento</span>
                    <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($fechaNacimientoActual, ENT_QUOTES, 'UTF-8') ?>" max="<?= date('Y-m-d') ?>" data-no-future="true" data-future-message="La fecha de nacimiento no puede ser futura." class="dato-valor-input input-value <?= isset($errores['fecha_nacimiento']) ? 'input-invalido' : '' ?>">
                    <?php if (isset($errores['fecha_nacimiento'])): ?>
                        <span class="msg-error"><?= htmlspecialchars($errores['fecha_nacimiento'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </li>

                <li class="dato-item">
                    <span class="dato-label">Foto</span>
                    <input type="file" name="foto" accept="image/*" class="dato-valor-input input-value <?= isset($errores['foto']) ? 'input-invalido' : '' ?>" data-max-file-size="2097152" data-max-file-message="La imagen no puede superar 2 MB.">
                    <?php if (isset($errores['foto'])): ?>
                        <span class="msg-error"><?= htmlspecialchars($errores['foto'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </li>
            </ul>

            <footer class="perfil-datos-acciones">
                <button type="submit" class="btn-guardar-perfil">Guardar</button>
                <a href="/perfil" class="btn-cancelar-perfil">Cancelar</a>
            </footer>
        </form>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
