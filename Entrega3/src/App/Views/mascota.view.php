<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/mascota.css" />
    <link rel="stylesheet" href="/assets/css/print.css" media="print" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <title><?= htmlspecialchars($mascota->fields['nombre'], ENT_QUOTES, 'UTF-8') ?> - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php' ?>

    <main>
        <header>
            <h1><?= htmlspecialchars($mascota->fields['nombre'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p>Conocé más sobre esta mascota</p>
        </header>

        <section class="detalle-mascota">
            <figure class="detalle-imagen">
                <img src="/assets/img/<?= htmlspecialchars($mascota->fields['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($mascota->fields['nombre'], ENT_QUOTES, 'UTF-8') ?>">
            </figure>

            <div class="detalle-info">
                <h2><?= htmlspecialchars($mascota->fields['nombre'], ENT_QUOTES, 'UTF-8') ?></h2>

                <p><strong>Especie:</strong> <?= htmlspecialchars(ucfirst($mascota->fields['especie']), ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Edad:</strong> <?= htmlspecialchars((string)$mascota->fields['edad'], ENT_QUOTES, 'UTF-8') ?> años</p>
                <p><strong>Tamaño:</strong> <?= htmlspecialchars(ucfirst($mascota->fields['tamano']), ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Temperamento:</strong> <?= htmlspecialchars(ucfirst($mascota->fields['temperamento']), ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Vacunado:</strong> <?= $mascota->fields['vacunado'] ? 'Sí' : 'No' ?></p>
                <p><strong>Castrado:</strong> <?= $mascota->fields['castrado'] ? 'Sí' : 'No' ?></p>

                <p class="descripcion"><?= htmlspecialchars($mascota->fields['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>

                <a href="/formulario-adopcion" class="boton-principal">Quiero Adoptar</a>
            </div>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
