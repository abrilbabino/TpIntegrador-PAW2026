<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <title>Resultados del Test - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="pagina-resultado">
        <section class="tarjeta-resultado">
            <article class="resultado-veredicto">
                <p class="resultado-match">¡Tenemos match!</p>

                <span class="resultado-emoji">
                    <?= $resultadoTest['tipo'] === 'perro' ? '🐶' : ($resultadoTest['tipo'] === 'gato' ? '🐱' : '🐾') ?>
                </span>

                <h2 class="resultado-titulo">
                    <?php if ($resultadoTest['tipo'] === 'perro'): ?>
                        Tu compa&ntilde;ero ideal es un PERRO
                    <?php elseif ($resultadoTest['tipo'] === 'gato'): ?>
                        Tu compa&ntilde;ero ideal es un GATO
                    <?php else: ?>
                        Pod&eacute;s adoptar perros o gatos
                    <?php endif; ?>
                </h2>

                <p class="resultado-justificacion">
                    <?= htmlspecialchars($resultadoTest['mensaje'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            </article>

            <nav class="resultado-actions">
                <a href="/adoptar" class="btn-primary">
                    <span class="material-symbols-outlined">search</span>
                    Ver todas las sugerencias
                </a>
            </nav>

            <?php if (!empty($mascotas)): ?>
            <hr class="resultado-divider">

            <section class="resultado-mascotas">
                <h3 class="resultado-subtitulo">
                    <span class="material-symbols-outlined">pets</span>
                    Mascotas recomendadas para vos
                </h3>

                <section class="grilla-recomendadas">
                    <?php foreach ($mascotas as $mascota): ?>
                    <a href="/mascota?id=<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="tarjeta-recomendada">
                        <figure>
                            <img src="/assets/img/<?= htmlspecialchars($mascota->fields['imagen'] ?? 'default.jpg', ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?>">
                        </figure>
                        <article class="tarjeta-recomendada-body">
                            <h4><?= htmlspecialchars($mascota->fields['nombre'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8') ?></h4>
                            <p>
                                <?= htmlspecialchars((string)($mascota->fields['edad'] ?? '0'), ENT_QUOTES, 'UTF-8') ?> a&ntilde;os
                                &middot; <?= htmlspecialchars(ucfirst($mascota->fields['tamano'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                &middot; <?= htmlspecialchars(ucfirst($mascota->fields['temperamento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </article>
                    </a>
                    <?php endforeach; ?>
                </section>
            </section>
            <?php endif; ?>

            <footer class="resultado-volver">
                <a href="/test-de-compatibilidad" class="btn-volver">
                    <span class="material-symbols-outlined">replay</span>
                    Volver a hacer el test
                </a>
            </footer>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
