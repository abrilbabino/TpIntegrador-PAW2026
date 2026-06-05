<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'PawMap: Encuentra a tu compañero ideal. Adopta perros y gatos en adopción de los mejores refugios.') ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    
    <link rel="stylesheet" href="/assets/css/style.css" />
    
    <link 
        rel="stylesheet" 
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" 
    />
    <title>Contacto - PawMap</title>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="container contact-form-section">
        <header class="hero-banner">
            <h1>Contactanos</h1>
        </header>

        <section class="contacto">
            <!-- Columna Izquierda: Formulario -->
            <article class="formulario">
                <h2 class="envia-mensaje">Envianos un mensaje</h2>
                <p class="texto">¿Tenés alguna duda o querés colaborar con nosotros? Completá el siguiente formulario y nos pondremos en contacto a la brevedad.</p>
                
                <form action="/contacto/enviar" method="POST">
                    <section class="row">
                        <fieldset class="nombre">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Tu nombre" required>
                        </fieldset>
                        <fieldset class="email">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="nombre@ejemplo.com" required>
                        </fieldset>
                    </section>
                    
                    <fieldset class="asunto">
                        <label for="asunto" class="form-label">Organización / Asunto</label>
                        <input type="text" class="form-control" id="asunto" name="asunto" placeholder="Motivo de tu contacto">
                    </fieldset>
                    
                    <fieldset class="mensaje">
                        <label for="mensaje" class="form-label">Mensaje</label>
                        <textarea class="form-control" id="mensaje" name="mensaje" rows="6" placeholder="Escribí tu mensaje aquí..." required></textarea>
                    </fieldset>
                    
                    <button type="submit" class="btn btn-enviar">Enviar Mensaje</button>
                </form>
            </article>

            <!-- Columna Derecha: Información -->
            <aside class="informacion">
                <section class="info-sidebar">
                    <h3>Información de Contacto</h3>
                    
                    <ul class="contact-list">    
                        <li class="contact-item">
                            <span class="material-symbols-outlined">location_on</span>
                            <address class="direccion">
                                <strong>Dirección</strong><br>
                                Calle 105 n3060, Mercedes, Bs. As.
                            </address>
                        </li>

                        <li class="contact-item">
                            <span class="material-symbols-outlined">mail</span>
                            <p class="email">
                                <strong>Email</strong><br>
                                pawmap2026@gmail.com
                            </p>
                        </li>
                        
                        <li class="contact-item">
                            <span class="material-symbols-outlined">call</span>
                            <p class="telefono">
                                <strong>Teléfono</strong><br>
                                +54 2324 513983
                            </p>
                        </li>
                    </ul>
                </section>
            </aside>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
