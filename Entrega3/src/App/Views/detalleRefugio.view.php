<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'PawMap: Encuentra a tu compañero ideal. Adopta perros y gatos en adopción de los mejores refugios.') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/refugio-perfil.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="/assets/css/pawcarousel.css" />
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>
    <title><?= $refugio ? 'Perfil de ' . htmlspecialchars($refugio->getNombre()) : 'Refugio no encontrado' ?> - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main>
        <?php if (!$refugio): ?>
            <section class="perfil-not-found">
                <span class="material-symbols-outlined perfil-not-found__icon">error</span>
                <h2 class="perfil-not-found__title">Refugio no encontrado</h2>
                <p>El refugio que estás buscando no existe o fue eliminado.</p>
                <a href="/refugios" class="perfil-not-found__link">Volver a Refugios</a>
            </section>
        <?php else:
            $ciudades   = [];
            $provincias = [];
            if (!empty($ubicaciones)) {
                foreach ($ubicaciones as $u) {
                    if (!empty($u['ciudad']))    $ciudades[]   = $u['ciudad'];
                    if (!empty($u['provincia'])) $provincias[] = $u['provincia'];
                }
            }
            $ciudad  = implode(', ', array_unique($ciudades));
            $prov    = implode(', ', array_unique($provincias));
            $cantidadMascotas = count($mascotas);
            $ubicacionText = trim(($ciudad ? $ciudad . ', ' : '') . $prov, ', ') ?: 'A confirmar';
        ?>

        <article class="perfil-layout">

            <aside class="perfil-sidebar">

                <figure class="perfil-logo-wrapper">
                    <img src="/assets/img/<?= htmlspecialchars($refugio->fields['imagen'] ?? 'default-refugio.jpg') ?>"
                         alt="Logo de <?= htmlspecialchars($refugio->getNombre()) ?>"
                         class="perfil-logo">
                </figure>

                <header class="perfil-title-section perfil-title-mobile">
                    <h1><?= htmlspecialchars($refugio->getNombre()) ?></h1>
                    <p class="perfil-badge-aliado">
                        <span class="material-symbols-outlined">verified</span>
                        <span>Refugio Aliado PawMap en <?= htmlspecialchars($ubicacionText, ENT_QUOTES, 'UTF-8') ?></span>
                    </p>
                </header>

                <nav class="perfil-actions" aria-label="Acciones del refugio">
                    <a href="/contacto?refugio=<?= $refugio->getId() ?>" class="perfil-btn-primary">
                        <span class="material-symbols-outlined">mail</span> Contactar al Refugio
                    </a>
                    <?php if (!empty($refugio->fields['telefono'])): ?>
                    <a href="tel:<?= htmlspecialchars($refugio->fields['telefono'], ENT_QUOTES, 'UTF-8') ?>" class="perfil-btn-secondary llamar-mobile">
                        <span class="material-symbols-outlined">call</span> Llamar
                    </a>
                    <?php endif; ?>
                </nav>

                <ul class="perfil-stats">
                    <li class="stat-item">
                        <span class="stat-icon"><span class="material-symbols-outlined">pets</span></span>
                        <span class="stat-info">
                            <strong class="stat-label">Adoptables</strong>
                            <span class="stat-value"><?= $cantidadMascotas ?> mascotas</span>
                        </span>
                    </li>
                    <li class="stat-item">
                        <span class="stat-icon"><span class="material-symbols-outlined">location_on</span></span>
                        <span class="stat-info">
                            <strong class="stat-label">Ubicación</strong>
                            <span class="stat-value"><?= htmlspecialchars(($ciudad ?: ($prov ?: 'N/A')), ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                    </li>
                    <li class="stat-item">
                        <span class="stat-icon"><span class="material-symbols-outlined">email</span></span>
                        <span class="stat-info">
                            <strong class="stat-label">Email</strong>
                            <span class="stat-value stat-value--small"><?= htmlspecialchars($refugio->fields['email'] ?? 'No disponible') ?></span>
                        </span>
                    </li>
                </ul>

            </aside>

            <section class="perfil-content">

                <header class="perfil-title-section perfil-title-desktop">
                    <h1><?= htmlspecialchars($refugio->getNombre()) ?></h1>
                    <p class="perfil-badge-aliado">
                        <span class="material-symbols-outlined">verified</span>
                        <span>Refugio Aliado PawMap en <?= htmlspecialchars($ubicacionText, ENT_QUOTES, 'UTF-8') ?></span>
                    </p>
                </header>

                <section class="perfil-about-container">
                    <header class="perfil-about-header">
                        <h2 class="perfil-section-title">
                            <span class="material-symbols-outlined">info</span> Sobre Nosotros
                        </h2>
                        <?php if (!empty($refugio->fields['telefono'])): ?>
                        <a href="tel:<?= htmlspecialchars($refugio->fields['telefono'], ENT_QUOTES, 'UTF-8') ?>" class="perfil-btn-secondary llamar-desktop">
                            <span class="material-symbols-outlined">call</span> Llamar
                        </a>
                        <?php endif; ?>
                    </header>
                    <section class="perfil-about">
                        <p><?= nl2br(htmlspecialchars($refugio->getDescripcion() ?: 'Sin descripción disponible.')) ?></p>
                        <ul class="perfil-tags">
                            <li class="perfil-tag"><span class="material-symbols-outlined">verified</span>Adopción Responsable</li>
                            <li class="perfil-tag"><span class="material-symbols-outlined">verified</span>Cuidado Integral</li>
                            <li class="perfil-tag"><span class="material-symbols-outlined">verified</span>Atención Veterinaria</li>
                        </ul>
                    </section>
                </section>

            </section>

            <section class="perfil-pets-section">
                <h2 class="perfil-section-title">Mascotas en Adopción</h2>

                <?php if (empty($mascotas)): ?>
                    <p class="perfil-pets-empty">Este refugio no tiene mascotas publicadas actualmente.</p>
                <?php else: ?>
                    <section class="carrusel" data-paw-carousel data-paw-effect="zoom" data-paw-miniaturas="false">
                        <?php foreach ($mascotas as $mascota): ?>
                            <article class="tarjeta-mascota">
                                <figure class="tarjeta-imagen">
                                    <a href="/mascota?id=<?= htmlspecialchars(($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="link-imagen">
                                        <img src="/assets/img/<?= htmlspecialchars($mascota->fields['imagen'] ?? 'default.jpg', ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?>">
                                    </a>
                                    <form method="POST" action="/favorito" class="form-favorito-tarjeta">
                                        <input type="hidden" name="mascota_id" value="<?= htmlspecialchars(($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn-favorito" aria-label="Agregar a favoritos">
                                            <span class="material-symbols-outlined">favorite</span>
                                        </button>
                                    </form>
                                </figure>
                                <a href="/mascota?id=<?= htmlspecialchars(($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="verPerfil">
                                    <figcaption class="tarjeta-info">
                                        <h3><?= htmlspecialchars($mascota->fields['nombre'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8') ?></h3>
                                        <p>
                                            <?= htmlspecialchars((string)($mascota->fields['edad'] ?? '0'), ENT_QUOTES, 'UTF-8') ?> años -
                                            <?= htmlspecialchars(ucfirst($mascota->fields['tamano'] ?? 'Desconocido'), ENT_QUOTES, 'UTF-8') ?> -
                                            <?= htmlspecialchars(ucfirst($mascota->fields['temperamento'] ?? 'Desconocido'), ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </figcaption>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </section>
                <?php endif; ?>
            </section>

        </article>

        <?php endif; ?>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
