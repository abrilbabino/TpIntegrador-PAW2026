<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Estilos generales del proyecto -->
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/contactoexitoso.css" />
    
    <!-- Fuentes y Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <title><?= htmlspecialchars($titulo ?? 'Contacto Exitoso - PawMap') ?></title>
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

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
