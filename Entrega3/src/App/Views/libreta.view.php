<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/libreta.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title>Libreta Sanitaria - <?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?> - PawMap</title>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="libreta-main"
          data-error-carga="<?= (isset($_GET['error']) && $_GET['error'] === 'error_carga') ? 'true' : 'false' ?>"
          data-error-registro-id="<?= htmlspecialchars($_GET['registro_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <header class="hero-libreta">
            <h1>Libreta Sanitaria de <?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?></h1>
        </header>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'permisos_denegados'): ?>
            <div class="mensaje-error libreta-mensaje-error">
                No tenés permisos para modificar esta libreta.
            </div>
        <?php endif; ?>

        <div data-paw-filtros="libreta" data-mascota-id="<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <!-- PAWFiltros injected here -->
        </div>

        <section class="pendientes" id="pendientes-container">
            <!-- PAWVisualizacion injected here -->
        </section>

        <section class="historial" id="historial-container">
            <!-- PAWVisualizacion injected here -->
        </section>

        <?php if ($puedeAgregar): ?>
            <button type="button" class="btn-agregar-registro" id="btn-abrir-agregar-registro">
                <span class="material-symbols-outlined">add</span> Agregar Registro
            </button>
        <?php endif; ?>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>

    <dialog id="modal-agregar-registro" class="modal-nativo">
        <header>
            <h2>Agregar Registro Sanitario</h2>
        </header>
        <form method="POST" action="/mascota/registro/guardar" class="form-registro">
            <input type="hidden" name="mascota_id" value="<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

            <label for="tipo">Tipo de registro</label>
            <select name="tipo" id="tipo" required>
                <option value="" disabled selected>Seleccioná un tipo</option>
                <option value="vacuna">Vacunación</option>
                <option value="desparasitacion">Desparasitación</option>
                <option value="cirugia">Cirugía</option>
                <option value="tratamiento">Tratamiento</option>
                <option value="chequeo">Chequeo</option>
            </select>

            <label for="titulo">Título / Descripción</label>
            <input type="text" id="titulo" name="titulo" placeholder="Ej: Antirrábica" required>

            <label for="fecha_programada">Fecha programada</label>
            <input type="date" id="fecha_programada" name="fecha_programada" required>

            <label for="observaciones">Observaciones (opcional)</label>
            <textarea id="observaciones" name="observaciones" rows="3" placeholder="Notas adicionales..."></textarea>

            <footer class="acciones-modal">
                <button type="button" class="btn-cancelar js-cerrar-modal" data-modal="modal-agregar-registro">Cancelar</button>
                <button type="submit" class="btn-guardar-registro">Guardar Registro</button>
            </footer>
        </form>
    </dialog>

    <dialog id="modal-completar-registro" class="modal-nativo">
        <header>
            <h2>Completar Registro</h2>
        </header>
        <form method="POST" action="/mascota/registro/completar" class="form-registro" enctype="multipart/form-data">
            <input type="hidden" name="mascota_id" value="<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="registro_id" id="completar_registro_id" value="">

            <p class="modal-completar-desc">Para marcar este registro como completado, debés adjuntar el certificado correspondiente.</p>

            <label for="archivo">Subir Certificado / Comprobante</label>
            <input type="file" name="archivo" id="archivo" accept="image/*,.pdf" required 
                class="input-file-completar <?php if (isset($_GET['error']) && $_GET['error'] === 'error_carga') echo 'input-invalido'; ?>">

            <footer class="acciones-modal">
                <button type="button" class="btn-cancelar js-cerrar-modal" data-modal="modal-completar-registro">Cancelar</button>
                <button type="submit" class="btn-guardar-registro">Completar y Subir</button>
            </footer>
        </form>
    </dialog>
</body>
</html>
