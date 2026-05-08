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
    <title>Adoptar - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php' ?>

    <main class="adoptar-main">
        <header class="hero-adoptar">
            <h1>Adoptá una Mascota</h1>
            <p>Filtrá por especie, tamaño, edad y más para encontrar tu compañero ideal.</p>
        </header>

        <section class="seccion-filtros">
            <details class="filtros" open>
                <summary>
                    <span class="material-symbols-outlined">filter_list</span>
                    <span>Filtros</span>
                    <span class="material-symbols-outlined filtros-chevron">expand_more</span>
                </summary>

                <form id="form-filtros" method="GET" action="/adoptar">
                    <p>Ubicación</p>
                    <input type="text" name="ubicacion" placeholder="Ciudad o zona..."
                           value="<?= htmlspecialchars($request->get('ubicacion') ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <p>Rango de Edad</p>
                    <div class="edad-rango">
                        <input type="number" name="edad_min" placeholder="Mín" min="0"
                               value="<?= htmlspecialchars($request->get('edad_min') ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <span>a</span>
                        <input type="number" name="edad_max" placeholder="Máx" min="0"
                               value="<?= htmlspecialchars($request->get('edad_max') ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <p>Tamaño</p>
                    <select name="tamano">
                        <option value="">Todos</option>
                        <?php foreach ($tamanos as $t): ?>
                            <option value="<?= htmlspecialchars($t->fields['tamano'], ENT_QUOTES, 'UTF-8') ?>" <?= ($request->get('tamano') == $t->fields['tamano']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($t->fields['tamano']), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <p>Especie</p>
                    <div class="filtro-especie-grupo">
                        <?php foreach ($especies as $e): ?>
                            <label class="especie-radio">
                                <input type="radio" name="especie" value="<?= htmlspecialchars($e->fields['especie'], ENT_QUOTES, 'UTF-8') ?>"
                                       <?= ($request->get('especie') == $e->fields['especie']) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars(ucfirst($e->fields['especie']), ENT_QUOTES, 'UTF-8') ?></span>
                            </label>
                        <?php endforeach; ?>
                        <label class="especie-radio">
                            <input type="radio" name="especie" value="" <?= ($request->get('especie') === null || $request->get('especie') === '') ? 'checked' : '' ?>>
                            <span>Todos</span>
                        </label>
                    </div>

                    <p>Temperamento</p>
                    <select name="temperamento">
                        <option value="">Todos</option>
                        <?php foreach ($temperamentos as $t): ?>
                            <option value="<?= htmlspecialchars($t->fields['temperamento'], ENT_QUOTES, 'UTF-8') ?>" <?= ($request->get('temperamento') == $t->fields['temperamento']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($t->fields['temperamento']), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">Aplicar Filtros</button>
                </form>
            </details>
        </section>

        <section class="adoptar-contenido">
                <article class="grilla-mascotas">
                    <?php foreach ($mascotas as $mascota): ?>
                        <a href="/mascota?id=<?= htmlspecialchars(($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="verPerfil">
                        <article class="tarjeta-mascota">
                            <figure class="tarjeta-imagen">
                                <img src="/assets/img/<?= htmlspecialchars($mascota->fields['imagen'] ?? 'default.jpg', ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?>">
                                <button class="btn-favorito" aria-label="Agregar a favoritos">
                                    <span class="material-symbols-outlined">favorite</span>
                                </button>
                            </figure>

                            <div class="tarjeta-info">
                                <h3><?= htmlspecialchars($mascota->fields['nombre'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8') ?></h3>
                                <p>
                                    <?= htmlspecialchars((string)($mascota->fields['edad'] ?? '0'), ENT_QUOTES, 'UTF-8') ?> años -
                                    <?= htmlspecialchars(ucfirst($mascota->fields['tamano'] ?? 'Desconocido'), ENT_QUOTES, 'UTF-8') ?> -
                                    <?= htmlspecialchars(ucfirst($mascota->fields['temperamento'] ?? 'Desconocido'), ENT_QUOTES, 'UTF-8') ?>
                                </p>
                            </div>
                        </article>
                        </a>
                    <?php endforeach; ?>

                    <?php if (empty($mascotas)): ?>
                        <p class="no-resultados">No se encontraron mascotas con los filtros seleccionados.</p>
                    <?php endif; ?>
                </article>

                <?php require __DIR__ . '/paginacion.view.php'; ?>
            </section>

    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
