<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <title>¿Cómo Adoptar? - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main>
        <header class="hero-como-adoptar">
            <h1>¿CÓMO ADOPTAR?</h1>
            <p>Una guía paso a paso para encontrar a tu compañero ideal en PawMap.</p>
        </header>

        <section class="pasos-adopcion">
            
            <article class="paso-individual">
                <p class="paso-numero">1</p>
                <span class="material-symbols-outlined">search</span>
                <h2>DESCUBRE</h2>
                <p class="descripcion">Explora nuestro mapa y perfiles de mascotas. Filtra por especie, edad, tamaño y ubicación.</p>
            </article>

            <article class="paso-individual">
                <p class="paso-numero">2</p>
                <span class="material-symbols-outlined">quiz</span>
                <h2>TEST</h2>
                <p class="descripcion">Completa nuestro test único. Te sugerimos mascotas que se adapten perfectamente a vos.</p>
            </article>

            <article class="paso-individual">
                <p class="paso-numero">3</p>
                <span class="material-symbols-outlined">description</span>
                <h2>SOLICITUD</h2>
                <p class="descripcion">¿Encontraste un match? Envía una solicitud de adopción directamente al refugio responsable.</p>
            </article>

            <article class="paso-individual">
                <p class="paso-numero">4</p>
                <span class="material-symbols-outlined">event_available</span>
                </header>
                <h2>ENTREVISTA Y VISITA</h2>
                <p class="descripcion">El refugio revisará tu solicitud y coordinará una entrevista y visita para conocerte mejor.</p>
            </article>

            <article class="paso-individual">
                <p class="paso-numero">5</p>
                <span class="material-symbols-outlined">pets</span>
                <h2>BIENVENIDO A CASA</h2>
                <p class="descripcion">Completa los trámites, firma los documentos y lleva a tu nuevo amigo a casa. ¡Cambia una vida!</p>
            </article>

        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>

</body>
</html>
