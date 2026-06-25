class PAWRefugioPerfil {
    constructor(contenedor) {
        this.contenedor = contenedor;
    }

    render() {
        if (!this.contenedor) {
            console.warn("PAWRefugioPerfil: contenedor inválido.");
            return;
        }

        this.initScrollSpy();
        this.initSolicitudesRefugio();
        this.initVerMasDescripcion();
        this.initAutocompleteUbicacion();
        this.initEliminarMascota();
        this.initPublicarMascota();
    }

    initAutocompleteUbicacion() {
        const inputUbi = document.getElementById("ubicacion-autocomplete");
        const ulSugerencias = document.getElementById("sugerencias-ubicacion");
        const inLat = document.getElementById("ubi_lat");
        const inLon = document.getElementById("ubi_lon");
        const inCiudad = document.getElementById("ubi_ciudad");
        const inProv = document.getElementById("ubi_provincia");
        const inPais = document.getElementById("ubi_pais");
        const inDireccion = document.getElementById("ubi_direccion");
        const btnGuardar = document.getElementById("btn-guardar-ubicacion");
        
        if (!inputUbi || !ulSugerencias) return;

        let timeout = null;

        inputUbi.addEventListener("input", () => {
            clearTimeout(timeout);
            const val = inputUbi.value.trim();
            if (btnGuardar) btnGuardar.disabled = true; // reset
            if (val.length < 3) {
                ulSugerencias.style.display = "none";
                return;
            }
            timeout = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(val)}&addressdetails=1`)
                    .then(res => res.json())
                    .then(data => {
                        ulSugerencias.innerHTML = "";
                        if (data.length === 0) {
                            ulSugerencias.style.display = "none";
                            return;
                        }
                        data.forEach(item => {
                            const li = document.createElement("li");
                            li.textContent = item.display_name;
                            
                            li.addEventListener("click", () => {
                                inputUbi.value = item.display_name;
                                ulSugerencias.style.display = "none";
                                
                                if (inLat) inLat.value = item.lat;
                                if (inLon) inLon.value = item.lon;
                                const addr = item.address || {};
                                if (inCiudad) inCiudad.value = addr.city || addr.town || addr.village || addr.municipality || "";
                                if (inProv) inProv.value = addr.state || addr.region || "";
                                if (inPais) inPais.value = addr.country || "";
                                if (inDireccion) inDireccion.value = item.display_name;
                                if (btnGuardar) btnGuardar.disabled = false;
                            });
                            
                            ulSugerencias.appendChild(li);
                        });
                        ulSugerencias.style.display = "block";
                    })
                    .catch(err => console.error(err));
            }, 500);
        });

        document.addEventListener("click", (e) => {
            if (!inputUbi.contains(e.target) && !ulSugerencias.contains(e.target)) {
                ulSugerencias.style.display = "none";
            }
        });
    }

    initVerMasDescripcion() {
        const texto = document.querySelector('.descripcion-texto');
        const btnVerMas = document.querySelector('.btn-ver-mas-desc');

        if (!texto || !btnVerMas) return;

        const checkClamp = () => {
            texto.classList.remove('expanded');
            btnVerMas.textContent = 'Ver más';
            
            // Wait for render layout calculations
            setTimeout(() => {
                if (texto.scrollHeight > texto.clientHeight) {
                    btnVerMas.style.display = 'inline-block';
                } else {
                    btnVerMas.style.display = 'none';
                }
            }, 50);
        };

        checkClamp();

        btnVerMas.addEventListener('click', () => {
            if (texto.classList.contains('expanded')) {
                texto.classList.remove('expanded');
                btnVerMas.textContent = 'Ver más';
            } else {
                texto.classList.add('expanded');
                btnVerMas.textContent = 'Ver menos';
            }
        });
        
        window.addEventListener('resize', checkClamp);
    }

    initSolicitudesRefugio() {
        const lista = document.querySelector('.perfil-refugio-lista-adopcion');
        if (!lista) return;

        lista.addEventListener('click', (e) => {
            const target = e.target;
            const isAceptar = target.classList.contains('btn-aceptar');
            const isRechazar = target.classList.contains('btn-rechazar');

            if (!isAceptar && !isRechazar) return;

            e.preventDefault();
            const id = target.getAttribute('data-id');
            const accion = isAceptar ? 'aceptar' : 'rechazar';

            if (!id) return;

            const confirmMessage = `¿Estás seguro de que querés ${isAceptar ? 'aceptar' : 'rechazar'} esta solicitud de adopción?`;

            PAW.cargarScript('paw-modal-confirm', '/assets/js/components/paw-modal-confirm.js', () => {
                const modal = new PAWModalConfirm(
                    isAceptar ? 'Aceptar solicitud' : 'Rechazar solicitud',
                    confirmMessage,
                    isAceptar ? 'Aceptar' : 'Rechazar',
                    isAceptar ? 'bold-btn' : 'red-btn'
                );

                modal.mostrar().then((confirmado) => {
                    if (!confirmado) return;

                    const li = target.closest('.perfil-lista-item-adopcion');
                    const btnAceptar = li.querySelector('.btn-aceptar');
                    const btnRechazar = li.querySelector('.btn-rechazar');

                    if (btnAceptar) btnAceptar.disabled = true;
                    if (btnRechazar) btnRechazar.disabled = true;

                    fetch('/api/solicitud/actualizar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id: parseInt(id), accion: accion })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const spanEstado = li.querySelector('.estado-solicitud');
                            if (spanEstado) {
                                spanEstado.className = 'estado-solicitud';
                                const nuevoEstadoClass = 'estado-' + data.estado.toLowerCase();
                                spanEstado.classList.add(nuevoEstadoClass);

                                const match = spanEstado.innerHTML.match(/Fecha:\s*(.*)/i);
                                const fechaStr = match ? match[1] : '';
                                spanEstado.innerHTML = `Estado: ${data.estado} <br> Fecha: ${fechaStr}`;
                            }
                            if (btnAceptar) btnAceptar.remove();
                            if (btnRechazar) btnRechazar.remove();
                        } else {
                            alert(data.mensaje || 'Error al procesar la solicitud.');
                            if (btnAceptar) btnAceptar.disabled = false;
                            if (btnRechazar) btnRechazar.disabled = false;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Ocurrió un error en la conexión con el servidor.');
                        if (btnAceptar) btnAceptar.disabled = false;
                        if (btnRechazar) btnRechazar.disabled = false;
                    });
                });
            });
        });
    }


    initScrollSpy() {
        const enlaces = document.querySelectorAll('.perfil-refugio-nav a');
        const secciones = Array.from(enlaces)
            .map(enlace => document.querySelector(enlace.getAttribute('href')))
            .filter(Boolean);

        if (enlaces.length === 0 || secciones.length === 0) {
            return;
        }

        enlaces.forEach(enlace => enlace.classList.remove('active'));

        const setActiveLink = (targetId) => {
            enlaces.forEach(enlace => enlace.classList.toggle('active', enlace.getAttribute('href') === `#${targetId}`));
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    enlaces.forEach(a => a.classList.remove('active'));
                    const link = document.querySelector(`.perfil-refugio-nav a[href="#${entry.target.id}"]`);
                    if (link) link.classList.add('active');
                }
            });
        }, { rootMargin: '-40% 0px -55% 0px' });

        secciones.forEach(s => observer.observe(s));

        enlaces.forEach(a => {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (!target) return;
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                enlaces.forEach(enlace => enlace.classList.remove('active'));
                this.classList.add('active');
                history.replaceState(null, '', this.getAttribute('href'));
            });
        });

        const initialHash = window.location.hash;
        if (initialHash) {
            const initialLink = document.querySelector(`.perfil-refugio-nav a[href="${initialHash}"]`);
            if (initialLink) {
                initialLink.classList.add('active');
                const target = document.querySelector(initialHash);
                if (target) target.scrollIntoView({ block: 'start' });
            }
        } else {
            if (enlaces[0]) enlaces[0].classList.add('active');
        }
    }

    initEliminarMascota() {
        const modal = document.getElementById('modal-confirmar-eliminar');
        const btnCancelar = document.getElementById('btn-cancelar-eliminar');
        const btnConfirmar = document.getElementById('btn-confirmar-eliminar');
        let formToSubmit = null;

        if (!modal || !btnCancelar || !btnConfirmar) return;

        const formsEliminar = document.querySelectorAll('.form-eliminar-mascota');
        formsEliminar.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                formToSubmit = form;
                modal.showModal();
            });
        });

        btnCancelar.addEventListener('click', () => {
            modal.close();
            formToSubmit = null;
        });

        btnConfirmar.addEventListener('click', () => {
            if (formToSubmit) {
                formToSubmit.submit();
            }
        });
    }
    initPublicarMascota() {
        const form = document.getElementById('form-publicar-mascota');
        const btnCancelar = document.getElementById('btn-cancelar-publicar');
        const details = form ? form.closest('details') : null;

        // Botón Cancelar: resetea el formulario y cierra el accordion
        if (btnCancelar && form) {
            btnCancelar.addEventListener('click', () => {
                form.reset();
                if (details) details.removeAttribute('open');
            });
        }

        // Si venimos de un guardado exitoso (?publicado=1), cerrar el accordion
        // El form ya viene vacío porque el controlador redirige sin oldMascota
        const params = new URLSearchParams(window.location.search);
        if (params.get('publicado') === '1' && details) {
            details.removeAttribute('open');
            // Limpiar el parámetro de la URL sin recargar
            const url = new URL(window.location.href);
            url.searchParams.delete('publicado');
            history.replaceState(null, '', url.toString());
        }
    }
}
