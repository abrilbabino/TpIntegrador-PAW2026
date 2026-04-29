<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
    <link rel="stylesheet" href="/assets/css//style.css" />
    <link rel="stylesheet" href="/assets/css/print.css" media="print" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
     <title>PawMap</title>
</head>
<body>
  <main>
    <header class="hero">
          <h1>Encuentra a tu nuevo<br>mejor amigo</h1>
          <p>Explora mascotas cerca de tu ubicación.</p>
      </header>

      <section class="puntos-refugios">
        <figure>
          <iframe
            src="https://www.google.com/maps/d/embed?mid=1fqEzq6nPa6IITzVkvMWnMvfQsy2sqrQ&ehbc=2E312F&noprof=1"
          ></iframe>
        </figure>
      </section>

    <section class="test-compatibilidad">
        <h2>¡Encontrá tu match!</h2>
        <p>¿Qué tipo de mascota buscás?</p>
        <a href="/test-de-compatibilidad" class="btn-realizar-test">Realizar Test</a>
    </section>


    <section class="grilla-mascotas">
        <h2>Mascotas en Adopción</h2>
        <a href="/mascotas" class="btn-ver-mascotas">Ver Todas las mascotas</a>
    </section>
  </main>
    <?php
      require __DIR__ . '/footer.view.php'
    ?>
 
</body>
</html>