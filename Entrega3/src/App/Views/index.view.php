<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'PawMap: Encuentra a tu compañero ideal. Adopta perros y gatos en adopción de los mejores refugios.') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/pawcarousel.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <title>Inicio - PawMap</title>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="" defer></script>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>
    <script type="application/ld+json">
    <?php $baseUrl = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST']; ?>
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "PawMap",
      "url": "<?= $baseUrl ?>/",
      "logo": "<?= $baseUrl ?>/assets/img/icon.png",
      "description": "Plataforma para conectar refugios y personas que desean adoptar mascotas en Argentina.",
      "sameAs": [
        "https://www.facebook.com/pawmap",
        "https://www.instagram.com/pawmap"
      ]
    }
    </script>
</head>
  <body>
    <?php require __DIR__ . '/barra-navegacion.view.php' ?>
    
    <main>
      <header class="hero-inicio">
        <h1>Encuentra a tu nuevo<br>mejor amigo</h1>
        <p>Explora mascotas cerca de tu ubicación.</p>
      </header>

      <section class="seccion-mapa">
        <figure id="leaflet-map" class="mapa-interactivo" data-refugios='<?= htmlspecialchars(json_encode($refugiosMapa ?? []), ENT_QUOTES, 'UTF-8') ?>'></figure>
        <a href="/mapa" class="boton-principal boton-ancho">Ver Mapa</a>
      </section>

      <section class="seccion-incentivar-adopcion">
        
        <article class="incentivar-adopcion-movil">
          <h2 class="titulo-incentivar-adopcion">¡Encontrá tu match!</h2>
          
          <div class="tarjeta-incentivar-adopcion">
            <p>¿Qué tipo de mascota buscás?</p>
            <a href="/test-de-compatibilidad" class="boton-secundario">Realizar Test</a>
          </div>
        </article>

        <article class="incentivar-adopcion-desktop">
          <header class="cuerpo-incentivar-adopcion">
            <h2>Animate a Adoptar</h2>
          </header>
          <a href="/como-adoptar" class="boton-secundario">Guía de Adopción</a>
        </article>

      </section>

        <section class="seccion-adopcion">
            <h2>Mascotas en Adopción</h2>
            
            <section class="carrusel" data-paw-carousel data-paw-effect="zoom" data-paw-miniaturas="false">
                <?php if (!empty($mascotas)): ?>
                    <?php 
                    foreach ($mascotas as $mascota): 
                        if (!is_object($mascota) || !isset($mascota->fields)) continue;
                    ?>
                        <article class="tarjeta-mascota">
                            <figure class="tarjeta-imagen">
                                <a href="/mascota?id=<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="link-imagen">
                                    <img src="/assets/img/<?= htmlspecialchars($mascota->fields['imagen'] ?? 'default-pet.jpg', ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?>">
                                </a>
                                <form method="POST" action="/favorito" class="form-favorito-tarjeta">
                                    <input type="hidden" name="mascota_id" value="<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" class="btn-favorito" aria-label="Agregar a favoritos">
                                        <span class="material-symbols-outlined">favorite</span>
                                    </button>
                                </form>
                            </figure>

                            <a href="/mascota?id=<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="verPerfil"> 
                                <section class="tarjeta-info">
                                    <h3><?= htmlspecialchars($mascota->fields['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p>
                                        <?= htmlspecialchars((string)($mascota->fields['edad'] ?? ''), ENT_QUOTES, 'UTF-8') ?> años - 
                                        <?= htmlspecialchars(ucfirst($mascota->fields['tamano'] ?? ''), ENT_QUOTES, 'UTF-8') ?> - 
                                        <?= htmlspecialchars(ucfirst($mascota->fields['temperamento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </section>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay mascotas en adopción en este momento.</p>
                <?php endif; ?>
            </section>

            <a href="/adoptar" class="boton-principal boton-centrado">Ver Todas las mascotas</a>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
    
    
  </body>
</html>
