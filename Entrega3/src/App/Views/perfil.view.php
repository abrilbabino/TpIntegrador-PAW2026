<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/perfil.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title><?= htmlspecialchars($titulo ?? 'Mi Perfil - PawMap') ?></title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="perfil-container">

        <!-- Cabecera -->
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

        <!-- Datos personales -->
        <section class="perfil-datos">
            <header class="perfil-datos-header">
                <h2>Datos Personales</h2>
                <a href="/perfil/editar" class="btn-editar" title="Editar datos">
                    <span class="material-symbols-outlined">edit_square</span>
                </a>
            </header>
            <ul class="perfil-datos-grid">
                <li class="dato-item">
                    <span class="dato-label">Nombre:</span>
                    <span class="dato-valor"><?= htmlspecialchars($adoptante['nombre'] ?? '—') ?></span>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Fecha de Nacimiento:</span>
                    <span class="dato-valor"><?= htmlspecialchars($adoptante['fecha_de_nacimiento'] ?? 'dd / mm / aaaa') ?></span>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Apellido:</span>
                    <span class="dato-valor"><?= htmlspecialchars($adoptante['apellido'] ?? '—') ?></span>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Mail:</span>
                    <span class="dato-valor"><?= htmlspecialchars($user['email'] ?? '—') ?></span>
                </li>
                <li class="dato-item">
                    <span class="dato-label">DNI:</span>
                    <span class="dato-valor"><?= htmlspecialchars($adoptante['dni'] ?? '—') ?></span>
                </li>
                <li class="dato-item">
                    <span class="dato-label">Teléfono:</span>
                    <span class="dato-valor"><?= htmlspecialchars($user['contacto'] ?? '—') ?></span>
                </li>
            </ul>
        </section>

        <!-- Navegación ancla (sticky) -->
        <nav class="perfil-nav">
            <a href="#sec-favoritos">
                <span class="material-symbols-outlined">favorite</span>
                Favoritos
            </a>
            <a href="#sec-solicitudes">
                <span class="material-symbols-outlined">mail</span>
                Solicitudes
            </a>
            <a href="#sec-adopciones">
                <span class="material-symbols-outlined">pets</span>
                Adopciones
            </a>
        </nav>

        <!-- Sección: Favoritos -->
        <section class="perfil-seccion" id="sec-favoritos">
            <h3>Favoritos</h3>
            <?php if (empty($favoritos)): ?>
                <article class="perfil-vacio">
                    <span class="material-symbols-outlined">pets</span>
                    <p>Todavía no tenés mascotas favoritas.</p>
                    <a href="/adoptar" class="btn-explorar">Explorar mascotas</a>
                </article>
            <?php else: ?>
                <ul class="perfil-cards-grid">
                    <?php foreach ($favoritos as $fav): ?>
                        <li class="perfil-card">
                            <a href="/mascota?id=<?= htmlspecialchars($fav['id']) ?>" class="perfil-card-link" title="Ver detalle de <?= htmlspecialchars($fav['nombre'] ?? 'Mascota') ?>">
                                <figure class="perfil-card-img">
                                    <img
                                        src="/assets/img/<?= htmlspecialchars($fav['imagen'] ?? 'default-pet.jpg') ?>"
                                        alt="<?= htmlspecialchars($fav['nombre'] ?? 'Mascota') ?>"
                                    />
                                </figure>
                                <h4><?= htmlspecialchars($fav['nombre'] ?? 'Sin nombre') ?></h4>
                                <p>
                                    <?= htmlspecialchars($fav['edad'] ?? '?') ?> año(s)
                                    · <?= htmlspecialchars($fav['tamano'] ?? '—') ?>
                                    · <?= htmlspecialchars($fav['temperamento'] ?? '—') ?>
                                </p>
                            </a>
                            <form method="POST" action="/favorito/eliminar" class="form-quitar-fav">
                                <input type="hidden" name="favorito_id" value="<?= $fav['favorito_id'] ?>" />
                                <button type="submit" class="btn-corazon activo" title="Quitar favorito">
                                    <span class="material-symbols-outlined">favorite</span>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <!-- Sección: Solicitudes -->
        <section class="perfil-seccion" id="sec-solicitudes">
            <h3>Solicitudes de adopción</h3>
            <?php if (empty($solicitudes)): ?>
                <article class="perfil-vacio">
                    <span class="material-symbols-outlined">mail</span>
                    <p>Todavía no hiciste solicitudes de adopción.</p>
                </article>
            <?php else: ?>
                <ul class="perfil-lista">
                    <?php foreach ($solicitudes as $sol): ?>
                        <li class="perfil-lista-item">
                            <h4><?= htmlspecialchars($sol['nombre'] ?? 'Mascota') ?></h4>
                            <p>
                                <?= htmlspecialchars($sol['edad'] ?? '?') ?> año(s)
                                · <?= htmlspecialchars($sol['tamano'] ?? '—') ?>
                                · <?= htmlspecialchars($sol['temperamento'] ?? '—') ?>
                            </p>
                            <span class="perfil-estado estado-<?= strtolower($sol['estado'] ?? 'pendiente') ?>">
                                <?= htmlspecialchars($sol['estado'] ?? 'PENDIENTE') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <!-- Sección: Adopciones -->
        <section class="perfil-seccion" id="sec-adopciones">
            <h3>Adopciones</h3>
            <?php if (empty($adopciones)): ?>
                <article class="perfil-vacio">
                    <span class="material-symbols-outlined">pets</span>
                    <p>Todavía no adoptaste ninguna mascota.</p>
                </article>
            <?php else: ?>
                <ul class="perfil-lista">
                    <?php foreach ($adopciones as $ad): ?>
                        <li class="perfil-lista-item">
                            <h4><?= htmlspecialchars($ad['nombre'] ?? 'Mascota') ?></h4>
                            <p>
                                <?= htmlspecialchars($ad['edad'] ?? '?') ?> año(s)
                                · <?= htmlspecialchars($ad['tamano'] ?? '—') ?>
                                · <?= htmlspecialchars($ad['temperamento'] ?? '—') ?>
                            </p>
                            <a href="/mascota?id=<?= $ad['id'] ?>" class="btn-ver-detalle" title="Ver detalle">
                                <span class="material-symbols-outlined">directions_walk</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

    </main>

    <script>
        // Marcar el enlace activo según la sección visible
        const secciones = document.querySelectorAll('.perfil-seccion');
        const enlaces = document.querySelectorAll('.perfil-nav a');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    enlaces.forEach(a => a.classList.remove('active'));
                    const link = document.querySelector(`.perfil-nav a[href="#${entry.target.id}"]`);
                    if (link) link.classList.add('active');
                }
            });
        }, { rootMargin: '-40% 0px -55% 0px' });

        secciones.forEach(s => observer.observe(s));

        // Scroll suave al hacer click
        enlaces.forEach(a => {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>

    <?php require __DIR__ . '/footer.view.php'; ?>

</body>
</html>
