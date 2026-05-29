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
    <title>Donaciones - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>
    <main>
        <header class="hero-donaciones">
            <h1>DONAR A UN REFUGIO</h1>
            <p>Tu ayuda transforma vidas peludas</p>
        </header> 
        <section class="formulario-donacion">
            <form action="/procesar-donacion" method="POST">

                <!-- Selector de Refugio -->
                <label for="refugio_id">Seleccioná un refugio</label>
                <select name="refugio_id" id="refugio_id" required>
                    <?php foreach ($refugios as $refugio): ?>
                        <option value="<?= $refugio->getId() ?>">
                            <?= htmlspecialchars($refugio->getNombre(), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Datos Financieros (Autocompletados por JS al elegir refugio) -->
                <label>ALIAS</label>
                <input type="text" id="alias" readonly placeholder="Alias autocompletado">

                <label>CVU</label>
                <input type="text" id="cvu" readonly placeholder="CVU autocompletado">

                <!-- Selección de Monto -->
                <label>Elegir un monto</label>
                <div class="opciones-monto">
                    <button type="button" onclick="setMonto(1000)">$1000</button>
                    <button type="button" onclick="setMonto(5000)">$5000</button>
                    <button type="button" onclick="setMonto(10000)">$10000</button>
                </div>

                <label for="monto_personalizado">Monto Personalizado</label>
                <input type="number" name="monto" id="monto_personalizado" placeholder="$6500">

                <h3>Metodos de Pago:</h3>
                <label><input type="radio" name="metodo_pago" value="mp"> Mercado Pago</label>
                <label><input type="radio" name="metodo_pago" value="transferencia"> Transferencia Bancaria</label>

                <button type="submit" class="btn-confirmar">¡CONFIRMAR Y PAGAR!</button>
            </form>
        </section>
    </main> 
    </body>
</html>