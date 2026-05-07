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
    <title><?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?> - PawMap</title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main>

        <article class="seccion-detalle-mascota">
            <section class="galeria-mascota">
                <figure class="imagen-principal">
                    <img src="/assets/img/<?= htmlspecialchars($mascota->fields['imagen'] ?? 'default-pet.jpg', ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?>">
                </figure>
                <nav class="carrusel-indicadores" aria-label="Controles de galería">
                    <?php 
                    $cantidadTotalFotos = 1 + count($fotosExtras ?? []); 
                    if ($cantidadTotalFotos > 1):
                        for ($i = 0; $i < $cantidadTotalFotos; $i++): 
                    ?>
                            <span class="item-carrusel <?= $i === 0 ? 'active' : '' ?>"></span>
                    <?php 
                        endfor; 
                    endif; 
                    ?>
                </nav>

                <section class="mas-fotos">
                    <h4>MÁS FOTOS</h4>
                    <ul class="grid-miniaturas">
                        <?php if (!empty($fotosExtras)): ?>
                            <?php foreach ($fotosExtras as $foto): ?>
                                <li>
                                    <img src="/assets/img/<?= htmlspecialchars($foto->fields['ruta_imagen'], ENT_QUOTES, 'UTF-8') ?>" alt="Foto extra">
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay fotos adicionales.</p>
                        <?php endif; ?>
                    </ul>
                </section>
            </section>

            <aside class="info-mascota">
                <div class="drag-handle"></div>
                <header class="info-header">
                    <h2><?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?></h2>
                    <form method="POST" action="/favoritos" class="form-favorito">
                        <input type="hidden" name="mascota_id" value="<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="boton-favorito" aria-label="Agregar a favoritos">
                            <span class="material-symbols-outlined"><?= isset($esFavorito) && $esFavorito ? 'favorite' : 'favorite_border' ?></span>
                        </button>
                    </form>
                </header>

                <?php
                    $esp = ucfirst($mascota->fields['especie'] ?? 'Desconocido');
                    $ciudades = [];
                    $provincias = [];
                    foreach ($ubicaciones as $u) {
                        if (!empty($u['ciudad'])) $ciudades[] = $u['ciudad'];
                        if (!empty($u['provincia'])) $provincias[] = $u['provincia'];
                    }
                    $ciudad = implode(', ', array_unique($ciudades));
                    $prov = implode(', ', array_unique($provincias));
                    $ubicacion = trim(($ciudad ? $ciudad . ', ' : '') . $prov, ', ');
                ?>
                <p class="info-subtitulo">
                    <?= htmlspecialchars($esp, ENT_QUOTES, 'UTF-8') ?> •
                    <?= $ubicacion ? htmlspecialchars($ubicacion, ENT_QUOTES, 'UTF-8') : 'Ubicación a confirmar' ?>
                </p>

                <section class="estadisticas-mascota">
                    <article class="estadistica-box">
                        <span class="estadistica-label">Edad</span>
                        <span class="estadistica-valor"><?= htmlspecialchars((string)($mascota->fields['edad'] ?? '?'), ENT_QUOTES, 'UTF-8') ?> años</span>
                    </article>
                    <article class="estadistica-box">
                        <span class="estadistica-label">Tamaño</span>
                        <span class="estadistica-valor"><?= htmlspecialchars(ucfirst($mascota->fields['tamano'] ?? '?'), ENT_QUOTES, 'UTF-8') ?></span>
                    </article>
                    <article class="estadistica-box">
                        <span class="estadistica-label">Sexo</span>
                        <span class="estadistica-valor"><?= htmlspecialchars(ucfirst($mascota->fields['sexo'] ?? '?'), ENT_QUOTES, 'UTF-8') ?></span>
                    </article>
                </section>

                <section class="etiquetas-mascota">
                    <h4>Etiquetas</h4>
                    <ul class="etiquetas-container">
                        <li class="etiqueta"><?= htmlspecialchars(ucfirst($mascota->fields['temperamento'] ?? 'Juguetón'), ENT_QUOTES, 'UTF-8') ?></li>
                        <li class="etiqueta">Ideal Depto</li>
                        <li class="etiqueta">Convive con Gatos</li>
                    </ul>
                </section>

                <section class="sobre-mi">
                    <h4>SOBRE MÍ</h4>
                    <p class="sobre-mi-box"><?= htmlspecialchars($mascota->fields['descripcion'] ?? 'Sin descripción disponible.', ENT_QUOTES, 'UTF-8') ?></p>
                </section>

                <section class="refugio-info">
                    <img src="/assets/img/<?= htmlspecialchars($refugio->fields['imagen'] ?? 'default-refugio.jpg', ENT_QUOTES, 'UTF-8') ?>"
                         alt="Logo refugio" class="img-refugio">
                    <figcaption class="refugio-texto">
                        <span class="refugio-label">A cargo de:</span>
                        <strong><?= htmlspecialchars($refugio->fields['nombre_institucion'] ?? 'Desconocido', ENT_QUOTES, 'UTF-8') ?></strong>
                    </figcaption>
                </section>

                <a href="/formulario-adopcion?id=<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="boton-adoptar">¡QUIERO ADOPTAR!</a>
            </aside>
        </article>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
