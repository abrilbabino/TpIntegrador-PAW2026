<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <title>Refugios Aliados - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="refugios-main">
        <header class="hero-refugios">
            <h1>Nuestros Refugios Aliados</h1>
            <p>Conocé a las organizaciones que cuidan y rescatan a tus futuras mascotas</p>
        </header>

        <section class="seccion-refugio">
            <aside class="seccion-filtros-refugios">
                <details class="filtros" open>
                    <summary>
                        <span class="material-symbols-outlined">filter_list</span>
                        <span>Filtros</span>
                        <span class="material-symbols-outlined filtros-simbolo">expand_more</span>
                    </summary>

                    <form id="form-filtros-refugios" method="GET" action="/refugios">
                        <fieldset>
                            <legend>Provincia</legend>
                            <select name="provincia">
                                <option value="">Todas</option>
                                <?php foreach ($provincias as $prov): ?>
                                    <option value="<?= htmlspecialchars($prov->fields['provincia'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        <?= ($request->get('provincia') == ($prov->fields['provincia'] ?? '')) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucfirst($prov->fields['provincia'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </fieldset>

                        <fieldset>
                            <legend>Ciudad</legend>
                            <select name="ciudad">
                                <option value="">Todas</option>
                                <?php foreach ($ciudades as $ciudad): ?>
                                    <option value="<?= htmlspecialchars($ciudad->fields['ciudad'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        <?= ($request->get('ciudad') == ($ciudad->fields['ciudad'] ?? '')) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucfirst($ciudad->fields['ciudad'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </fieldset>

                        <button type="submit">Aplicar Filtros</button>
                    </form>
                </details>

                <article class="cta-refugio desktop">
                    <h3>
                        <span class="material-symbols-outlined">pets</span>
                        ¿Representás a un Refugio?
                    </h3>
                    <p>Sumate a nuestra red y dale visibilidad a tus mascotas.</p>
                    <a href="/registro-refugio" class="btn-registro-refugio">
                        <span class="material-symbols-outlined">add_circle</span>
                        Registrate
                    </a>
                </article>
            </aside>

            <section class="refugios-contenido">
                <article class="grilla-refugios">
                    <?php foreach ($refugios as $refugio): ?>
                        <article class="tarjeta-refugio">
                            <figure class="tarjeta-refugio-imagen">
                                <img src="/assets/img/<?= htmlspecialchars($refugio->fields['imagen'] ?? 'default-refugio.jpg', ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($refugio->fields['nombre_institucion'] ?? 'Refugio', ENT_QUOTES, 'UTF-8') ?>">
                            </figure>

                            <article class="tarjeta-refugio-info">
                                <header class="tarjeta-refugio-header">
                                    <h3><?= htmlspecialchars($refugio->fields['nombre_institucion'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8') ?></h3>
                                    <a href="tel:<?= htmlspecialchars($refugio->fields['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="icono-telefono" aria-label="Llamar al refugio">
                                        <span class="material-symbols-outlined">call</span>
                                    </a>
                                </header>
                                <p class="refugio-ubicacion"><?= htmlspecialchars(($refugio->fields['ciudad'] ?? 'Desconocido') . ', ' . ($refugio->fields['provincia'] ?? 'Desconocido'), ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="refugio-adoptables">
                                    <strong>Adoptables disponibles: <?= htmlspecialchars((string)($refugio->fields['adoptables_disponibles'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></strong>
                                </p>
                            </article>
                        </article>
                    <?php endforeach; ?>

                    <?php if (empty($refugios)): ?>
                        <p class="no-resultados">No se encontraron refugios con los filtros seleccionados.</p>
                    <?php endif; ?>
                </article>

                <?php require __DIR__ . '/paginacion.view.php'; ?>

                <article class="cta-refugio mobile">
                    <h3>
                        <span class="material-symbols-outlined">pets</span>
                        ¿Representás a un Refugio?
                    </h3>
                    <p>Sumate a nuestra red y dale visibilidad a tus mascotas.</p>
                    <a href="/registro-refugio" class="btn-registro-refugio">
                        <span class="material-symbols-outlined">add_circle</span>
                        Registrate
                    </a>
                </article>
            </section>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
