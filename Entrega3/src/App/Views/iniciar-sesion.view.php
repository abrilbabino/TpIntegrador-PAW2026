<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/iniciar-sesion.css" />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
    />
    <title>Iniciar Sesión</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main>
        <section class="login-section">
            <header class="sesion-header">
                <h1>Iniciar Sesión</h1>

                <figure class="avatar">
                <span class="material-symbols-outlined">person</span>
            </figure>

            </header>

            <form class="login-form">
                <label for="user-login">Usuario</label>
                <input
                    type="text"
                    id="user-login"
                    placeholder="Ingresá tu usuario"
                    required
                />

                <label for="pass-login">Contraseña</label>
                <div class="campo-contraseña">
                    <input
                        type="password"
                        id="pass-login"
                        placeholder="Ingresá tu contraseña"
                        required
                    />
                    <span class="material-symbols-outlined simbolos mostrar-contraseña">visibility_off</span>
                </div>

                <button type="submit">Iniciar Sesión</button>
            </form>
            <p>
                ¿No tenes cuenta aún?
                <label for="mostrar-registro" class="registro-link">Registrate aquí</label>
            </p>
        </section>
    </main>

    <input type="checkbox" id="mostrar-registro" class="registro-check" />
    <label for="mostrar-registro" class="fondo-registro"></label>
    <aside class="registro-panel">
        <header class="registro-header">
            <h2>Registrarme</h2>
            <label for="mostrar-registro" class="registro-cerrar">✕</label>
        </header>

        <form class="registro-form">
            <label for="name">Nombre Completo</label>
            <input type="text" id="name" placeholder="Ingresá tu nombre" required />

            <label for="mail">Correo Electrónico</label>
            <input
                type="email"
                id="mail"
                placeholder="Ingresá tu correo electrónico"
                required
            />

            <label for="user-register">Usuario</label>
            <input
                type="text"
                id="user-register"
                placeholder="Ingresá un usuario"
                required
            />

            <label for="pass-register">Contraseña</label>
            <div class="campo-contraseña">
                <input
                    type="password"
                    id="pass-register"
                    placeholder="Ingresá tu contraseña"
                    required
                />
                <span class="material-symbols-outlined simbolos mostrar-contraseña">visibility_off</span>
            </div>

            <button type="submit">Registrarme</button>
        </form>
    </aside>
    
    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>