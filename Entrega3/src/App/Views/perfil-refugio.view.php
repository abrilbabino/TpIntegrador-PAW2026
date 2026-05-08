<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/perfil.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>Mi Perfil - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="perfil-container">
        <section class="perfil-header">
            <figure class="perfil-avatar">
                <span class="material-symbols-outlined">location_city</span>
            </figure>
            <h1>Hola, <?= htmlspecialchars($user['nombre_usuario']) ?>!</h1>
            <p class="perfil-email"><?= htmlspecialchars($user['email'] ?? '') ?></p>
            <a href="/logout" class="perfil-logout">
                <span class="material-symbols-outlined">logout</span>
                Cerrar sesión
            </a>
        </section>

        <section class="perfil-mascotas">
            <h2>
                <span class="material-symbols-outlined">pets</span>
                Mis Mascotas Publicadas
            </h2>
            
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
