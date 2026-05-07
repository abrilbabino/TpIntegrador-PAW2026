<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/perfil.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <title>Mi Perfil - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="perfil-container">
        <!-- Cabecera del perfil -->
        <section class="perfil-header">
            <figure class="perfil-avatar">
                <span class="material-symbols-outlined">person</span>
            </figure>
            <h1>Hola, <?= htmlspecialchars($user['nombre_usuario']) ?>!</h1>
            <p class="perfil-email"><?= htmlspecialchars($user['email'] ?? '') ?></p>
            <a href="/logout" class="perfil-logout">
                <span class="material-symbols-outlined">logout</span>
                Cerrar sesión
            </a>
        </section>

        <!-- Sección de favoritos -->
        <section class="perfil-favoritos">
            <h2>
                <span class="material-symbols-outlined">favorite</span>
                Mis Mascotas Favoritas
            </h2>

            <?php if (empty($favoritos)): ?>
                <div class="favoritos-vacio">
                    <span class="material-symbols-outlined">pets</span>
                    <p>Todavía no tenés mascotas favoritas.</p>
                    <a href="/adoptar" class="btn-explorar">Explorar mascotas</a>
                </div>
            <?php else: ?>
                <div class="favoritos-grid">
                    <?php foreach ($favoritos as $fav): ?>
                        <article class="favorito-card">
                            <figure class="favorito-img">
                                <img 
                                    src="/assets/img/<?= htmlspecialchars($fav['imagen'] ?? 'default-pet.jpg') ?>" 
                                    alt="<?= htmlspecialchars($fav['nombre'] ?? 'Mascota') ?>" 
                                />
                            </figure>
                            <div class="favorito-info">
                                <h3><?= htmlspecialchars($fav['nombre'] ?? 'Sin nombre') ?></h3>
                                <p class="favorito-especie">
                                    <span class="material-symbols-outlined">pets</span>
                                    <?= htmlspecialchars($fav['especie'] ?? '') ?>
                                </p>
                                <?php if (!empty($fav['edad'])): ?>
                                    <p class="favorito-edad">
                                        <span class="material-symbols-outlined">cake</span>
                                        <?= htmlspecialchars($fav['edad']) ?> año(s)
                                    </p>
                                <?php endif; ?>
                                <p class="favorito-estado estado-<?= strtolower($fav['estado_adopcion'] ?? 'disponible') ?>">
                                    <?= htmlspecialchars($fav['estado_adopcion'] ?? 'DISPONIBLE') ?>
                                </p>
                            </div>
                            <div class="favorito-acciones">
                                <a href="/mascota?id=<?= $fav['id'] ?>" class="btn-ver">
                                    <span class="material-symbols-outlined">visibility</span>
                                    Ver detalle
                                </a>
                                <form method="POST" action="/favorito/eliminar" class="form-eliminar">
                                    <input type="hidden" name="favorito_id" value="<?= $fav['favorito_id'] ?>" />
                                    <button type="submit" class="btn-eliminar">
                                        <span class="material-symbols-outlined">delete</span>
                                        Quitar
                                    </button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
