<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="/assets/img/icon.png?v=2">
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <title><?= htmlspecialchars($titulo ?? 'Seguimiento Post-Adopción') ?></title>
</head>
<body>
    <?php require __DIR__ . '/barra-navegacion.view.php'; ?>

    <main class="seguimiento-layout">
        <section class="seguimiento-main">
                <article class="pet-card">
                    <figure class="imagen-mascota">
                        <img src="/assets/img/<?= htmlspecialchars($mascotaSeleccionada->fields['imagen'] ?? 'default-pet.jpg', ENT_QUOTES, 'UTF-8') ?>">
                    </figure>
                    <header class="pet-card-info">
                        <h1><?= htmlspecialchars($mascotaSeleccionada->fields['nombre']) ?></h1>
                        <p><?= htmlspecialchars($mascotaSeleccionada->fields['especie'] ?? '') ?> - <?= htmlspecialchars($mascotaSeleccionada->fields['edad'] ?? '') ?> años</p>
                    </header>
                </article>

                <!-- ROADMAP DE ENCUESTAS -->
                <section class="roadmap-encuestas">
                    <header>
                        <h2>Encuestas</h2>
                        <p>Completa las encuestas a medida que se habiliten según los días transcurridos desde la adopción.</p>
                    </header>
                        <?php foreach ($estadoEncuestas as $enc): ?>
                            <article class="encuesta-card estado-<?= strtolower($enc['estado']) ?>">
                                <header>
                                    <h4>Encuesta: <?= htmlspecialchars($enc['titulo']) ?></h4>
                                    <span class="badge-dias"><?= $enc['dias_requeridos'] ?> Días</span>
                                </header>
                                
                                <?php if ($enc['estado'] === 'COMPLETADA'): ?>
                                    <p class="estado-texto exito">
                                        <span class="material-symbols-outlined">check_circle</span> Completada
                                    </p>
                                <?php elseif ($enc['estado'] === 'HABILITADA'): ?>
                                    <button class="btn-accion" onclick="abrirModalEncuesta('<?= $enc['id'] ?>', '<?= htmlspecialchars($enc['titulo']) ?>')">
                                        <span class="material-symbols-outlined">assignment</span> Completar
                                    </button>
                                <?php else: ?>
                                    <p class="estado-texto bloqueado">
                                        <span class="material-symbols-outlined">lock</span> 
                                        Faltan <?= $enc['faltan'] ?> días
                                    </p>
                                    <button class="btn-accion btn-encuesta" disabled>
                                        Bloqueada
                                    </button>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                </section>

                <section id="historial-sanitario" class="timeline-section">
                    <header>
                        <h2>Recordatorios</h2>
                    </header>
                    <?php if (empty($registrosPendientes)): ?>
                        <p>No hay registros sanitarios pendientes para esta mascota.</p>
                    <?php else: ?>
                        <ul class="timeline">
                            <?php foreach ($registrosPendientes as $registro): ?>
                                <li class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <article class="timeline-content">
                                        <?php 
                                            $claseBadge = strtolower($registro->fields['estado']) === 'completado' ? 'badge-completado' : 'badge-pendiente';
                                        ?>
                                        <header>
                                            <h4><?= htmlspecialchars($registro->fields['titulo']) ?></h4>
                                            <span class="estado-badge <?= $claseBadge ?>"><?= htmlspecialchars($registro->fields['estado']) ?></span>
                                        </header>
                                        <?php 
                                            $fechaProg = strtotime($registro->fields['fecha_programada']);
                                            $vencido = (strtolower($registro->fields['estado']) === 'pendiente' && $fechaProg < time());
                                            $claseFecha = $vencido ? 'fecha-vencida' : 'fecha-normal';
                                        ?>
                                        <p class="<?= $claseFecha ?>"><strong>Programado:</strong> <?= htmlspecialchars(date('d/m/Y', $fechaProg)) ?></p>
                                        <?php if (!empty($registro->fields['fecha_realizada'])): ?>
                                            <p><strong>Realizado:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($registro->fields['fecha_realizada']))) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($registro->fields['archivo_adjunto'])): ?>
                                            <p><a href="<?= htmlspecialchars($registro->fields['archivo_adjunto']) ?>" target="_blank">Ver Comprobante</a></p>
                                        <?php endif; ?>
                                    </article>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
            <!-- BANNER DINAMICO DE ESTADO SANITARIO -->
                <section class="banner-alerta status-<?= htmlspecialchars($estadoGlobal ?? 'dia') ?>">
                    <article class="banner-content">
                        <span class="material-symbols-outlined icon-status">
                            <?= ($estadoGlobal === 'dia') ? 'check_circle' : 'warning' ?>
                        </span>
                        <section>
                            <h3><?= ($estadoGlobal === 'dia') ? 'Todo al día' : 'Atención: Tenes registros vencidos' ?></h3>
                            <?php if ($estadoGlobal === 'dia'): ?>
                                <p>Excelente trabajo manteniendo la libreta sanitaria al día.</p>
                            <?php else: ?>
                                <p>Revisa los recordatorios para ver qué acción sanitaria está pendiente.</p>
                            <?php endif; ?>
                        </section>
                    </article>
                    <a href="mascota/libreta?id=<?= htmlspecialchars((string)($mascotaSeleccionada->fields['id'] ?? ''),ENT_QUOTES,'UTF-8') ?>" class="btn-historial">
                        Ver Historial Sanitario
                    </a>
                </section>
                <section class="comprobantes-container">
                    <header>
                        <h2>Comprobantes</h2>
                    </header>
                    <button class="btn-comprobante" onclick="abrirModalSubida('foto')">
                        Fotos de <?= htmlspecialchars($mascotaSeleccionada->fields['nombre']) ?>
                        <span class="material-symbols-outlined icono-comprobante">attach_file</span>
                    </button>
                    <button class="btn-comprobante" onclick="abrirModalSubida('certificado')">
                        Certificado vacunación
                        <span class="material-symbols-outlined icono-comprobante">attach_file</span>
                    </button>
                    <button class="btn-comprobante" onclick="abrirModalSubida('comprobante')">
                        Comprobantes medicinales
                        <span class="material-symbols-outlined icono-comprobante">attach_file</span>
                    </button>
                </section>
        </section>
    </main>

    <!-- Modal Cargar Archivo -->
    <?php if ($mascotaSeleccionada): ?>
    <dialog id="modal-archivo">
        <header>
            <h3>Cargar Certificado o Comprobante</h3>
        </header>
        <form action="/seguimiento/subir-archivo" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="mascota_id" value="<?= $mascotaSeleccionada->fields['id'] ?>">
            
            <fieldset style="display: none;">
                <label for="tipo_archivo">Tipo de archivo:</label>
                <select name="tipo_archivo" id="tipo_archivo" required>
                    <option value="foto">Foto de la mascota</option>
                    <option value="certificado">Certificado Médico / General</option>
                    <option value="comprobante">Comprobante vinculado a un Registro</option>
                </select>
            </fieldset>

            <fieldset id="fieldset_registro_id" style="display: none;">
                <label for="registro_id">Registro asociado (Pendiente):</label>
                <select name="registro_id" id="registro_id">
                    <option value="">Seleccione un registro...</option>
                    <?php foreach ($registros as $reg): ?>
                        <?php if (strtolower($reg->fields['estado']) === 'pendiente'): ?>
                            <option value="<?= $reg->fields['id'] ?>"><?= htmlspecialchars($reg->fields['titulo']) ?> - <?= htmlspecialchars(date('d/m/Y', strtotime($reg->fields['fecha_programada']))) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </fieldset>

            <fieldset>
                <label for="archivo">Archivo (PDF o Imagen):</label>
                <input type="file" name="archivo" id="archivo" accept="image/*,.pdf" required>
            </fieldset>
            <footer class="form-actions">
                <button type="submit" class="btn-accion">Subir Archivo</button>
                <button type="button" class="btn-cerrar" onclick="document.getElementById('modal-archivo').close()">Cancelar</button>
            </footer>
        </form>
    </dialog>

    <!-- Modal Encuesta Adaptación -->
    <dialog id="modal-encuesta">
        <header>
            <h3 id="modal-encuesta-titulo">Encuesta de Seguimiento</h3>
        </header>
        <form action="/encuesta/guardar" method="POST">
            <input type="hidden" name="mascota_id" value="<?= $mascotaSeleccionada->fields['id'] ?>">
            <input type="hidden" name="etapa" id="input_etapa" value="">
            
            <fieldset class="encuesta-fields" id="fields_3_dias" style="display:none;">
                <label for="sueno">Hábitos de sueño:</label>
                <select name="sueno" id="sueno">
                    <option value="Normal">Normal (Duerme toda la noche)</option>
                    <option value="Intermitente">Intermitente (Se despierta mucho)</option>
                    <option value="Llora">Llora por las noches</option>
                </select>
                <label for="alimentacion">Alimentación:</label>
                <select name="alimentacion" id="alimentacion">
                    <option value="Come bien">Come bien su ración</option>
                    <option value="Ansioso">Come demasiado rápido/ansioso</option>
                    <option value="Falta de apetito">Falta de apetito</option>
                </select>
            </fieldset>

            <fieldset class="encuesta-fields" id="fields_7_dias" style="display:none;">
                <label for="conducta">Conducta general:</label>
                <select name="conducta" id="conducta">
                    <option value="Excelente">Excelente (Juguetón, amigable)</option>
                    <option value="Buena">Buena (Tranquilo)</option>
                    <option value="Regular">Regular (Tímido, asustadizo)</option>
                    <option value="Problemática">Problemática (Agresividad, rompe cosas)</option>
                </select>
            </fieldset>

            <fieldset class="encuesta-fields" id="fields_14_dias" style="display:none;">
                <label for="progreso_general">Progreso General (Cómo se ha adaptado al hogar):</label>
                <textarea name="progreso_general" id="progreso_general" rows="4" placeholder="Describe brevemente su evolución..."></textarea>
            </fieldset>

            <fieldset>
                <label for="comentarios">Comentarios adicionales:</label>
                <textarea name="comentarios" id="comentarios" rows="2"></textarea>
            </fieldset>

            <footer class="form-actions">
                <button type="submit" class="btn-accion">Enviar Encuesta</button>
                <button type="button" class="btn-cerrar" onclick="document.getElementById('modal-encuesta').close()">Cancelar</button>
            </footer>
        </form>
    </dialog>
    <?php endif; ?>

    <script>
        function abrirModalSubida(tipo) {
            const selectTipo = document.getElementById('tipo_archivo');
            selectTipo.value = tipo;

            const fieldsetRegistro = document.getElementById('fieldset_registro_id');
            const selectRegistro = document.getElementById('registro_id');
            const opciones = selectRegistro.querySelectorAll('option:not([value=""])');

            if (tipo === 'foto') {
                fieldsetRegistro.style.display = 'none';
                selectRegistro.required = false;
                selectRegistro.value = '';
            } else {
                fieldsetRegistro.style.display = 'block';
                selectRegistro.required = true;
                selectRegistro.value = '';

                opciones.forEach(opt => {
                    const texto = opt.innerText.toLowerCase();
                    if (tipo === 'certificado') {
                        opt.style.display = texto.includes('vacuna') ? 'block' : 'none';
                    } else if (tipo === 'comprobante') {
                        opt.style.display = !texto.includes('vacuna') ? 'block' : 'none';
                    }
                });
            }

            document.getElementById('modal-archivo').showModal();
        }
        
        function abrirModalEncuesta(etapa, titulo) {
            document.getElementById('modal-encuesta-titulo').innerText = 'Encuesta: ' + titulo;
            document.getElementById('input_etapa').value = etapa;
            
            // Ocultar todos y deshabilitar require
            document.querySelectorAll('.encuesta-fields').forEach(f => {
                f.style.display = 'none';
                f.querySelectorAll('select, textarea').forEach(input => input.removeAttribute('required'));
            });
            
            // Mostrar el activo y hacer require
            const activeFieldset = document.getElementById('fields_' + etapa);
            if (activeFieldset) {
                activeFieldset.style.display = 'block';
                activeFieldset.querySelectorAll('select, textarea').forEach(input => input.setAttribute('required', 'required'));
            }
            
            document.getElementById('modal-encuesta').showModal();
        }

    </script>
    <?php require __DIR__ . '/footer.view.php'; ?>
</body>
</html>
