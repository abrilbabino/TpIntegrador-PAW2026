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
        this.initEdicionInteractiva();
        this.initSolicitudesRefugio();
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

    initEdicionInteractiva() {
        this.form = document.getElementById('perfil-refugio-form');
        this.editBtn = document.getElementById('btn-edit-refugio');
        this.cancelBtn = document.getElementById('btn-cancel-refugio');

        if (this.editBtn && this.contenedor) {
            this.editBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.contenedor.classList.add('is-editing');
            });
        }

        if (this.cancelBtn && this.contenedor && this.form) {
            this.cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.contenedor.classList.remove('is-editing');
                this.form.reset();

                const inputs = this.form.querySelectorAll('.dato-valor-input');
                inputs.forEach(input => {
                    if (input.hasAttribute('data-original')) {
                        input.value = input.getAttribute('data-original');
                    }
                });

                const mensajesError = this.form.querySelectorAll('.msg-error');
                mensajesError.forEach(msg => msg.remove());
                const inputsConError = this.form.querySelectorAll('.error, .input-invalido');
                inputsConError.forEach(input => {
                    input.classList.remove('error');
                    input.classList.remove('input-invalido');
                });
            });
        }
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
}
