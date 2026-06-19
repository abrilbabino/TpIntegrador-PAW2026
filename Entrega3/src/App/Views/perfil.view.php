<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>Mi Perfil - PawMap</title>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="perfil-container <?= !empty($errores) ? 'is-editing' : '' ?>">


        <!-- Cabecera -->
        <section class="perfil-header">
            <figure class="perfil-avatar-wrapper">
                <?php 
                $fotoPerfil = $user['foto_perfil'] ?? '';
                $avatarUrl = '';
                if (!empty($fotoPerfil)) {
                    $pathFisico = __DIR__ . '/../../../public/assets/img/' . $fotoPerfil;
                    if (file_exists($pathFisico) && is_file($pathFisico)) {
                        $avatarUrl = '/assets/img/' . htmlspecialchars($fotoPerfil);
                    }
                }
                ?>
                <span id="preview-placeholder" class="avatar-placeholder <?= !empty($avatarUrl) ? 'hidden' : '' ?>">
                    <span class="material-symbols-outlined">person</span>
                </span>
                <?php if (!empty($avatarUrl)): ?>
                    <img id="image-preview" src="<?= $avatarUrl ?>" data-original="<?= $avatarUrl ?>" alt="Foto de perfil">
                <?php else: ?>
                    <img id="image-preview" src="" data-original="" alt="Foto de perfil" class="hidden">
                <?php endif; ?>
                <figcaption class="avatar-hover-overlay">
                    <span class="material-symbols-outlined">photo_camera</span>
                </figcaption>
            </figure>

            <h1>Hola, <?= htmlspecialchars($user['nombre_usuario']) ?>!</h1>
            <p class="perfil-email"><?= htmlspecialchars($user['email'] ?? '') ?></p>
            <a href="/logout" class="perfil-logout">
                <span class="material-symbols-outlined">logout</span>
                Cerrar sesión
            </a>
        </section>

        <!-- Datos personales (formulario de edición) -->
        <form id="perfil-form" method="POST" action="/perfil/guardar" enctype="multipart/form-data" class="perfil-datos" novalidate>
            <header class="perfil-datos-header">
                <h2>Datos Personales</h2>
                <button type="button" id="btn-edit-perfil" class="btn-editar" title="Editar datos">
                    <span class="material-symbols-outlined">edit_square</span>
                </button>
            </header>

            <input type="file" id="foto_perfil_o_logo" name="foto_perfil_o_logo" accept="image/*" class="hidden-input">
            <input type="hidden" id="eliminar_foto" name="eliminar_foto" value="0">
            <?php if (isset($errores['foto_perfil_o_logo'])): ?>
                <aside class="alerta-error" role="alert">
                    <span class="material-symbols-outlined">error</span>
                    <section>
                        <strong>Error con la foto de perfil:</strong>
                        <p><?= htmlspecialchars($errores['foto_perfil_o_logo'], ENT_QUOTES, 'UTF-8') ?></p>
                    </section>
                </aside>
            <?php endif; ?>

            <ul class="perfil-datos-grid">
                <li class="dato-item">
                    <span class="dato-label">Nombre de Usuario *</span>
                    <span class="dato-valor static-value"><?= htmlspecialchars($user['nombre_usuario'] ?? '—') ?></span>
                    <input type="text" name="nombre_usuario" value="<?= htmlspecialchars($oldData['nombre_usuario'] ?? $user['nombre_usuario'] ?? '') ?>" data-original="<?= htmlspecialchars($user['nombre_usuario'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['nombre_usuario']) ? 'input-invalido' : '' ?>" minlength="4" maxlength="20" pattern="[a-zA-Z0-9_.-]+" required <?= isset($errores['nombre_usuario']) ? 'data-server-error="' . htmlspecialchars($errores['nombre_usuario'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Nombre *</span>
                    <span class="dato-valor static-value"><?= htmlspecialchars($adoptante['nombre'] ?? '—') ?></span>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($oldData['nombre'] ?? $adoptante['nombre'] ?? '') ?>" data-original="<?= htmlspecialchars($adoptante['nombre'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['nombre']) ? 'input-invalido' : '' ?>" minlength="2" maxlength="50" pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" required <?= isset($errores['nombre']) ? 'data-server-error="' . htmlspecialchars($errores['nombre'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Apellido *</span>
                    <span class="dato-valor static-value"><?= htmlspecialchars($adoptante['apellido'] ?? '—') ?></span>
                    <input type="text" name="apellido" value="<?= htmlspecialchars($oldData['apellido'] ?? $adoptante['apellido'] ?? '') ?>" data-original="<?= htmlspecialchars($adoptante['apellido'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['apellido']) ? 'input-invalido' : '' ?>" minlength="2" maxlength="50" pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" required <?= isset($errores['apellido']) ? 'data-server-error="' . htmlspecialchars($errores['apellido'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Mail *</span>
                    <span class="dato-valor static-value"><?= htmlspecialchars($user['email'] ?? '—') ?></span>
                    <input type="email" name="email" value="<?= htmlspecialchars($oldData['email'] ?? $user['email'] ?? '') ?>" data-original="<?= htmlspecialchars($user['email'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['email']) ? 'input-invalido' : '' ?>" required <?= isset($errores['email']) ? 'data-server-error="' . htmlspecialchars($errores['email'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                </li>
                <li class="dato-item">
                    <span class="dato-label">DNI *</span>
                    <span class="dato-valor static-value"><?= htmlspecialchars($adoptante['dni'] ?? '—') ?></span>
                    <input type="text" name="dni" value="<?= htmlspecialchars($oldData['dni'] ?? $adoptante['dni'] ?? '') ?>" data-original="<?= htmlspecialchars($adoptante['dni'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['dni']) ? 'input-invalido' : '' ?>" minlength="7" maxlength="10" pattern="^[0-9\.]{7,10}$" required <?= isset($errores['dni']) ? 'data-server-error="' . htmlspecialchars($errores['dni'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Fecha de Nacimiento</span>
                    <span class="dato-valor static-value"><?= htmlspecialchars($adoptante['fecha_de_nacimiento'] ?? 'dd / mm / aaaa') ?></span>
                    <input type="date" name="fecha_de_nacimiento" value="<?= htmlspecialchars($oldData['fecha_de_nacimiento'] ?? $adoptante['fecha_de_nacimiento'] ?? '') ?>" data-original="<?= htmlspecialchars($adoptante['fecha_de_nacimiento'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['fecha_de_nacimiento']) ? 'input-invalido' : '' ?>" <?= isset($errores['fecha_de_nacimiento']) ? 'data-server-error="' . htmlspecialchars($errores['fecha_de_nacimiento'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Teléfono</span>
                    <span class="dato-valor static-value"><?= htmlspecialchars($user['contacto'] ?? '—') ?></span>
                    <input type="tel" name="contacto" value="<?= htmlspecialchars($oldData['contacto'] ?? $user['contacto'] ?? '') ?>" data-original="<?= htmlspecialchars($user['contacto'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['contacto']) ? 'input-invalido' : '' ?>" minlength="6" maxlength="20" pattern="^\+?[0-9\s\-]{6,20}$" <?= isset($errores['contacto']) ? 'data-server-error="' . htmlspecialchars($errores['contacto'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                </li>
                <li class="dato-item input-value">
                    <span class="dato-label">Contraseña Actual</span>
                    <input type="password" name="contrasena_actual" placeholder="Requerida para cambiar contraseña" class="dato-valor-input <?= isset($errores['contrasena_actual']) ? 'input-invalido' : '' ?>" data-original="" <?= isset($errores['contrasena_actual']) ? 'data-server-error="' . htmlspecialchars($errores['contrasena_actual'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                </li>
                <li class="dato-item input-value">
                    <span class="dato-label">Nueva Contraseña</span>
                    <input type="password" name="contrasena" placeholder="Nueva contraseña (opcional)" class="dato-valor-input <?= isset($errores['contrasena']) ? 'input-invalido' : '' ?>" minlength="6" data-original="" <?= isset($errores['contrasena']) ? 'data-server-error="' . htmlspecialchars($errores['contrasena'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                </li>
            </ul>

            <footer class="perfil-datos-acciones">
                <button type="submit" class="btn-guardar-perfil">Guardar</button>
                <button type="button" id="btn-cancel-perfil" class="btn-cancelar-perfil">Cancelar</button>
            </footer>
        </form>

        <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
            <aside class="alerta-exito" role="status">
                <span class="material-symbols-outlined">check_circle</span>
                <section>
                    <strong>¡Cambios guardados!</strong>
                    <p>Tu perfil se ha actualizado correctamente.</p>
                </section>
            </aside>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <aside class="alerta-error" role="alert">
                <span class="material-symbols-outlined">error</span>
                <section>
                    <strong>Ocurrió un inconveniente</strong>
                    <?php if ($_GET['error'] === 'campos_obligatorios'): ?>
                        <p>Por favor, completá todos los campos obligatorios (*).</p>
                    <?php elseif ($_GET['error'] === 'usuario_existente'): ?>
                        <p>El nombre de usuario elegido ya se encuentra en uso por otra persona.</p>
                    <?php else: ?>
                        <p>Ocurrió un error al guardar los cambios. Intentalo de nuevo.</p>
                    <?php endif; ?>
                </section>
            </aside>
        <?php endif; ?>

        <!-- Navegación ancla (sticky) -->
        <nav class="perfil-nav">
            <a href="#sec-favoritos">
                <span class="material-symbols-outlined">favorite</span>
                Favoritos
            </a>
            <a href="#sec-solicitudes">
                <span class="material-symbols-outlined">mail</span>
                Solicitudes
            </a>
            <a href="#sec-adopciones">
                <span class="material-symbols-outlined">pets</span>
                Adopciones
            </a>
        </nav>

        <!-- Sección: Favoritos -->
        <section class="perfil-seccion sec-favoritos" id="sec-favoritos">
            <h3>Favoritos</h3>
            <?php if (empty($favoritos)): ?>
                <article class="perfil-vacio">
                    <span class="material-symbols-outlined">pets</span>
                    <p>Todavía no tenés mascotas favoritas.</p>
                    <a href="/adoptar" class="btn-explorar">Explorar mascotas</a>
                </article>
            <?php else: ?>
                <ul class="perfil-cards-grid">
                    <?php foreach ($favoritos as $fav): ?>
                        <li class="perfil-card">
                            <a href="/mascota?id=<?= htmlspecialchars($fav['id']) ?>" class="perfil-card-link" title="Ver detalle de <?= htmlspecialchars($fav['nombre'] ?? 'Mascota') ?>">
                                <figure class="perfil-card-img">
                                    <img
                                        src="/assets/img/<?= htmlspecialchars($fav['imagen'] ?? 'default-pet.jpg') ?>"
                                        alt="<?= htmlspecialchars($fav['nombre'] ?? 'Mascota') ?>"
                                    />
                                </figure>
                                <h4><?= htmlspecialchars($fav['nombre'] ?? 'Sin nombre') ?></h4>
                                <p>
                                    <?= htmlspecialchars($fav['edad'] ?? '?') ?> año(s)
                                    · <?= htmlspecialchars($fav['tamano'] ?? '—') ?>
                                    · <?= htmlspecialchars($fav['temperamento'] ?? '—') ?>
                                </p>
                            </a>
                            <form method="POST" action="/api/favorito/toggle" class="form-quitar-fav">
                                <input type="hidden" name="mascota_id" value="<?= htmlspecialchars($fav['id']) ?>" />
                                <button type="submit" class="btn-corazon activo" title="Quitar favorito">
                                    <span class="material-symbols-outlined">favorite</span>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <!-- Sección: Solicitudes -->
        <section class="perfil-seccion sec-solicitudes" id="sec-solicitudes">
            <h3>Solicitudes de adopción</h3>
            <?php if (empty($solicitudes)): ?>
                <article class="perfil-vacio">
                    <span class="material-symbols-outlined">mail</span>
                    <p>Todavía no hiciste solicitudes de adopción.</p>
                </article>
            <?php else: ?>
                <ul class="perfil-lista">
                    <?php foreach ($solicitudes as $sol): ?>
                        <li class="perfil-lista-item">
                            <h4><?= htmlspecialchars($sol['nombre'] ?? 'Mascota') ?></h4>
                            <p>
                                <?= htmlspecialchars($sol['edad'] ?? '?') ?> año(s)
                                · <?= htmlspecialchars($sol['tamano'] ?? '—') ?>
                                · <?= htmlspecialchars($sol['temperamento'] ?? '—') ?>
                            </p>
                            <span class="perfil-estado estado-<?= strtolower($sol['estado'] ?? 'pendiente') ?>">
                                <?= htmlspecialchars($sol['estado'] ?? 'PENDIENTE') ?>
                            </span>
                            <?php if (($sol['estado'] ?? '') === 'APROBADA'): ?>

                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <!-- Sección: Adopciones -->
        <section class="perfil-seccion sec-adopciones" id="sec-adopciones">
            <h3>Adopciones</h3>
            <?php if (empty($adopciones)): ?>
                <article class="perfil-vacio">
                    <span class="material-symbols-outlined">pets</span>
                    <p>Todavía no adoptaste ninguna mascota.</p>
                </article>
            <?php else: ?>
                <ul class="perfil-lista">
                    <?php foreach ($adopciones as $ad): ?>
                        <li class="perfil-lista-item">
                            <h4><?= htmlspecialchars($ad['nombre'] ?? 'Mascota') ?></h4>
                            <p>
                                <?= htmlspecialchars($ad['edad'] ?? '?') ?> año(s)
                                · <?= htmlspecialchars($ad['tamano'] ?? '—') ?>
                                · <?= htmlspecialchars($ad['temperamento'] ?? '—') ?>
                            </p>
                            <a href="/seguimiento?id=<?= $ad['id'] ?>" class="btn-ver-detalle" title="Ver detalle">
                                <span class="material-symbols-outlined">directions_walk</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

    </main>

    <dialog id="avatar-modal" class="avatar-modal hidden">
        <span id="avatar-modal-backdrop" class="avatar-modal-backdrop"></span>
        <section class="avatar-modal-content">
            <h3>Cambiar foto de perfil</h3>
            <button type="button" id="modal-upload-btn" class="modal-option-btn bold-btn">Subir foto</button>
            <button type="button" id="modal-delete-btn" class="modal-option-btn red-btn hidden">Eliminar foto actual</button>
            <button type="button" id="modal-cancel-btn" class="modal-option-btn cancel-btn">Cancelar</button>
        </section>
    </dialog>

    <?php require __DIR__ . '/footer.view.php'; ?>

</body>
</html>
