<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>Mi Perfil - PawMap</title>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="perfil-refugio-container">
        <section class="perfil-refugio-header">
            <figure class="perfil-refugio-avatar">
                <?php if (!empty($user['foto'])): ?>
                    <img src="/assets/img/<?= htmlspecialchars($user['foto']) ?>" alt="">
                <?php else: ?>
                    <span class="material-symbols-outlined">person</span>
                <?php endif; ?>            
            </figure>

            <h1>Hola, <?= htmlspecialchars($user['nombre_usuario']) ?>!</h1>
            <p class="perfil-refugio-email"><?= htmlspecialchars($user['email'] ?? '') ?></p>
            <a href="/logout" class="perfil-refugio-logout">
                <span class="material-symbols-outlined">logout</span>
                Cerrar sesión
            </a>
        </section>

        <section class="perfil-refugio-datos">
            <header class="perfil-refugio-datos-header">
                <h2>Datos del Refugio</h2>
                <a href="/perfil/editar" class="btn-editar" title="Editar datos">
                    <span class="material-symbols-outlined">edit_square</span>
                </a>
            </header>
            <ul class="perfil-refugio-datos-grid">
                <li class="dato-item">
                    <span class="dato-label">Nombre de la institución:</span>
                    <span class="dato-valor"><?= htmlspecialchars($refugio['nombre_institucion'] ?? '—') ?></span>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Descripción:</span>
                    <span class="dato-valor"><?= htmlspecialchars($refugio['descripcion'] ?? '-') ?></span>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Teléfono:</span>
                    <span class="dato-valor"><?= htmlspecialchars($refugio['telefono'] ?? '—') ?></span>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Mail:</span>
                    <span class="dato-valor"><?= htmlspecialchars($user['email'] ?? '—') ?></span>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Alias:</span>
                    <span class="dato-valor"><?= htmlspecialchars($refugio['alias'] ?? '—') ?></span>
                </li>
                <li class="dato-item">
                    <span class="dato-label">CVU:</span>
                    <span class="dato-valor"><?= htmlspecialchars($refugio['cvu'] ?? '—') ?></span>
                </li>
            </ul>
        </section>


        <section class="perfil-refugio-mascotas">
            <h2>
                <span class="material-symbols-outlined">pets</span>
                Mis Mascotas Publicadas
            </h2>
            <?php if (empty($mascotas)): ?>
                <article class="perfil-vacio">
                    <span class="material-symbols-outlined">pets</span>
                    <p>Todavía no tenés mascotas publicadas.</p>
                    <a href="/publicar" class="btn-explorar">Publicar mascota</a>
                </article>
            <?php else: ?>
                <ul class="perfil-cards-grid">
                    <?php foreach ($mascotas as $mascota): ?>
                        <li class="perfil-card">
                            <a href="/mascota?id=<?= htmlspecialchars($mascota->fields['id']) ?>" class="perfil-card-link" title="Ver detalle de <?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota') ?>">
                                <figure class="perfil-card-img">
                                    <img
                                        src="/assets/img/<?= htmlspecialchars($mascota->fields['imagen'] ?? 'default-pet.jpg') ?>"
                                        alt="<?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota') ?>"
                                    />
                                </figure>
                                <h4><?= htmlspecialchars($mascota->fields['nombre'] ?? 'Sin nombre') ?></h4>
                                <p>
                                    <?= htmlspecialchars($mascota->fields['edad'] ?? '?') ?> año(s)
                                    · <?= htmlspecialchars($mascota->fields['tamano'] ?? '—') ?>
                                    · <?= htmlspecialchars($mascota->fields['temperamento'] ?? '—') ?>
                                </p>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
