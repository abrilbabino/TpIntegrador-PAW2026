<!DOCTYPE html>
<html lang="es">
<head>
    <meta .charset="utf-8">
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <title>Error Interno del Servidor</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'?>
    <h1 class="error">Error Interno del Servidor</h1>
    <?php require __DIR__ . '/footer.view.php'; ?>
    <?php require __DIR__ . '/iniciar-sesion.view.php'; ?>
    <?php require __DIR__ . '/carrito.view.php'; ?>
</body>
</html>