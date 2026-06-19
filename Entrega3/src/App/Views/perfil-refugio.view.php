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
                <form id="perfil-refugio-form" class="perfil-refugio-form" method="POST" action="/perfil/refugio/guardar" enctype="multipart/form-data" novalidate>
                    <ul class="perfil-refugio-datos-grid">
                        <li class="dato-refugio-item">
                            <span class="dato-refugio-label">Nombre de la institución:</span>
                            <span class="dato-refugio-valor static-value"><?= htmlspecialchars($refugio['nombre_institucion'] ?? '—') ?></span>
                            <input type="text" name="nombre_institucion" value="<?= htmlspecialchars($oldData['nombre_institucion'] ?? $refugio['nombre_institucion'] ?? '') ?>" data-original="<?= htmlspecialchars($refugio['nombre_institucion'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['nombre_institucion']) ? 'input-invalido' : '' ?>" minlength="2" maxlength="100" required <?= isset($errores['nombre_institucion']) ? 'data-server-error="' . htmlspecialchars($errores['nombre_institucion'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                        </li>
                        <li class="dato-refugio-item">
                            <span class="dato-refugio-label">Descripción:</span>
                            <div class="static-value dato-refugio-descripcion-container">
                                <p class="dato-refugio-valor descripcion-texto"><?= nl2br(htmlspecialchars($refugio['descripcion'] ?? '-')) ?></p>
                                <button type="button" class="btn-ver-mas-desc oculto">Ver más</button>
                            </div>
                            <input type="text" name="descripcion" value="<?= htmlspecialchars($oldData['descripcion'] ?? $refugio['descripcion'] ?? '') ?>" data-original="<?= htmlspecialchars($refugio['descripcion'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['descripcion']) ? 'input-invalido' : '' ?>" maxlength="500" <?= isset($errores['descripcion']) ? 'data-server-error="' . htmlspecialchars($errores['descripcion'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                        </li>
                        <li class="dato-refugio-item">
                            <span class="dato-refugio-label">Teléfono:</span>
                            <span class="dato-refugio-valor static-value"><?= htmlspecialchars($refugio['telefono'] ?? '—') ?></span>
                            <input type="tel" name="telefono" value="<?= htmlspecialchars($oldData['telefono'] ?? $refugio['telefono'] ?? '') ?>" data-original="<?= htmlspecialchars($refugio['telefono'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['telefono']) ? 'input-invalido' : '' ?>" minlength="6" maxlength="20" pattern="^\+?[0-9\s\-]{6,20}$" <?= isset($errores['telefono']) ? 'data-server-error="' . htmlspecialchars($errores['telefono'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                        </li>
                        <li class="dato-refugio-item">
                            <span class="dato-refugio-label">Mail:</span>
                            <span class="dato-refugio-valor static-value"><?= htmlspecialchars($user['email'] ?? '—') ?></span>
                            <input type="email" name="email" value="<?= htmlspecialchars($oldData['email'] ?? $user['email'] ?? '') ?>" data-original="<?= htmlspecialchars($user['email'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['email']) ? 'input-invalido' : '' ?>" required <?= isset($errores['email']) ? 'data-server-error="' . htmlspecialchars($errores['email'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                        </li>
                        <li class="dato-refugio-item">
                            <span class="dato-refugio-label">Alias:</span>
                            <span class="dato-refugio-valor static-value"><?= htmlspecialchars($refugio['alias'] ?? '—') ?></span>
                            <input type="text" name="alias" value="<?= htmlspecialchars($oldData['alias'] ?? $refugio['alias'] ?? '') ?>" data-original="<?= htmlspecialchars($refugio['alias'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['alias']) ? 'input-invalido' : '' ?>" minlength="4" maxlength="40" <?= isset($errores['alias']) ? 'data-server-error="' . htmlspecialchars($errores['alias'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                        </li>
                        <li class="dato-refugio-item">
                            <span class="dato-refugio-label">CVU:</span>
                            <span class="dato-refugio-valor static-value"><?= htmlspecialchars($refugio['cvu'] ?? '—') ?></span>
                            <input type="text" name="cvu" value="<?= htmlspecialchars($oldData['cvu'] ?? $refugio['cvu'] ?? '') ?>" data-original="<?= htmlspecialchars($refugio['cvu'] ?? '') ?>" class="dato-valor-input input-value <?= isset($errores['cvu']) ? 'input-invalido' : '' ?>" minlength="22" maxlength="22" pattern="^[0-9]{22}$" <?= isset($errores['cvu']) ? 'data-server-error="' . htmlspecialchars($errores['cvu'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                        </li>
                    </ul>
                    <footer class="perfil-datos-acciones input-value">
                        <button type="submit" class="btn-guardar-perfil">Guardar</button>
                        <button type="button" id="btn-cancel-refugio" class="btn-cancelar-perfil">Cancelar</button>
                    </footer>
                </form>

            </article>
        </section>

        <!-- Navegación ancla (sticky) -->
        <nav class="perfil-refugio-nav">
            <a href="#sec-solicitudes-adopcion">
                <span class="material-symbols-outlined">assignment</span>
                Gestión de solicitudes
            </a>
            <a href="#sec-monitoreo">
                <span class="material-symbols-outlined">monitor_heart</span>
                Monitoreo Post-Adopción
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
            <a href="#sec-mascotas-adoptadas">
                <span class="material-symbols-outlined">volunteer_activism</span>
                Mascotas Adoptadas
            </a>
        </nav>
        
        <!-- Sección: Solicitudes -->
         <section class="perfil-refugio-seccion" id="sec-solicitudes-adopcion">
            <details class="perfil-dropdown">
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
        
        <!-- Sección: Monitoreo Post-Adopción -->
        <section class="perfil-refugio-seccion" id="sec-monitoreo">
            <details class="perfil-dropdown">
            <summary> <h3>Monitoreo Post-Adopción</h3> </summary>
            
            <?php if (empty($seguimientoAgrupado)): ?>
                <article class="perfil-refugio-vacio">
                    <span class="material-symbols-outlined">pets</span>
                    <p>Aún no hay seguimientos para mascotas adoptadas.</p>
                </article>
            <?php else: ?>
                <ul class="monitoreo-lista-mascotas">
                    <?php foreach ($seguimientoAgrupado as $mascotaId => $datos): ?>
                        <li class="monitoreo-item-mascota">
                            <details class="perfil-dropdown mascota-seguimiento-dropdown">
                                <summary class="mascota-seguimiento-summary">
                                    <h4>
                                        <?= htmlspecialchars($datos['mascota_nombre']) ?> 
                                        <small>(Adoptante: <?= htmlspecialchars($datos['adoptante_nombre']) ?>)</small>
                                    </h4>
                                </summary>
                                
                                <article class="mascota-seguimiento-contenido">
                                    <h5 class="monitoreo-subtitle">Encuestas</h5>
                                    <?php if (empty($datos['encuestas'])): ?>
                                        <p class="text-muted" style="margin-top: 1rem; color: #666;">No hay encuestas respondidas aún.</p>
                                    <?php else: ?>
                                        <ul class="monitoreo-lista-encuestas">
                                            <?php foreach ($datos['encuestas'] as $enc): ?>
                                                <?php 
                                                $alertaClass = $enc['alerta_generada'] ? 'encuesta-alerta' : 'encuesta-ok';
                                                ?>
                                                <li class="monitoreo-item-encuesta <?= $alertaClass ?>">
                                                    <header class="encuesta-header">
                                                        <span class="encuesta-etapa badge-<?= htmlspecialchars($enc['etapa']) ?>">Etapa: <?= htmlspecialchars(str_replace('_', ' ', $enc['etapa'])) ?></span>
                                                        <span class="encuesta-fecha"><?= htmlspecialchars(date('d/m/Y', strtotime($enc['fecha_encuesta']))) ?></span>
                                                    </header>
                                                    <details class="encuesta-detalles">
                                                        <summary>Ver respuestas completas</summary>
                                                        <article class="encuesta-respuestas-grid">
                                                            <?php if (!empty($enc['conducta'])): ?>
                                                                <p><strong>Conducta:</strong> <?= htmlspecialchars($enc['conducta']) ?></p>
                                                            <?php endif; ?>
                                                            <?php if (!empty($enc['sueno'])): ?>
                                                                <p><strong>Sueño:</strong> <?= htmlspecialchars($enc['sueno']) ?></p>
                                                            <?php endif; ?>
                                                            <?php if (!empty($enc['alimentacion'])): ?>
                                                                <p><strong>Alimentación:</strong> <?= htmlspecialchars($enc['alimentacion']) ?></p>
                                                            <?php endif; ?>
                                                            <?php if (!empty($enc['progreso_general'])): ?>
                                                                <p><strong>Progreso general:</strong> <?= htmlspecialchars($enc['progreso_general']) ?></p>
                                                            <?php endif; ?>
                                                            <?php if (!empty($enc['comentarios'])): ?>
                                                                <p class="encuesta-comentarios"><strong>Comentarios:</strong> <br><?= nl2br(htmlspecialchars($enc['comentarios'])) ?></p>
                                                            <?php endif; ?>
                                                        </article>
                                                    </details>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>

                                    <h5 class="monitoreo-subtitle mt-2">Fotos y Archivos de Seguimiento</h5>
                                    <?php if (empty($datos['fotos'])): ?>
                                        <p class="text-muted" style="margin-top: 1rem; color: #666;">No se han subido fotos ni certificados aún.</p>
                                    <?php else: ?>
                                        <ul class="monitoreo-galeria-fotos">
                                            <?php foreach ($datos['fotos'] as $foto): ?>
                                                <li class="monitoreo-foto-item">
                                                    <a href="/<?= htmlspecialchars(ltrim($foto['url'], '/')) ?>" target="_blank" class="foto-link">
                                                        <figure class="monitoreo-foto-figure">
                                                            <?php if ($foto['tipo'] === 'certificado_med'): ?>
                                                                <span class="monitoreo-doc-placeholder">
                                                                    <span class="material-symbols-outlined">description</span>
                                                                </span>
                                                            <?php else: ?>
                                                                <img src="/<?= htmlspecialchars(ltrim($foto['url'], '/')) ?>" alt="Seguimiento <?= htmlspecialchars($foto['mascota_nombre']) ?>" class="monitoreo-img">
                                                            <?php endif; ?>
                                                            <figcaption class="monitoreo-foto-info">
                                                                <span class="monitoreo-foto-tipo"><?= $foto['tipo'] === 'certificado_med' ? 'Certificado Médico' : 'Foto' ?></span>
                                                            </figcaption>
                                                        </figure>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </article>
                            </details>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            </details>
        </section>
        
        <section class="perfil-refugio-ubicacion" id="sec-ubicacion">
            <details class="perfil-dropdown">
                <summary> <h3>Ubicación: Agregar/Modificar</h3> </summary>

            <article class="perfil-refugio-ubicacion-content">
                <?php if (!empty($refugio['ciudad']) && !empty($refugio['provincia'])): ?>
                    <article class="ubicacion-actual-box">
                        <span class="material-symbols-outlined icono-pin-actual">where_to_vote</span>
                        <p class="ubicacion-actual-text">
                            <span class="label-ubicacion">Ubicación guardada:</span>
                            <strong class="valor-ubicacion"><?= htmlspecialchars($refugio['direccion'] ?? ($refugio['ciudad'] . ', ' . $refugio['provincia'])) ?></strong>
                        </p>
                    </article>
                <?php endif; ?>

                <form action="/perfil/refugio/ubicacion" method="POST" id="form-ubicacion-refugio" class="form-ubicacion-premium form-ubicacion-refugio">
                    <fieldset class="grupo-input-ubicacion">
                        <label for="ubicacion-autocomplete" class="label-buscar-ubicacion">¿Te mudaste? Buscá la nueva dirección:</label>
                        <section class="input-wrapper-premium">
                            <input type="text" id="ubicacion-autocomplete" class="input-ubicacion-premium" placeholder="Ej: Av. Rivadavia 1234, Buenos Aires..." autocomplete="off" required>
                            <ul id="sugerencias-ubicacion" class="sugerencias-ubicacion-premium"></ul>
                        </section>
                    </fieldset>
                    
                    <input type="hidden" name="latitud" id="ubi_lat">
                    <input type="hidden" name="longitud" id="ubi_lon">
                    <input type="hidden" name="ciudad" id="ubi_ciudad">
                    <input type="hidden" name="provincia" id="ubi_provincia">
                    <input type="hidden" name="pais" id="ubi_pais">
                    <input type="hidden" name="direccion" id="ubi_direccion">
                    
                    <footer class="acciones-ubicacion-premium">
                        <button type="submit" class="btn-primario btn-guardar-premium" id="btn-guardar-ubicacion" disabled>
                            Guardar Cambios
                        </button>
                    </footer>
                </form>
            </article>
            </details>
        </section>



        <section class="perfil-refugio-publicar" id="sec-publicar">
            <details class="perfil-dropdown">
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

        <section class="perfil-editar-mascota sec-editar-mascota" id="sec-editar-mascota">
            <details class="perfil-dropdown">
            <summary> <h3>Actualizar/Eliminar Mascota</h3> </summary>
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
                            <a href="/perfil/eliminar?id=<?= htmlspecialchars($mascota->fields['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-eliminar-mascota" title="Eliminar mascota">
                                <span class="material-symbols-outlined">delete</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            </details>
        </section>

        <section class="perfil-editar-mascota sec-mascotas-adoptadas" id="sec-mascotas-adoptadas">
            <details class="perfil-dropdown">
            <summary> <h3>Mascotas Adoptadas</h3> </summary>
            <?php if (empty($mascotasAdoptadas)): ?>
                <article class="perfil-vacio">
                    <span class="material-symbols-outlined">volunteer_activism</span>
                    <p>Todavía no tenés mascotas adoptadas.</p>
                </article>
            <?php else: ?>
                <ul class="perfil-refugio-grid">
                    <?php foreach ($mascotasAdoptadas as $mascota): ?>
                        <li class="tarjeta-refugio-mascota">
                            <a href="/mascota?id=<?= htmlspecialchars($mascota->fields['id']) ?>" class="tarjeta-refugio-mascota-link" title="Ver detalle de <?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota') ?>">
                                <h4><?= htmlspecialchars($mascota->fields['nombre'] ?? 'Sin nombre') ?></h4>
                                <p>
                                    <?= htmlspecialchars($mascota->fields['edad'] ?? '?') ?> año(s)
                                    · <?= htmlspecialchars($mascota->fields['tamano'] ?? '—') ?>
                                    · <?= htmlspecialchars($mascota->fields['temperamento'] ?? '—') ?>
                                </p>
                            </a>
                            <a href="/mascota/libreta?id=<?= htmlspecialchars($mascota->fields['id']) ?>" class="btn-editar btn-libreta" title="Ver Libreta Sanitaria">
                               <span class="material-symbols-outlined">medical_information</span>
                           </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            </details>
        </section>


    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
