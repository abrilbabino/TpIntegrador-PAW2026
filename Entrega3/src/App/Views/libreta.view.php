<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/libreta.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>Libreta Sanitaria - <?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="libreta-main">
        <header class="hero-libreta">
            <h1>Libreta Sanitaria de <?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?></h1>
        </header>

        <form method="GET" action="/mascota/libreta" id="form-filtros-libreta" class="filtros-seccion">
            <input type="hidden" name="id" value="<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

            <fieldset class="filtro-grupo">
                <label for="anio">Año</label>
                <select name="anio" id="anio">
                    <option value="">Todos</option>
                    <?php 
                        $anioActual = (int)date('Y');
                        $anioSel = $request->get('anio');
                        for($i = $anioActual + 1; $i >= 2010; $i--): 
                    ?>
                        <option value="<?= $i ?>" <?= $anioSel == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </fieldset>

            <fieldset class="filtro-grupo">
            <label for="mes">Mes</label>
            <select name="mes" id="mes">
                <option value="">Todos</option>
                <?php 
                    $meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 
                              5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 
                              9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
                    $mesActual = $request->get('mes');
                    foreach($meses as $numero => $nombre): 
                ?>
                    <option value="<?= $numero ?>" <?= $mesActual == $numero ? 'selected' : '' ?>>
                        <?= $nombre ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </fieldset>

            <fieldset class="filtro-grupo">
                <label for="categoria">Categoría</label>
                <select name="categoria" id="categoria">
                    <?php 
                        $catActual = $request->get('categoria') ?? ''; 
                        $categorias = [
                            '' => 'Todas',
                            'vacuna' => 'Vacunación',
                            'desparasitacion' => 'Desparasitación',
                            'cirugia' => 'Cirugías',
                            'tratamiento' => 'Tratamientos',
                            'chequeo' => 'Chequeos'
                        ];
                        foreach ($categorias as $valor => $etiqueta):
                    ?>
                        <option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>" <?= $catActual === $valor ? 'selected' : '' ?>>
                            <?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </fieldset>

            <button type="submit" class="btn-filtrar">
                <span class="material-symbols-outlined">filter_list</span> Filtrar
            </button>
        </form>

        <section class="pendientes">
            <header class="titulo">
                <span class="material-symbols-outlined">event_upcoming</span> Próximos turnos
            </header>
            
            <?php 
            $listaPendientes = $pendientes ?? $proximos ?? []; 
            if (empty($listaPendientes)): 
            ?>
                <p class="no-registros">No hay eventos próximos.</p>
            <?php else: ?>
                <?php foreach ($listaPendientes as $registro): ?>
                    <?php
                        $tipoStr = strtolower($registro->fields['tipo'] ?? '');
                        $iconName = 'medical_services';
                        if ($tipoStr === 'vacuna') $iconName = $registro->getIconoHtml();
                        elseif ($tipoStr === 'desparasitacion') $iconName = $registro->getIconoHtml();
                        elseif ($tipoStr === 'cirugia') $iconName = $registro->getIconoHtml();
                        elseif ($tipoStr === 'chequeo') $iconName = $registro->getIconoHtml();
                    ?>
                    <article class="card-registro card-pendiente">
                        <figure class="card-icon-container icon-pendiente">
                            <span class="material-symbols-outlined"><?= $iconName ?></span>
                        </figure>
                        <section class="card-content">
                            <header>
                                <h3><?= htmlspecialchars($registro->fields['titulo'] ?? 'Registro', ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="card-date">
                                    <span class="material-symbols-outlined">calendar_today</span>
                                    <?= htmlspecialchars($registro->fields['fecha_programada'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </header>
                            <?php if (!empty($registro->fields['observaciones'])): ?>
                                <article class="card-obs">
                                    <strong>Observaciones:</strong>
                                    <p><?= htmlspecialchars($registro->fields['observaciones'], ENT_QUOTES, 'UTF-8') ?></p>
                                </article>
                            <?php endif; ?>
                        </section>
                        <form method="POST" action="/mascota/registro/completar" class="form-completar">
                            <input type="hidden" name="registro_id" value="<?= htmlspecialchars((string)($registro->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="mascota_id"  value="<?= htmlspecialchars((string)($mascota->fields['id']  ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn-completar" title="Marcar como completado">
                                <span class="material-symbols-outlined">check_circle</span>
                            </button>
                        </form>
                        <footer class="badge badge-pendiente">Pendiente</footer>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="historial">
            <header class="titulo">
                <span class="material-symbols-outlined">history</span> Historial
            </header>
            
            <?php if (empty($historial)): ?>
                <p class="no-registros">No hay registros en el historial.</p>
            <?php else: ?>
                <?php foreach ($historial as $registro): ?>
                    <?php
                        $tipoStr = strtolower($registro->fields['tipo'] ?? '');
                        $iconName = 'medical_services';
                        if ($tipoStr === 'vacuna') $iconName = $registro->getIconoHtml();
                        elseif ($tipoStr === 'desparasitacion') $iconName = $registro->getIconoHtml();
                        elseif ($tipoStr === 'cirugia') $iconName = $registro->getIconoHtml();
                        elseif ($tipoStr === 'chequeo') $iconName = $registro->getIconoHtml();
                    ?>
                    <article class="card-registro card-completado">
                        <figure class="card-icon-container icon-completado">
                            <span class="material-symbols-outlined"><?= $iconName ?></span>
                        </figure>
                        <section class="card-content">
                            <header>
                                <h3><?= htmlspecialchars($registro->fields['titulo'] ?? 'Registro', ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="card-date">
                                    <span class="material-symbols-outlined">calendar_today</span>
                                    <?= htmlspecialchars($registro->fields['fecha_realizada'] ?? $registro->fields['fecha_programada'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </header>
                            <?php if (!empty($registro->fields['observaciones'])): ?>
                                <article class="card-obs">
                                    <strong>Observaciones:</strong>
                                    <p><?= htmlspecialchars($registro->fields['observaciones'], ENT_QUOTES, 'UTF-8') ?></p>
                                </article>
                            <?php endif; ?>
                        </section>
                        <span class="icono-completado material-symbols-outlined" aria-label="Completado">check_circle</span>
                        <footer class="badge badge-completado">Completado</footer>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <button type="button" class="btn-agregar-registro" onclick="document.getElementById('modal-agregar-registro').showModal();">
            <span class="material-symbols-outlined">add</span> Agregar Registro
        </button>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>

    <dialog id="modal-agregar-registro" class="modal-nativo">
        <header>
            <h2>Agregar Registro Sanitario</h2>
        </header>
        <form method="POST" action="/mascota/registro/guardar" class="form-registro">
            <input type="hidden" name="mascota_id" value="<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

            <label for="tipo">Tipo de registro</label>
            <select name="tipo" id="tipo" required>
                <option value="" disabled selected>Seleccioná un tipo</option>
                <option value="vacuna">Vacunación</option>
                <option value="desparasitacion">Desparasitación</option>
                <option value="cirugia">Cirugía</option>
                <option value="tratamiento">Tratamiento</option>
                <option value="chequeo">Chequeo</option>
            </select>

            <label for="titulo">Título / Descripción</label>
            <input type="text" id="titulo" name="titulo" placeholder="Ej: Antirrábica" required>

            <label for="fecha_programada">Fecha programada</label>
            <input type="date" id="fecha_programada" name="fecha_programada" required>

            <label for="observaciones">Observaciones (opcional)</label>
            <textarea id="observaciones" name="observaciones" rows="3" placeholder="Notas adicionales..."></textarea>

            <footer class="acciones-modal">
                <button type="button" class="btn-cancelar" onclick="document.getElementById('modal-agregar-registro').close();">Cancelar</button>
                <button type="submit" class="btn-guardar-registro">Guardar Registro</button>
            </footer>
        </form>
    </dialog>
</body>
</html>
