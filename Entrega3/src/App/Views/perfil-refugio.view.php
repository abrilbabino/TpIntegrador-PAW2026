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

    <main class="perfil-refugio-container">
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
                <a href="/perfil/editar" class="btn-editar" title="Editar datos">
                    <span class="material-symbols-outlined">edit_square</span>
                </a>
            </header>
            <ul class="perfil-refugio-datos-grid">
                <li class="dato-refugio-item">
                    <span class="dato-refugio-label">Nombre de la institución:</span>
                    <span class="dato-refugio-valor"><?= htmlspecialchars($refugio['nombre_institucion'] ?? '—') ?></span>
                </li>
                <li class="dato-refugio-item">
                    <span class="dato-refugio-label">Descripción:</span>
                    <span class="dato-refugio-valor"><?= htmlspecialchars($refugio['descripcion'] ?? '-') ?></span>
                </li>
                <li class="dato-refugio-item">
                    <span class="dato-refugio-label">Teléfono:</span>
                    <span class="dato-refugio-valor"><?= htmlspecialchars($refugio['telefono'] ?? '—') ?></span>
                </li>
                <li class="dato-refugio-item">
                    <span class="dato-refugio-label">Mail:</span>
                    <span class="dato-refugio-valor"><?= htmlspecialchars($user['email'] ?? '—') ?></span>
                </li>
                <li class="dato-refugio-item">
                    <span class="dato-refugio-label">Alias:</span>
                    <span class="dato-refugio-valor"><?= htmlspecialchars($refugio['alias'] ?? '—') ?></span>
                </li>
                <li class="dato-refugio-item">
                    <span class="dato-refugio-label">CVU:</span>
                    <span class="dato-refugio-valor"><?= htmlspecialchars($refugio['cvu'] ?? '—') ?></span>
                </li>
            </ul>
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
                        <li class="perfil-lista-item-adopcion">
                            <h4>
                                <?= htmlspecialchars($sol['mascota_nombre'] ?? 'Mascota') ?>
                                por <?= htmlspecialchars($sol['adoptante_nombre'] ?? '—') ?>
                                <?= htmlspecialchars($sol['adoptante_apellido'] ?? '—') ?>
                            </h4>
                            <span class="estado-solicitud estado-pendiente">
                                Estado: <?= htmlspecialchars($sol['estado'] ?? '—') ?>  <br>
                                Fecha: <?= htmlspecialchars($sol['fecha'] ?? '—') ?>
                            </span>
                            <p>
                                Detalle de la mascota: <br>
                                <?= htmlspecialchars($sol['edad'] ?? '?') ?> año(s)
                                · <?= htmlspecialchars($sol['tamano'] ?? '—') ?>
                                · <?= htmlspecialchars($sol['temperamento'] ?? '—') ?>
                            </p>
                            <button class="btn-rechazar">RECHAZAR</button>
                            <button class="btn-aceptar">ACEPTAR</button>
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
            <summary> <h3>Publicar Mascota</h3> </summary>
            <p>Agrega los datos de la nueva mascota:</p>
        
            <form>
                <label for="nombre">Nombre
                <input type="text" id="nombre" name="nombre" placeholder="Ingresá el nombre de la mascota">
                </label>
                
                <label for="especie">Especie
                    <?php foreach ($especies as $e): ?>
                            <label class="especie-refugio-radio">
                                <input type="radio" name="especie" value="<?= htmlspecialchars($e->fields['especie'], ENT_QUOTES, 'UTF-8') ?>"
                                       <?= ($request->get('especie') == $e->fields['especie']) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars(ucfirst($e->fields['especie']), ENT_QUOTES, 'UTF-8') ?></span>
                            </label>
                        <?php endforeach; ?>
                </label>
                
                <label for="fecha_nacimiento">Fecha de nacimiento
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
                </label>

                <label for="descripcion">Descripción
                <textarea id="descripcion" name="descripcion" placeholder="Ingresá una descripción de la mascota"></textarea>
                </label>

                <label for="tamanio">Tamaño
                <select id="tamanio" name="tamanio">
                    <?php foreach ($tamanos as $t): ?>
                            <option value="<?= htmlspecialchars($t->fields['tamano'], ENT_QUOTES, 'UTF-8') ?>" <?= ($request->get('tamano') == $t->fields['tamano']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($t->fields['tamano']), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                </select>
                </label>
                <label for="sexo">Sexo
                <select id="sexo" name="sexo">
                    <option value="macho">Macho</option>
                    <option value="hembra">Hembra</option>
                </select>
                </label>

                <label for="temperamento">temperamento
                <select id="temperamento" name="temperamento">
                    <?php foreach ($temperamentos as $t): ?>
                            <option value="<?= htmlspecialchars($t->fields['temperamento'], ENT_QUOTES, 'UTF-8') ?>" <?= ($request->get('temperamento') == $t->fields['temperamento']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($t->fields['temperamento']), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                </select>
                </label>

                <label for="esterilizado">¿Está esterilizado?
                <select id="esterilizado" name="esterilizado">
                    <option value="si">Sí</option>
                    <option value="no">No</option>
                </select>
                </label>
                <label for="foto">Foto
                <input type="file" id="foto" name="foto" accept="image/*">
                </label>
                <button type="submit">Publicar</button>
            </form>
        </section>

        <section class="perfil-editar-mascota" id="sec-editar-mascota">
            <h2>
                <span class="material-symbols-outlined">pets</span>
                Actualizar/Eliminar Mascota
            </h2>
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
                            <a href="/perfil/editar" class="btn-editar" title="Editar datos">
                                <span class="material-symbols-outlined">edit_square</span>
                            </a>
                            <a href="/perfil/eliminar" class="btn-eliminar-mascota" title="Eliminar mascota">
                                <span class="material-symbols-outlined">delete</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
