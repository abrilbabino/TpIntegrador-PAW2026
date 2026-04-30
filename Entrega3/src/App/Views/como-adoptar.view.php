<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
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
        <header>
            <h1>¿CÓMO ADOPTAR?</h1>
            <p>Una guía paso a paso para encontrar a tu compañero ideal en PawMap.</p>
        </header>

        <section class="pasos-adopcion">
            
            <article class="paso-individual">
                <header class="icono-paso-individual">
                    <span class="paso-numero">1</span>
                    <span class="material-symbols-outlined">search</span>
                </header>
                <h2>1. DESCUBRE A TU COMPAÑERO</h2>
                <p>Explora nuestro mapa y perfiles de mascotas. Filtra por especie, edad, tamaño y ubicación.</p>
            </article>

            <article class="paso-individual">
                <header class="icono-paso-individual">
                    <span class="paso-numero">2</span>
                    <span class="material-symbols-outlined">quiz</span>
                </header>
                <h2>2. TEST DE COMPATIBILIDAD</h2>
                <p>Completa nuestro test único. Te sugerimos mascotas que se adapten perfectamente a tu estilo de vida.</p>
            </article>

            <article class="paso-individual">
                <header class="icono-paso-individual">
                    <span class="paso-numero">3</span>
                    <span class="material-symbols-outlined">description</span>
                </header>
                <h2>3. ENVÍA TU SOLICITUD</h2>
                <p>¿Encontraste un match? Envía una solicitud de adopción directamente al refugio responsable.</p>
            </article>

            <article class="paso-individual">
                <header class="icono-paso-individual">
                    <span class="paso-numero">4</span>
                    <span class="material-symbols-outlined">event_available</span>
                </header>
                <h2>4. ENTREVISTA Y VISITA</h2>
                <p>El refugio revisará tu solicitud y coordinará una entrevista y visita para conocerte mejor.</p>
            </article>

            <article class="paso-individual">
                <header class="icono-paso-individual">
                    <span class="paso-numero">5</span>
                    <span class="material-symbols-outlined">pets</span>
                </header>
                <h2>5. BIENVENIDO A CASA</h2>
                <p>Completa los trámites, firma los documentos y lleva a tu nuevo amigo a casa. ¡Cambia una vida!</p>
            </article>

        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>

</body>
</html>