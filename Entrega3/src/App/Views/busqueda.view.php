<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'PawMap: Encuentra a tu compañero ideal. Adopta perros y gatos en adopción de los mejores refugios.') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/adoptar.css" />
    <link rel="stylesheet" href="/assets/css/refugios.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="/assets/css/busqueda.css" />
    <title><?= htmlspecialchars($titulo ?? 'Resultados de búsqueda') ?></title>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/components/paw-paginacion.js"></script>
    <script src="/assets/js/components/PAWVisualizacion.js"></script>
    <script src="/assets/js/app.js"></script>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="contenedor-busqueda" id="busqueda-resultados" data-resultados="<?= htmlspecialchars(json_encode($resultados_mixtos ?? []), ENT_QUOTES, 'UTF-8') ?>">
        <h1 id="titulo-busqueda" class="titulo-busqueda">Resultados para: "<?= htmlspecialchars($q ?? '') ?>"</h1>
        
        <section id="grilla-resultados" class="grilla-items"></section>
        
        <nav id="paginacion-js" class="paginacion"></nav>
    </main>



    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
