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
    <main class="donar-main">
        <header class="hero-donaciones">
            <h1>DONAR A UN REFUGIO</h1>
            <p>Tu ayuda transforma vidas peludas</p>
        </header> 
        <section class="formulario-donacion">
            <figure class="imagen-refugio">
                <img id="imagen-refugio" src="/assets/img/animalesTiernos.jpg" alt="Imagen de mascotas tiernas">
                <figcaption id="descripcion-refugio">Tu donación nos ayuda a comprar alimento, pagar gastos veterinarios y seguir rescatando animalitos de la calle. ¡Gracias por sumar tu granito de arena!</figcaption>
            </figure>
            <form class="formulario-donaciones" action="/procesar-donacion" method="POST">
                <!-- Selector de Refugio -->
                <label for="refugio_id">Seleccioná un refugio</label>
                <select name="refugio_id" id="refugio_id" required>
                    <?php if (empty($refugios)): ?>
                        <option value="">No hay refugios disponibles</option>
                    <?php endif; ?>
                    <?php foreach ($refugios as $refugio): ?>
                        <?php $seleccionado = (string) ($valores['refugio_id'] ?? '') === (string) $refugio->getId(); ?>
                        <option value="<?= $refugio->getId() ?>"
                                data-alias="<?= htmlspecialchars((string) ($refugio->getAlias() ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                data-cvu="<?= htmlspecialchars((string) ($refugio->getCvu() ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                <?= $seleccionado ? 'selected' : '' ?>>
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
                <input type="number" name="monto" id="monto_personalizado" min="1" step="1" value="<?= htmlspecialchars((string) ($valores['monto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="$6500" required>

                <h3>Metodos de Pago:</h3>
                <label><input type="radio" name="metodo_pago" value="mp" <?= ($valores['metodo_pago'] ?? '') === 'mp' ? 'checked' : '' ?> required> Mercado Pago</label>
                <label><input type="radio" name="metodo_pago" value="transferencia" <?= ($valores['metodo_pago'] ?? '') === 'transferencia' ? 'checked' : '' ?>> Transferencia Bancaria</label>

                <button type="submit" class="btn-confirmar" <?= empty($refugios) ? 'disabled' : '' ?>>¡CONFIRMAR Y PAGAR!</button>
            </form>
        </section>
    </main> 

    <?php require __DIR__ . '/footer.view.php'; ?>

    <script src="/assets/js/donar.js" defer></script>
</body>
</html>
