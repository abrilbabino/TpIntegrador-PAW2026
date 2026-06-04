<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="" defer></script>
    <script src="/assets/js/components/paw.js"></script>
    <script src="/assets/js/app.js"></script>

    <title><?= $titulo ?? 'Mapa Interactivo - PawMap' ?></title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php' ?>
    
    <main class="contenedor-mapa-page">
        <aside class="sidebar-mapa seccion-filtros">
            <details class="filtros" open>
                <summary>
                    <span class="material-symbols-outlined">filter_list</span>
                    <span>Filtros</span>
                    <span class="material-symbols-outlined filtros-chevron">expand_more</span>
                </summary>
                
                <form action="/mapa" method="GET" id="form-filtros">
                    <p>Ubicación</p>
                    <fieldset class="input-con-icono">
                        <input type="text" name="ubicacion" placeholder="Ingresá tu ubicación" value="<?= htmlspecialchars($_GET['ubicacion'] ?? '') ?>">
                    </fieldset>
                    
                    <input type="hidden" name="lat_usuario" id="lat_usuario" value="<?= htmlspecialchars($_GET['lat_usuario'] ?? '') ?>">
                    <input type="hidden" name="lng_usuario" id="lng_usuario" value="<?= htmlspecialchars($_GET['lng_usuario'] ?? '') ?>">
                    
                    <p>Rango de Edad</p>
                    <fieldset class="edad-rango">
                        <input type="number" name="edad_min" placeholder="Mín" min="0" value="<?= htmlspecialchars($_GET['edad_min'] ?? '') ?>">
                        <span>a</span>
                        <input type="number" name="edad_max" placeholder="Máx" min="0" value="<?= htmlspecialchars($_GET['edad_max'] ?? '') ?>">
                    </fieldset>

                    <p>Tamaño</p>
                    <select name="tamano">
                        <option value="">Todos</option>
                        <option value="pequeño" <?= ($_GET['tamano'] ?? '') === 'pequeño' ? 'selected' : '' ?>>Pequeño</option>
                        <option value="mediano" <?= ($_GET['tamano'] ?? '') === 'mediano' ? 'selected' : '' ?>>Mediano</option>
                        <option value="grande" <?= ($_GET['tamano'] ?? '') === 'grande' ? 'selected' : '' ?>>Grande</option>
                    </select>

                    <p>Especie</p>
                    <fieldset class="filtro-especie-grupo">
                        <label class="especie-radio">
                            <input type="radio" name="especie" value="perro" <?= ($_GET['especie'] ?? '') === 'perro' ? 'checked' : '' ?>>
                            <span>Perro</span>
                        </label>
                        <label class="especie-radio">
                            <input type="radio" name="especie" value="gato" <?= ($_GET['especie'] ?? '') === 'gato' ? 'checked' : '' ?>>
                            <span>Gato</span>
                        </label>
                        <label class="especie-radio">
                            <input type="radio" name="especie" value="" <?= empty($_GET['especie']) ? 'checked' : '' ?>>
                            <span>Todos</span>
                        </label>
                    </fieldset>

                    <p>Temperamento</p>
                    <select name="temperamento">
                        <option value="">Todos</option>
                        <option value="tranquilo" <?= ($_GET['temperamento'] ?? '') === 'tranquilo' ? 'selected' : '' ?>>Tranquilo</option>
                        <option value="jugueton" <?= ($_GET['temperamento'] ?? '') === 'jugueton' ? 'selected' : '' ?>>Juguetón</option>
                        <option value="protector" <?= ($_GET['temperamento'] ?? '') === 'protector' ? 'selected' : '' ?>>Protector</option>
                        <option value="amigable" <?= ($_GET['temperamento'] ?? '') === 'amigable' ? 'selected' : '' ?>>Amigable</option>
                        <option value="energetico" <?= ($_GET['temperamento'] ?? '') === 'energetico' ? 'selected' : '' ?>>Energético</option>
                    </select>

                    <button type="submit">Aplicar Filtros</button>
                </form>
            </details>

            <section class="refugios-cercanos sidebar-only-desktop">
                <header class="titulo-refugios-cercanos">
                    <span class="material-symbols-outlined">home</span>
                    <h3>Refugios cerca de ti</h3>
                </header>
                
                <?php 
                $hasLocation = !empty($_GET['lat_usuario']) || !empty($_GET['ubicacion']);
                if (!$hasLocation): 
                ?>
                    <section class="mensaje-gps-mapa">
                        <span class="material-symbols-outlined icono-gps-mapa">location_off</span>
                        Activá tu GPS en el mapa o ingresá tu ubicación arriba para descubrir los refugios de tu zona.
                    </section>
                <?php else: ?>
                    <ul class="lista-refugios-sidebar">
                        <?php 
                        $limitRefugios = 3;
                        $count = 0;
                        if (!empty($refugiosMapa)): 
                            foreach ($refugiosMapa as $rm): 
                                if ($count >= $limitRefugios) break;
                        ?>
                            <li class="refugio-cercano-item">
                                <img src="/assets/img/<?= htmlspecialchars($rm['imagen'] ?? 'default-refugio.jpg') ?>" class="refugio-mini-img" alt="Refugio">
                                <section class="refugio-mini-info">
                                    <h4><?= htmlspecialchars($rm['nombre_institucion'] ?? 'Refugio') ?></h4>
                                    <p><span class="material-symbols-outlined icon-small">location_on</span> <?= htmlspecialchars(($rm['ciudad'] ?? 'Ubicación') . ', ' . ($rm['provincia'] ?? '')) ?></p>
                                    <?php if (isset($rm['distancia_km'])): ?>
                                        <span class="refugio-distancia">A <?= number_format($rm['distancia_km'], 1) ?> km</span>
                                    <?php endif; ?>
                                </section>
                            </li>
                        <?php 
                                $count++;
                            endforeach; 
                        else: ?>
                            <li class="item-vacio-refugio">No hay refugios cercanos a esta ubicación.</li>
                        <?php endif; ?>
                    </ul>
                    <a href="/refugios" class="boton-secundario boton-ver-todos">Ver todos</a>
                <?php endif; ?>
            </section>
        </aside>

        <section class="contenido-mapa">
            <section class="mapa-wrapper">
                <figure id="leaflet-map" class="mapa-interactivo" data-refugios='<?= htmlspecialchars(json_encode($refugiosMapa ?? []), ENT_QUOTES, 'UTF-8') ?>'></figure>
                <button type="button" id="btn-gps-flotante" class="btn-gps-flotante" title="Centrar en mi ubicación">
                    <span class="material-symbols-outlined">my_location</span>
                </button>
                <aside class="mapa-nota-gps">
                    <span class="material-symbols-outlined icon-small">info</span>
                    En PC, la ubicación puede ser inexacta por falta de GPS.
                </aside>
            </section>

            <section class="mascotas-zona">
                <span class="decorador-linea"></span>
                <h2>Mascotas en la zona</h2>
                
                <section class="carrusel-mascotas">
                    <?php if (!empty($mascotas)): ?>
                        <?php foreach ($mascotas as $mascota): 
                            if (!is_object($mascota) || !isset($mascota->fields)) continue;
                        ?>
                            <article class="tarjeta-mascota">
                                <figure class="tarjeta-imagen">
                                    <a href="/mascota?id=<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="link-imagen">
                                        <img src="/assets/img/<?= htmlspecialchars($mascota->fields['imagen'] ?? 'default-pet.jpg', ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($mascota->fields['nombre'] ?? 'Mascota', ENT_QUOTES, 'UTF-8') ?>">
                                    </a>
                                    <form method="POST" action="/favorito" class="form-favorito-tarjeta">
                                        <input type="hidden" name="mascota_id" value="<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn-favorito" aria-label="Agregar a favoritos">
                                            <span class="material-symbols-outlined">favorite</span>
                                        </button>
                                    </form>
                                </figure>

                                <a href="/mascota?id=<?= htmlspecialchars((string)($mascota->fields['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="verPerfil">
                                    <section class="tarjeta-info">
                                        <header class="tarjeta-info-header">
                                            <h3><?= htmlspecialchars($mascota->fields['nombre'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8') ?></h3>
                                            <span class="btn-ver-perfil-mobile">Ver Perfil</span>
                                        </header>
                                        <p>
                                            <?= htmlspecialchars((string)($mascota->fields['edad'] ?? '0'), ENT_QUOTES, 'UTF-8') ?> años -
                                            <?= htmlspecialchars(ucfirst($mascota->fields['tamano'] ?? 'Desconocido'), ENT_QUOTES, 'UTF-8') ?> -
                                            <?= htmlspecialchars(ucfirst($mascota->fields['temperamento'] ?? 'Desconocido'), ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </section>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No se encontraron mascotas que coincidan con los filtros.</p>
                    <?php endif; ?>
                </section>

                <section class="refugios-cercanos mobile-only refugios-mobile-espaciado">
                    <header class="titulo-refugios-cercanos">
                        <span class="material-symbols-outlined">home</span>
                        <h3>Refugios cerca de ti</h3>
                    </header>
                    
                    <?php 
                    $hasLocation = !empty($_GET['lat_usuario']) || !empty($_GET['ubicacion']);
                    if (!$hasLocation): 
                    ?>
                        <section class="mensaje-gps-mapa">
                            <span class="material-symbols-outlined icono-gps-mapa">location_off</span>
                            Activá tu GPS en el mapa o ingresá tu ubicación arriba para descubrir los refugios de tu zona.
                        </section>
                    <?php else: ?>
                        <ul class="lista-refugios-sidebar">
                            <?php 
                            $limitRefugios = 3;
                            $count = 0;
                            if (!empty($refugiosMapa)): 
                                foreach ($refugiosMapa as $rm): 
                                    if ($count >= $limitRefugios) break;
                            ?>
                                <li class="refugio-cercano-item">
                                    <img src="/assets/img/<?= htmlspecialchars($rm['imagen'] ?? 'default-refugio.jpg') ?>" class="refugio-mini-img" alt="Refugio">
                                    <section class="refugio-mini-info">
                                        <h4><?= htmlspecialchars($rm['nombre_institucion'] ?? 'Refugio') ?></h4>
                                        <p><span class="material-symbols-outlined icon-small">location_on</span> <?= htmlspecialchars(($rm['ciudad'] ?? 'Ubicación') . ', ' . ($rm['provincia'] ?? '')) ?></p>
                                        <?php if (isset($rm['distancia_km'])): ?>
                                            <span class="refugio-distancia">A <?= number_format($rm['distancia_km'], 1) ?> km</span>
                                        <?php endif; ?>
                                    </section>
                                </li>
                            <?php 
                                    $count++;
                                endforeach; 
                            else: ?>
                                <li class="item-vacio-refugio">No hay refugios cercanos a esta ubicación.</li>
                            <?php endif; ?>
                        </ul>
                        <a href="/refugios" class="boton-secundario boton-ver-todos">Ver todos</a>
                    <?php endif; ?>
                </section>
            </section>
        </section>
    </main>

    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
