<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/print.css" media="print" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <title>PawMap</title>
</head>
  <body>
    <?php require __DIR__ . '/barra-navegacion.view.php' ?>
    
    <main>
      <header>
        <h1>Encuentra a tu nuevo<br>mejor amigo</h1>
        <p>Explora mascotas cerca de tu ubicación.</p>
      </header>

      <section class="seccion-mapa">
        <figure>
          <iframe
            src="https://www.google.com/maps/d/embed?mid=1fqEzq6nPa6IITzVkvMWnMvfQsy2sqrQ&ehbc=2E312F&noprof=1"
            title="Mapa de refugios y mascotas"
          ></iframe>
        </figure>
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
          <a href="/formulario-adopcion" class="boton-secundario">Formulario de Adopción</a>
        </article>

      </section>

      <section class="seccion-adopcion">
        <h2>Mascotas en Adopción</h2>
        
        <section class="grilla-mascotas">
            <?php if (!empty($mascotas)): ?>
                <?php foreach ($mascotas as $mascota): ?>
                    <article class="tarjeta-mascota">
                        <figure class="tarjeta-imagen">
                            <img src="/assets/img/<?= htmlspecialchars($mascota->fields['imagen'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($mascota->fields['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                            
                            <a href="/mascota?id=<?= htmlspecialchars((string)$mascota->fields['id'], ENT_QUOTES, 'UTF-8') ?>" class="verPerfil">Ver Perfil</a>
                        </figure>
                        
                        <section class="tarjeta-info">
                            <h3><?= htmlspecialchars($mascota->fields['nombre'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>
                                <?= htmlspecialchars((string)$mascota->fields['edad'], ENT_QUOTES, 'UTF-8') ?> años - 
                                <?= htmlspecialchars(ucfirst($mascota->fields['tamano']), ENT_QUOTES, 'UTF-8') ?> - 
                                <?= htmlspecialchars(ucfirst($mascota->fields['temperamento']), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </section>
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