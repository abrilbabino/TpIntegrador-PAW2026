<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'PawMap: Encuentra a tu compañero ideal. Adopta perros y gatos en adopción de los mejores refugios.') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <title>Refugios Aliados - PawMap</title>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="refugios-main">
        <header class="hero-refugios">
            <h1>Nuestros Refugios Aliados</h1>
            <p>Conocé a las organizaciones que cuidan y rescatan a tus futuras mascotas</p>
        </header>

        <section class="seccion-refugio">
            <div data-paw-filtros="refugios" style="display: contents;"></div>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
