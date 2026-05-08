<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
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

    <main class="sesion-container">
        <section class="login-section">
            <header class="sesion-header">
                <h1>Iniciar Sesión</h1>

                <figure class="avatar">
                <span class="material-symbols-outlined">person</span>
            </figure>

            </header>

            <?php if (isset($_GET['error']) && $_GET['error'] == '1'): ?>
                <div class="login-error">
                    <span class="material-symbols-outlined">error</span>
                    Usuario o contraseña incorrectos.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'campos'): ?>
                <div class="login-error">
                    <span class="material-symbols-outlined">error</span>
                    Completá todos los campos.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'usuario_existente'): ?>
                <div class="login-error">
                    <span class="material-symbols-outlined">error</span>
                    El nombre de usuario ya está en uso.
                </div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="/login">
                <label for="user-login">Usuario</label>
                <input
                    type="text"
                    id="nombre_usuario"
                    name="nombre_usuario"
                    placeholder="Ingresá tu usuario"
                    required
                />

                <label for="pass-login">Contraseña</label>
                <div class="campo-contraseña">
                    <input
                        type="password"
                        id="contrasena"
                        name="contrasena"
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

        <form class="registro-form" method="POST" action="/register">
            <label for="name">Nombre Completo</label>
            <input type="text" id="name" name="name" placeholder="Ingresá tu nombre" required />

            <label for="mail">Correo Electrónico</label>
            <input
                type="email"
                id="mail"
                name="email"
                placeholder="Ingresá tu correo electrónico"
                required
            />

            <label for="user-register">Usuario</label>
            <input
                type="text"
                id="user-register"
                name="username"
                placeholder="Ingresá un usuario"
                required
            />

            <label for="pass-register">Contraseña</label>
            <div class="campo-contraseña">
                <input
                    type="password"
                    id="pass-register"
                    name="password"
                    placeholder="Ingresá tu contraseña"
                    required
                />
                <span class="material-symbols-outlined simbolos mostrar-contraseña">visibility_off</span>
            </div>

            <label for="rol">Tipo de cuenta</label>
            <select id="rol" name="rol" required class="registro-input">
                <option value="adoptante" selected>Quiero adoptar una mascota</option>
                <!-- <option value="refugio">Soy un refugio / protectora</option> -->
            </select>

            <button type="submit">Registrarme</button>
        </form>
    </aside>
    
    <?php require __DIR__ . '/footer.view.php'; ?>

    <script>
        document.querySelectorAll('.mostrar-contraseña').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    this.textContent = 'visibility';
                } else {
                    input.type = 'password';
                    this.textContent = 'visibility_off';
                }
            });
        });
    </script>
</body>
</html>
