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
    <link rel="stylesheet" href="/assets/css/adopcion-exitosa.css" />
    <title>Solicitud Enviada - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main>
        <section class="mensaje-exito">
            <span class="material-symbols-outlined icono-exito">check_circle</span>
            <h1>¡Solicitud Enviada!</h1>
            <p>Hemos procesado tu solicitud de adopción correctamente.<br> 
               Te hemos enviado un correo electrónico con los detalles.<br>
               El refugio se pondrá en contacto con usted a la brevedad.</p>
            
            <a href="/adoptar" class="btn-volver">VOLVER A ADOPTAR</a>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
