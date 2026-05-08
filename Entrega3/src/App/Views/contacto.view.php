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
    
    <!-- Fuentes y Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/assets/css/contacto.css" />
    
    <title><?= htmlspecialchars($titulo ?? 'Contactanos - PawMap') ?></title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <header class="hero-banner">
        <h1>Contactanos</h1>
    </header>

    <main class="container contact-form-section">
        <section class="row g-5">
            <!-- Columna Izquierda: Formulario -->
            <article class="col-lg-7 col-md-8">
                <h2 class="mb-4">Envianos un mensaje</h2>
                <p class="mb-4 text-muted">¿Tenés alguna duda o querés colaborar con nosotros? Completá el siguiente formulario y nos pondremos en contacto a la brevedad.</p>
                
                <form action="/contacto/enviar" method="POST">
                    <section class="row">
                        <fieldset class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Tu nombre" required>
                        </fieldset>
                        <fieldset class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="nombre@ejemplo.com" required>
                        </fieldset>
                    </section>
                    
                    <fieldset class="mb-3">
                        <label for="asunto" class="form-label">Organización / Asunto</label>
                        <input type="text" class="form-control" id="asunto" name="asunto" placeholder="Motivo de tu contacto">
                    </fieldset>
                    
                    <fieldset class="mb-4">
                        <label for="mensaje" class="form-label">Mensaje</label>
                        <textarea class="form-control" id="mensaje" name="mensaje" rows="6" placeholder="Escribí tu mensaje aquí..." required></textarea>
                    </fieldset>
                    
                    <button type="submit" class="btn btn-enviar">Enviar Mensaje</button>
                </form>
            </article>

            <!-- Columna Derecha: Información -->
            <aside class="col-lg-5 col-md-4">
                <section class="info-sidebar">
                    <h3>Información de Contacto</h3>
                    
                    <ul class="list-unstyled p-0 m-0">
                        <li class="contact-item">
                            <span class="material-symbols-outlined">location_on</span>
                            <address class="m-0">
                                <strong>Dirección</strong><br>
                                Av. Principal 1234, Ciudad Autónoma de Buenos Aires, Argentina
                            </address>
                        </li>
                        
                        <li class="contact-item">
                            <span class="material-symbols-outlined">mail</span>
                            <p class="m-0">
                                <strong>Email</strong><br>
                                <a href="mailto:pawmap2026@gmail.com">pawmap2026@gmail.com</a>
                            </p>
                        </li>
                        
                        <li class="contact-item">
                            <span class="material-symbols-outlined">call</span>
                            <p class="m-0">
                                <strong>Teléfono</strong><br>
                                +54 11 4567-8901
                            </p>
                        </li>
                    </ul>
                    
                    <footer class="mt-4">
                        <a href="https://maps.google.com" target="_blank" class="map-link d-flex align-items-center">
                            <span class="material-symbols-outlined me-2">map</span>
                            Cómo llegar / Ver Mapa
                        </a>
                    </footer>
                </section>
            </aside>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
