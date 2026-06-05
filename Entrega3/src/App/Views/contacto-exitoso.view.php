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
    
    <title>Contacto Exitoso - PawMap</title>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="success-container container">
        <section class="success-card">
            <div class="success-icon">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
            
            <h1>¡Mensaje Enviado!</h1>
            <p>Gracias por ponerte en contacto con PawMap. Hemos recibido tu mensaje correctamente y nos comunicaremos con vos a la brevedad.</p>
            
            <nav>
                <a href="/" class="btn-home">Volver al Inicio</a>
            </nav>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>

</body>
</html>
