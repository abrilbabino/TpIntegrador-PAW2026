<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/libreta.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>Libreta Sanitaria - <?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <!--REVISAR TOOOOOOODO ESTO -->
    <main class="libreta-main">
        <header class="libreta-header">
            <h1>Libreta Sanitaria de <?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?></h1>
        </header>

        <section class="filtros-libreta">
            <form method="GET" action="/mascota/libreta" id="form-filtros-libreta">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                
                <div class="tabs-categorias">
                    <?php 
                        $catActual = $request->get('categoria') ?? ''; 
                        $categorias = [
                            '' => 'Todos',
                            'vacuna' => 'Vacunación',
                            'desparasitacion' => 'Desparasitación',
                            'cirugia' => 'Cirugías',
                            'tratamiento' => 'Tratamientos',
                            'chequeo' => 'Chequeos'
                        ];
                    ?>
                    <?php foreach ($categorias as $valor => $etiqueta): ?>
                        <button type="submit" name="categoria" value="<?= $valor ?>" class="tab-btn <?= $catActual === $valor ? 'active' : '' ?>">
                            <?= $etiqueta ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="filtro-anio">
                    <label for="anio">Año:</label>
                    <select name="anio" id="anio" onchange="document.getElementById('form-filtros-libreta').submit();">
                        <option value="">Todos</option>
                        <?php 
                            $anioActual = (int)date('Y');
                            $anioSel = $request->get('anio');
                            for($i = $anioActual + 1; $i >= 2010; $i--): 
                        ?>
                            <option value="<?= $i ?>" <?= $anioSel == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>
        </section>

        <?php if (!empty($proximos)): ?>
        <section class="banner-proximos">
            <h2><i class="fa-solid fa-calendar-check"></i> Próximos Turnos</h2>
            <div class="proximos-grid">
                <?php foreach ($proximos as $registro): ?>
                    <article class="card-registro pendiente">
                        <div class="icono-registro">
                            <?= $registro->getIconoHtml() ?>
                        </div>
                        <div class="info-registro">
                            <h3><?= htmlspecialchars($registro->fields['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="fecha"><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars($registro->fields['fecha_programada'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if ($registro->fields['observaciones']): ?>
                                <p class="observaciones"><?= htmlspecialchars($registro->fields['observaciones'], ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                            <span class="badge-estado pendiente">Pendiente</span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <section class="historial-registros">
            <h2><i class="fa-solid fa-clock-rotate-left"></i> Historial Médico</h2>
            
            <?php if (empty($historial)): ?>
                <p class="no-registros">No hay registros en el historial para los filtros seleccionados.</p>
            <?php else: ?>
                <div class="historial-lista">
                    <?php foreach ($historial as $registro): ?>
                        <article class="card-registro completado">
                            <div class="icono-registro">
                                <?= $registro->getIconoHtml() ?>
                            </div>
                            <div class="info-registro">
                                <h3><?= htmlspecialchars($registro->fields['titulo'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p class="fecha">
                                    <i class="fa-regular fa-calendar-check"></i> 
                                    <?= htmlspecialchars($registro->fields['fecha_realizada'] ?? $registro->fields['fecha_programada'], ENT_QUOTES, 'UTF-8') ?>
                                </p>
                                <?php if ($registro->fields['observaciones']): ?>
                                    <p class="observaciones"><?= htmlspecialchars($registro->fields['observaciones'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>
                                <span class="badge-estado completado">Completado</span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
