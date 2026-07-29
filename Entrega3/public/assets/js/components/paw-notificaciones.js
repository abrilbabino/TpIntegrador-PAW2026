class PAWNotificaciones {
    constructor() {
        this.btnCampanita = document.getElementById('btn-campanita');
        this.badge = document.getElementById('badge-notificaciones');
        this.dropdown = document.getElementById('dropdown-notificaciones');
        this.lista = document.getElementById('lista-notificaciones');
        
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
        if (this.noLeidasCount > 0) {
            this.badge.textContent = this.noLeidasCount;
            this.badge.classList.remove('oculto');
        } else {
            this.badge.classList.add('oculto');
        }

        // Renderizar lista
        this.lista.innerHTML = '';
        if (this.notificaciones.length === 0) {
            this.lista.innerHTML = '<li class="noti-vacia">No tienes notificaciones</li>';
            return;
        }

        this.notificaciones.forEach(notif => {
            const li = document.createElement('li');
            li.className = `noti-item ${!notif.leida ? 'noti-no-leida' : ''}`;
            
            const link = document.createElement('a');
            link.href = notif.enlace || '#';
            link.className = 'noti-link';
            
            const content = `
                <div class="noti-content">
                    <strong>${notif.titulo}</strong>
                    <p>${notif.mensaje}</p>
                    <small>${this.formatearFecha(notif.fecha_creacion)}</small>
                </div>
            `;
            link.innerHTML = content;
            li.appendChild(link);
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
        if (this.noLeidasCount > 0) {
            this.marcarComoLeidas();
        }
    }

    cerrarDropdown() {
        this.dropdown.classList.add('oculto');
        this.dropdownAbierto = false;
    }

    async marcarComoLeidas() {
        const noLeidasIds = this.notificaciones
            .filter(n => !n.leida && n.id)
            .map(n => n.id);

        // Actualizamos localmente primero
        this.noLeidasCount = 0;
        this.notificaciones.forEach(n => n.leida = true);
        this.actualizarUI();

        if (noLeidasIds.length === 0) return;

        try {
            await fetch('/api/notificaciones/leer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: noLeidasIds })
            });
        } catch (e) {
            console.error('Error marcando como leídas', e);
        }
    }

    formatearFecha(fechaStr) {
        if (!fechaStr) return 'Justo ahora';
        const d = new Date(fechaStr);
        if (isNaN(d)) return 'Justo ahora';
        return d.toLocaleDateString('es-AR') + ' ' + d.toLocaleTimeString('es-AR', {hour: '2-digit', minute:'2-digit'});
    }
}
