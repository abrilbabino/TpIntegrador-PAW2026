<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/test-compatibilidad.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <title>Test de Compatibilidad - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main>
        <header class="hero-adoptar">
            <h1>TEST DE COMPATIBILIDAD</h1>
            <p>Respondé estas preguntas para encontrar a tu compañero ideal.</p>
        </header>

        <section class="seccion-test">
            <header class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </header>

            <p class="indicador-paso" id="indicador-paso">Pregunta 1 de <?= count($preguntas) ?></p>

            <form id="testForm" method="POST" action="/test-de-compatibilidad/resultado">

                <?php foreach ($preguntas as $i => $pregunta): ?>
                <fieldset class="paso <?= $i === 0 ? 'active' : '' ?>" data-paso="<?= $i + 1 ?>">
                    <h2><?= htmlspecialchars($pregunta['titulo']) ?></h2>
                    <ul class="options-container">
                        <?php foreach ($pregunta['opciones'] as $opcion): ?>
                        <li>
                            <label class="tarjeta-opcion">
                                <input type="radio" name="<?= htmlspecialchars($pregunta['name']) ?>" value="<?= htmlspecialchars($opcion['valor']) ?>">
                                <span class="contenido-opcion">
                                    <strong class="opcion-titulo"><?= htmlspecialchars($opcion['etiqueta']) ?></strong>
                                    <?php if (!empty($opcion['subtitulo'])): ?>
                                    <small class="opcion-subtitulo"><?= htmlspecialchars($opcion['subtitulo']) ?></small>
                                    <?php endif; ?>
                                </span>
                            </label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
                <?php endforeach; ?>

                <menu class="boton-fila">
                    <button type="button" class="btn-anterior" id="btnAnterior">Anterior</button>
                    <button type="button" class="btn-siguiente" id="btnSiguiente">Siguiente</button>
                    <button type="submit" class="btn-resultados" id="btnResultados">Ver Resultados</button>
                </menu>

            </form>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>

    <script src="/assets/js/test-compatibilidad.js"></script>
</body>
</html>
