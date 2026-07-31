class PAWNotificaciones {
    constructor() {
        this.btnCampanita = document.getElementById('btn-campanita');
        this.badge = document.getElementById('badge-notificaciones');
        this.dropdown = document.getElementById('dropdown-notificaciones');
        this.lista = document.getElementById('lista-notificaciones');
        this.btnMarcarTodas = document.getElementById('btn-marcar-todas-leidas');

        if (!this.btnCampanita) return;

        this.usuarioId = this.btnCampanita.getAttribute('data-usuario-id');
        this.noLeidasCount = 0;
        this.notificaciones = [];
        this.socket = null;
        this.dropdownAbierto = false;

        this.init();
    }

    async init() {
        // 1. Cargar notificaciones iniciales via AJAX
        await this.cargarNotificacionesIniciales();

        // 2. Conectar WebSocket
        this.conectarWebSocket();

        // 3. Event Listeners
        this.btnCampanita.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });

        if (this.btnMarcarTodas) {
            this.btnMarcarTodas.addEventListener('click', (e) => {
                e.stopPropagation();
                this.marcarComoLeidas(); // Marca todas
            });
        }

        document.addEventListener('click', (e) => {
            if (this.dropdownAbierto && !this.btnCampanita.contains(e.target) && !this.dropdown.contains(e.target)) {
                this.cerrarDropdown();
            }
        });
    }

    async cargarNotificacionesIniciales() {
        try {
            const res = await fetch('/api/notificaciones');
            const data = await res.json();
            if (data.success) {
                this.notificaciones = data.notificaciones;
                this.noLeidasCount = data.no_leidas_count;
                this.actualizarUI();
            }
        } catch (error) {
            console.error('Error cargando notificaciones:', error);
        }
    }

    conectarWebSocket() {
        // En producción sería wss://pawmap.lat/ws, localmente usamos ws://localhost:8081
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const host = window.location.hostname;
        const wsUrl = host === 'localhost' || host === '127.0.0.1'
            ? `ws://${host}:8081`
            : `${protocol}//${host}/ws`;

        this.socket = new WebSocket(wsUrl);

        this.socket.addEventListener('open', () => {
            this.socket.send(JSON.stringify({
                type: 'auth',
                usuario_id: this.usuarioId
            }));
        });

        this.socket.addEventListener('message', (event) => {
            try {
                const notificacion = JSON.parse(event.data);
                if (notificacion.titulo && notificacion.mensaje) {
                    this.agregarNotificacion(notificacion);
                }
            } catch (e) {
                console.error("Error parseando notificacion ws", e);
            }
        });

        this.socket.addEventListener('close', () => {
            // Reconexión simple
            setTimeout(() => this.conectarWebSocket(), 5000);
        });
    }

    agregarNotificacion(notificacion) {
        // Agregar al principio
        notificacion.leida = false;
        this.notificaciones.unshift(notificacion);
        this.noLeidasCount++;
        this.actualizarUI();
    }

    actualizarUI() {
        // Actualizar badge
        this.badge.textContent = this.noLeidasCount;
        if (this.noLeidasCount > 0) {
            this.badge.classList.remove('oculto');
        } else {
            this.badge.classList.add('oculto');
        }

        // Botón marcar todas
        if (this.btnMarcarTodas) {
            if (this.noLeidasCount > 0) {
                this.btnMarcarTodas.classList.remove('oculto');
            } else {
                this.btnMarcarTodas.classList.add('oculto');
            }
        }

        // Renderizar lista
        this.lista.innerHTML = '';
        if (this.notificaciones.length === 0) {
            this.lista.innerHTML = '<li class="noti-vacia">No tienes notificaciones</li>';
            return;
        }

        this.notificaciones.forEach(notif => {
            const noLeida = notif.leida === false || notif.leida === 0 || notif.leida === '0' || notif.leida === 'f';
            const li = document.createElement('li');
            li.className = `noti-item ${noLeida ? 'noti-no-leida' : ''}`;

            const contentElement = document.createElement('a');
            contentElement.className = 'noti-link';

            // Si NO está leída, al hacer click la marcamos como leída individualmente
            if (noLeida && notif.id) {
                contentElement.style.cursor = 'pointer';
                contentElement.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.marcarUnaComoLeida(notif.id);
                });
            }

            const content = `
                <div class="noti-content">
                    <strong>${notif.titulo}</strong>
                    <p>${notif.mensaje}</p>
                    <small>${this.formatearFecha(notif.fecha_creacion)}</small>
                </div>
            `;
            contentElement.innerHTML = content;
            li.appendChild(contentElement);
            this.lista.appendChild(li);
        });
    }

    toggleDropdown() {
        if (this.dropdownAbierto) {
            this.cerrarDropdown();
        } else {
            this.abrirDropdown();
        }
    }

    abrirDropdown() {
        this.dropdown.classList.remove('oculto');
        this.dropdownAbierto = true;
    }

    cerrarDropdown() {
        this.dropdown.classList.add('oculto');
        this.dropdownAbierto = false;
    }

    marcarUnaComoLeida(id) {
        const notif = this.notificaciones.find(n => n.id === id);
        if (!notif) return;
        const noLeida = notif.leida === false || notif.leida === 0 || notif.leida === '0' || notif.leida === 'f';
        if (!noLeida) return;

        notif.leida = true;
        this.noLeidasCount = Math.max(0, this.noLeidasCount - 1);
        this.actualizarUI();

        const csrfTokenInput = document.querySelector('input[name="csrf_token"]');
        const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';

        fetch('/api/notificaciones/leer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: [id], csrf_token: csrfToken }),
            keepalive: true
        }).catch(e => console.error('Error marcando notificación como leída', e));
    }

    marcarComoLeidas() {
        const noLeidasIds = this.notificaciones
            .filter(n => n.leida === false || n.leida === 0 || n.leida === '0' || n.leida === 'f')
            .map(n => n.id);

        // Actualizamos localmente primero (feedback instantáneo)
        this.noLeidasCount = 0;
        this.notificaciones.forEach(n => n.leida = true);
        this.actualizarUI();

        if (noLeidasIds.length === 0) return;

        const csrfTokenInput = document.querySelector('input[name="csrf_token"]');
        const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';

        // Fire-and-forget: no esperamos la respuesta del servidor
        fetch('/api/notificaciones/leer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: noLeidasIds, csrf_token: csrfToken })
        }).catch(e => console.error('Error marcando como leídas', e));
    }

    formatearFecha(fechaStr) {
        if (!fechaStr) return 'Justo ahora';
        const d = new Date(fechaStr);
        if (isNaN(d)) return 'Justo ahora';
        return d.toLocaleDateString('es-AR') + ' ' + d.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
    }
}
