<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>Mi Perfil - PawMap</title>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="perfil-refugio-container <?= !empty($errores) ? 'is-editing' : '' ?>">
        <section class="perfil-refugio-header">
            <figure class="perfil-refugio-avatar">
                <?php if (!empty($user['foto'])): ?>
                    <img src="/assets/img/<?= htmlspecialchars($user['foto']) ?>" alt="">
                <?php else: ?>
                    <span class="material-symbols-outlined">person</span>
                <?php endif; ?>            
            </figure>

            <h1>Hola, <?= htmlspecialchars($user['nombre_usuario']) ?>!</h1>
            <p class="perfil-refugio-email"><?= htmlspecialchars($user['email'] ?? '') ?></p>
            <a href="/logout" class="perfil-refugio-logout">
                <span class="material-symbols-outlined">logout</span>
                Cerrar sesión
            </a>
        </section>

        <section class="perfil-refugio-datos">
            <header class="perfil-refugio-datos-header">
                <h2>Datos del Refugio</h2>
                <button type="button" id="btn-edit-refugio" class="btn-editar" title="Editar datos">
                    <span class="material-symbols-outlined">edit_square</span>
                </button>
            </header>
            <article class="perfil-refugio-datos-body">
                <form id="perfil-refugio-form" method="POST" action="/perfil/refugio/guardar" enctype="multipart/form-data" novalidate>
                    <ul class="perfil-refugio-datos-grid">
                        <li class="dato-refugio-item">
                            <span class="dato-refugio-label">Nombre de la institución:</span>
                            <span class="dato-refugio-valor static-value"><?= htmlspecialchars($refugio['nombre_institucion'] ?? '—') ?></span>
                            <input type="text" name="nombre_institucion" value="<?= htmlspecialchars($oldData['nombre_institucion'] ?? $refugio['nombre_institucion'] ?? '') ?>" data-original="<?= htmlspecialchars($refugio['nombre_institucion'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['nombre_institucion']) ? 'input-invalido' : '' ?>" minlength="2" maxlength="100" required>
                            <?php if (isset($errores['nombre_institucion'])): ?>
                                <span class="msg-error input-value"><?= htmlspecialchars($errores['nombre_institucion'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </li>
                        <li class="dato-refugio-item">
                            <span class="dato-refugio-label">Descripción:</span>
                            <div class="static-value dato-refugio-descripcion-container">
                                <p class="dato-refugio-valor descripcion-texto"><?= nl2br(htmlspecialchars($refugio['descripcion'] ?? '-')) ?></p>
                                <button type="button" class="btn-ver-mas-desc" style="display: none;">Ver más</button>
                            </div>
                            <input type="text" name="descripcion" value="<?= htmlspecialchars($oldData['descripcion'] ?? $refugio['descripcion'] ?? '') ?>" data-original="<?= htmlspecialchars($refugio['descripcion'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['descripcion']) ? 'input-invalido' : '' ?>" maxlength="500">
                            <?php if (isset($errores['descripcion'])): ?>
                                <span class="msg-error input-value"><?= htmlspecialchars($errores['descripcion'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </li>
                        <li class="dato-refugio-item">
                            <span class="dato-refugio-label">Teléfono:</span>
                            <span class="dato-refugio-valor static-value"><?= htmlspecialchars($refugio['telefono'] ?? '—') ?></span>
                            <input type="tel" name="telefono" value="<?= htmlspecialchars($oldData['telefono'] ?? $refugio['telefono'] ?? '') ?>" data-original="<?= htmlspecialchars($refugio['telefono'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['telefono']) ? 'input-invalido' : '' ?>" minlength="6" maxlength="20" pattern="^\+?[0-9\s\-]{6,20}$">
                            <?php if (isset($errores['telefono'])): ?>
                                <span class="msg-error input-value"><?= htmlspecialchars($errores['telefono'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </li>
                        <li class="dato-refugio-item">
                            <span class="dato-refugio-label">Mail:</span>
                            <span class="dato-refugio-valor static-value"><?= htmlspecialchars($user['email'] ?? '—') ?></span>
                            <input type="email" name="email" value="<?= htmlspecialchars($oldData['email'] ?? $user['email'] ?? '') ?>" data-original="<?= htmlspecialchars($user['email'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['email']) ? 'input-invalido' : '' ?>" required>
                            <?php if (isset($errores['email'])): ?>
                                <span class="msg-error input-value"><?= htmlspecialchars($errores['email'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </li>
                        <li class="dato-refugio-item">
                            <span class="dato-refugio-label">Alias:</span>
                            <span class="dato-refugio-valor static-value"><?= htmlspecialchars($refugio['alias'] ?? '—') ?></span>
                            <input type="text" name="alias" value="<?= htmlspecialchars($oldData['alias'] ?? $refugio['alias'] ?? '') ?>" data-original="<?= htmlspecialchars($refugio['alias'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['alias']) ? 'input-invalido' : '' ?>" minlength="4" maxlength="40">
                            <?php if (isset($errores['alias'])): ?>
                                <span class="msg-error input-value"><?= htmlspecialchars($errores['alias'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </li>
                        <li class="dato-refugio-item">
                            <span class="dato-refugio-label">CVU:</span>
                            <span class="dato-refugio-valor static-value"><?= htmlspecialchars($refugio['cvu'] ?? '—') ?></span>
                            <input type="text" name="cvu" value="<?= htmlspecialchars($oldData['cvu'] ?? $refugio['cvu'] ?? '') ?>" data-original="<?= htmlspecialchars($refugio['cvu'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['cvu']) ? 'input-invalido' : '' ?>" minlength="22" maxlength="22" pattern="^[0-9]{22}$">
                            <?php if (isset($errores['cvu'])): ?>
                                <span class="msg-error input-value"><?= htmlspecialchars($errores['cvu'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </li>
                    </ul>
                    <footer class="perfil-datos-acciones input-value">
                        <button type="submit" class="btn-guardar-perfil">Guardar</button>
                        <button type="button" id="btn-cancel-refugio" class="btn-cancelar-perfil">Cancelar</button>
                    </footer>
                </form>
                <figure class="perfil-refugio-mapa">
                    Mapa - no se como ponerlo todavia :/
                </figure>
            </article>
        </section>

        <!-- Navegación ancla (sticky) -->
        <nav class="perfil-refugio-nav">
            <a href="#sec-solicitudes-adopcion">
                <span class="material-symbols-outlined">assignment</span>
                Gestión de solicitudes
            </a>
            <a href="#sec-ubicacion">
                <span class="material-symbols-outlined">location_on</span>
                Ubicación: Agregar/Modificar
            </a>
            <a href="#sec-publicar">
                <span class="material-symbols-outlined">pets</span>
                Publicar Mascota
            </a>
            <a href="#sec-editar-mascota">
                <span class="material-symbols-outlined">refresh</span>
                Actualizar / Eliminar Mascota
            </a>
        </nav>
        
        <!-- Sección: Solicitudes -->
         <section class="perfil-refugio-seccion" id="sec-solicitudes-adopcion">
            <details class="perfil-dropdown" open>
            <summary> <h3>Solicitudes de adopción</h3> </summary>
            <?php if (empty($solicitudes)): ?>
                <article class="perfil-refugio-vacio">
                    <span class="material-symbols-outlined">mail</span>
                    <p>Todavía no recibiste solicitudes de adopción.</p>
                </article>
            <?php else: ?>
                <ul class="perfil-refugio-lista-adopcion">
                    <?php foreach ($solicitudes as $sol): ?>
                        <?php 
                        $estadoSol = strtoupper($sol['estado'] ?? 'PENDIENTE'); 
                        $disabled = ($estadoSol !== 'PENDIENTE') ? 'disabled' : '';
                        $estadoClass = 'estado-' . strtolower($estadoSol);
                        ?>
                        <li class="perfil-lista-item-adopcion" data-id="<?= htmlspecialchars($sol['id']) ?>">
                            <h4>
                                <?= htmlspecialchars($sol['mascota_nombre'] ?? 'Mascota') ?>
                                por <?= htmlspecialchars($sol['adoptante_nombre'] ?? '—') ?>
                                <?= htmlspecialchars($sol['adoptante_apellido'] ?? '—') ?>
                            </h4>
                            <span class="estado-solicitud <?= $estadoClass ?>">
                                Estado: <?= htmlspecialchars($sol['estado'] ?? '—') ?>  <br>
                                Fecha: <?= htmlspecialchars($sol['fecha'] ?? '—') ?>
                            </span>
                            <p>
                                Detalle de la mascota: <br>
                                <?= htmlspecialchars($sol['edad'] ?? '?') ?> año(s)
                                · <?= htmlspecialchars($sol['tamano'] ?? '—') ?>
                                · <?= htmlspecialchars($sol['temperamento'] ?? '—') ?>
                            </p>
                            <?php if ($estadoSol === 'PENDIENTE'): ?>
                                <button class="btn-rechazar" data-id="<?= htmlspecialchars($sol['id']) ?>">RECHAZAR</button>
                                <button class="btn-aceptar" data-id="<?= htmlspecialchars($sol['id']) ?>">ACEPTAR</button>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            </details>
        </section>
        
        <section class="perfil-refugio-ubicacion" id="sec-ubicacion">
            <summary> <h3>Ubicación: Agregar/Modificar</h3> </summary>
            <?php if (empty($ubicacion)): ?>
                <article class="perfil-refugio-vacio">
                    <span class="material-symbols-outlined">location_on</span>
                    <p>Todavía no estableciste una ubicación.</p>
                </article>
            <?php else: ?>
                <ul class="perfil-refugio-lista-ubicacion">
                        <li class="perfil-item-ubicacion">
                            <span class="material-symbols-outlined">location_on</span>
                            <p>
                                Provincia: <?= htmlspecialchars($sol['provincia'] ?? '?') ?>
                                · Ciudad: <?= htmlspecialchars($sol['ciudad'] ?? '?') ?>
                            </p>
                        </li>
                </ul>
            <?php endif; ?>
        </section>

        <section class="perfil-refugio-publicar" id="sec-publicar">
            <details class="perfil-dropdown" open>
            <summary> <h3>Publicar Mascota</h3> </summary>
            <p>Agrega los datos de la nueva mascota:</p>
        
            <form id="form-publicar-mascota" method="POST" action="/perfil/mascota/publicar" enctype="multipart/form-data">
                <label for="nombre">Nombre
                <input type="text" id="nombre" name="nombre" placeholder="Ingresá el nombre de la mascota"
                    value="<?= htmlspecialchars($oldMascota['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    minlength="2"
                    maxlength="60"
                    pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s'-]+$"
                    required
                    data-trim-required="true"
                    title="Solo se permiten letras, espacios, apóstrofes y guiones."
                    <?= !empty($erroresMascota['nombre']) ? 'data-server-error="' . htmlspecialchars($erroresMascota['nombre'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                </label>
                
                <fieldset class="publicar-mascota-grupo">
                    <legend>Especie</legend>
                    <?php $errorEspecie = $erroresMascota['especie'] ?? ''; ?>
                    <?php foreach ($especies as $e): ?>
                        <label class="especie-refugio-radio">
                            <input type="radio" name="especie" value="<?= htmlspecialchars($e->fields['especie'], ENT_QUOTES, 'UTF-8') ?>"
                                   required
                                   <?= (($oldMascota['especie'] ?? '') == $e->fields['especie']) ? 'checked' : '' ?>
                                   <?= !empty($errorEspecie) ? 'data-server-error="' . htmlspecialchars($errorEspecie, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                            <span><?= htmlspecialchars(ucfirst($e->fields['especie']), ENT_QUOTES, 'UTF-8') ?></span>
                        </label>
                        <?php $errorEspecie = ''; ?>
                    <?php endforeach; ?>
                </fieldset>
                
                <label for="fecha_nacimiento">Fecha de nacimiento
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                    value="<?= htmlspecialchars($oldMascota['fecha_nacimiento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    max="<?= date('Y-m-d') ?>"
                    data-no-future="true"
                    data-future-message="La fecha de nacimiento no puede ser futura."
                    <?= !empty($erroresMascota['fecha_nacimiento']) ? 'data-server-error="' . htmlspecialchars($erroresMascota['fecha_nacimiento'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                </label>

                <label for="descripcion_mascota">Descripción
                <textarea id="descripcion_mascota" name="descripcion_mascota" placeholder="Ingresá una descripción de la mascota" maxlength="500" <?= !empty($erroresMascota['descripcion_mascota']) ? 'data-server-error="' . htmlspecialchars($erroresMascota['descripcion_mascota'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>><?= htmlspecialchars($oldMascota['descripcion_mascota'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </label>

                <label for="tamanio">Tamaño
                <select id="tamanio" name="tamanio" required <?= !empty($erroresMascota['tamanio']) ? 'data-server-error="' . htmlspecialchars($erroresMascota['tamanio'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                    <option value="">Seleccioná un tamaño</option>
                    <?php foreach ($tamanos as $t): ?>
                            <option value="<?= htmlspecialchars($t->fields['tamano'], ENT_QUOTES, 'UTF-8') ?>" <?= (($oldMascota['tamanio'] ?? '') == $t->fields['tamano']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($t->fields['tamano']), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                </select>
                </label>

                <label for="sexo">Sexo
                <select id="sexo" name="sexo" required <?= !empty($erroresMascota['sexo']) ? 'data-server-error="' . htmlspecialchars($erroresMascota['sexo'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                    <option value="macho" <?= (($oldMascota['sexo'] ?? '') === 'macho') ? 'selected' : '' ?>>Macho</option>
                    <option value="hembra" <?= (($oldMascota['sexo'] ?? '') === 'hembra') ? 'selected' : '' ?>>Hembra</option>
                </select>
                </label>

                <label for="temperamento">Temperamento
                <select id="temperamento" name="temperamento" required <?= !empty($erroresMascota['temperamento']) ? 'data-server-error="' . htmlspecialchars($erroresMascota['temperamento'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                    <option value="">Seleccioná un temperamento</option>
                    <?php foreach ($temperamentos as $t): ?>
                            <option value="<?= htmlspecialchars($t->fields['temperamento'], ENT_QUOTES, 'UTF-8') ?>" <?= (($oldMascota['temperamento'] ?? '') == $t->fields['temperamento']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($t->fields['temperamento']), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                </select>
                </label>

                <label for="esterilizado">¿Está esterilizado?
                <select id="esterilizado" name="esterilizado" required <?= !empty($erroresMascota['esterilizado']) ? 'data-server-error="' . htmlspecialchars($erroresMascota['esterilizado'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                    <option value="si" <?= (($oldMascota['esterilizado'] ?? '') === 'si') ? 'selected' : '' ?>>Sí</option>
                    <option value="no" <?= (($oldMascota['esterilizado'] ?? 'no') !== 'si') ? 'selected' : '' ?>>No</option>
                </select>
                </label>

                <label for="foto">Foto <small>(opcional, máx. 2 MB — JPG, PNG, WEBP)</small>
                <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp"
                    data-max-file-size="2097152"
                    data-max-file-message="La imagen no puede superar 2 MB."
                    data-file-types-message="Archivo no válido. Solo JPG, PNG o WEBP."
                    <?= !empty($erroresMascota['foto']) ? 'data-server-error="' . htmlspecialchars($erroresMascota['foto'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                </label>

                <button type="submit" id="btn-publicar-mascota">Publicar</button>
            </form>
            </details>
        </section>

        <section class="perfil-editar-mascota" id="sec-editar-mascota">
            <details class="perfil-dropdown" open>
            <summary><h2>
                <span class="material-symbols-outlined">pets</span>
                Actualizar/Eliminar Mascota
            </h2></summary>
            <?php if (empty($mascotas)): ?>
                <article class="perfil-vacio">
                    <span class="material-symbols-outlined">pets</span>
                    <p>Todavía no tenés mascotas publicadas.</p>
                </article>
            <?php else: ?>
                <ul class="perfil-refugio-grid">
                    <?php foreach ($mascotas as $mascota): ?>
                        <li class="tarjeta-refugio-mascota">
                            <a href="/mascota?id=<?= htmlspecialchars($mascota->fields['id']) ?>" class="tarjeta-refugio-mascota-link" title="Ver detalle de <?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota') ?>">
                                <h4><?= htmlspecialchars($mascota->fields['nombre'] ?? 'Sin nombre') ?></h4>
                                <p>
                                    <?= htmlspecialchars($mascota->fields['edad'] ?? '?') ?> año(s)
                                    · <?= htmlspecialchars($mascota->fields['tamano'] ?? '—') ?>
                                    · <?= htmlspecialchars($mascota->fields['temperamento'] ?? '—') ?>
                                </p>
                            </a>
                            <a href="/mascota/editar?id=<?= htmlspecialchars($mascota->fields['id']) ?>" class="btn-editar" title="Editar datos">
                               <span class="material-symbols-outlined">edit_square</span>
                           </a>
                            <a href="/perfil/eliminar" class="btn-eliminar-mascota" title="Eliminar mascota">
                                <span class="material-symbols-outlined">delete</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            </details>
        </section>

        <!-- Sección: Solicitudes para este refugio -->
        <section class="perfil-seccion" id="sec-solicitudes">
            <h3>
                <span class="material-symbols-outlined">mail</span>
                Solicitudes Recibidas
            </h3>
            <?php if (empty($solicitudes)): ?>
                <article class="perfil-vacio">
                    <span class="material-symbols-outlined">mail</span>
                    <p>Todavía no recibiste solicitudes de adopción.</p>
                </article>
            <?php else: ?>
                <ul class="perfil-lista">
                    <?php foreach ($solicitudes as $sol): ?>
                        <li class="perfil-lista-item">
                            <h4>Mascota: <?= htmlspecialchars($sol['mascota_nombre'] ?? 'Mascota') ?></h4>
                            <p>
                                Adoptante: <?= htmlspecialchars($sol['adoptante_nombre'] . ' ' . $sol['adoptante_apellido']) ?>
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
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
