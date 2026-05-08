<!DOCTYPE html>
<html lang="es">
<head>
    <meta .charset="utf-8">
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <link rel="stylesheet" href="/assets/css/style.css" />
    <title>Page Not Found</title>
</head>
<body>  
    <?php require __DIR__ . '/barra-navegacion.view.php'?>
    <h1 class="error"><?= $mensaje_error ?? 'Página no encontrada' ?></h1>
    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
