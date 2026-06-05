class PAWPerfil {
    // Encapsula la lógica interactiva del perfil de usuario, incluyendo scroll spy, alertas y previsualización de imágenes.
    constructor(contenedor) {
        this.contenedor = contenedor;
    }

    render() {
        if (!this.contenedor) {
            console.warn("PAWPerfil: contenedor inválido.");
            return;
        }

        this.initAlertas();
        this.initScrollSpy();
        this.initEdicionInteractiva();
    }

    // Emplea la API nativa de History (replaceState) para limpiar los parámetros GET de la URL sin recargar la página.
    // Utiliza setTimeout para orquestar la animación de desvanecimiento CSS (fade-out) y posterior ocultamiento (hidden) de las alertas.
    initAlertas() {
        const alerta = document.querySelector('.alerta-exito, .alerta-error');
        if (alerta) {
            if (window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            setTimeout(() => {
                alerta.classList.add('fade-out');
                setTimeout(() => {
                    alerta.classList.add('hidden');
                }, 500);
            }, 4000);
        }
    }

    // Utiliza la API nativa IntersectionObserver para detectar qué sección del perfil está visible actualmente (Scroll Spy).
    // Asigna la clase 'active' dinámicamente a los enlaces de navegación según el elemento intersectado.
    initScrollSpy() {
        const secciones = document.querySelectorAll('.perfil-seccion');
        const enlaces = document.querySelectorAll('.perfil-nav a');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    enlaces.forEach(a => a.classList.remove('active'));
                    const link = document.querySelector(`.perfil-nav a[href="#${entry.target.id}"]`);
                    if (link) link.classList.add('active');
                }
            });
        }, { rootMargin: '-40% 0px -55% 0px' });

        secciones.forEach(s => observer.observe(s));

        // Asigna event listeners de click empleando scrollIntoView nativo con interpolación suave (behavior: smooth).
        enlaces.forEach(a => {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    // Inicializa referencias al DOM y delega la suscripción de eventos para el modo edición.
    initEdicionInteractiva() {
        this.form = document.getElementById('perfil-form');
        this.editBtn = document.getElementById('btn-edit-perfil');
        this.cancelBtn = document.getElementById('btn-cancel-perfil');
        this.fileInput = document.getElementById('foto_perfil_o_logo');
        this.imgPreview = document.getElementById('image-preview');
        this.placeholder = document.getElementById('preview-placeholder');
        this.eliminarFotoInput = document.getElementById('eliminar_foto');
        
        this.avatarModal = document.getElementById('avatar-modal');
        this.modalBackdrop = document.getElementById('avatar-modal-backdrop');
        this.modalUploadBtn = document.getElementById('modal-upload-btn');
        this.modalDeleteBtn = document.getElementById('modal-delete-btn');
        this.modalCancelBtn = document.getElementById('modal-cancel-btn');
        this.avatarCircle = document.querySelector('.perfil-avatar-wrapper');

        this.registrarEventosEdicion();
    }

    // Verifica la existencia y el estado de visibilidad de la previsualización de imagen usando classList.
    tieneFotoActiva() {
        return this.imgPreview && this.imgPreview.getAttribute('src') && !this.imgPreview.classList.contains('hidden');
    }

    abrirModalAvatar(e) {
        if (e) e.preventDefault();
        if (!this.contenedor.classList.contains('is-editing')) return;
        if (this.avatarModal) {
            this.avatarModal.classList.remove('hidden');
        }

        if (this.tieneFotoActiva()) {
            if (this.modalDeleteBtn) this.modalDeleteBtn.classList.remove('hidden');
        } else {
            if (this.modalDeleteBtn) this.modalDeleteBtn.classList.add('hidden');
        }
    }

    cerrarModalAvatar() {
        if (this.avatarModal) {
            this.avatarModal.classList.add('hidden');
        }
    }

    // Centraliza la definición de los manejadores de eventos.
    registrarEventosEdicion() {
        // Modo Edición: Activa estados CSS
        if (this.editBtn && this.contenedor) {
            this.editBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.contenedor.classList.add('is-editing');
            });
        }

        // Cancelar Edición: Restaura los campos y previsualizaciones manipulando atributos (src, data-original) y clases.
        if (this.cancelBtn && this.contenedor && this.form) {
            this.cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.contenedor.classList.remove('is-editing');
                this.form.reset();
                if (this.eliminarFotoInput) this.eliminarFotoInput.value = '0';

                // Restaurar los valores verdaderos (DB) en caso de que PHP los haya llenado con $oldData
                const inputs = this.form.querySelectorAll('.dato-valor-input');
                inputs.forEach(input => {
                    if (input.hasAttribute('data-original')) {
                        input.value = input.getAttribute('data-original');
                    }
                });

                // Limpiar mensajes de error de validación
                const mensajesError = this.form.querySelectorAll('.msg-error');
                mensajesError.forEach(msg => msg.remove());
                const inputsConError = this.form.querySelectorAll('.error, .input-invalido');
                inputsConError.forEach(input => {
                    input.classList.remove('error');
                    input.classList.remove('input-invalido');
                });

                if (this.imgPreview) {
                    const original = this.imgPreview.getAttribute('data-original');
                    if (original) {
                        this.imgPreview.setAttribute('src', original);
                        this.imgPreview.classList.remove('hidden');
                        if (this.placeholder) this.placeholder.classList.add('hidden');
                    } else {
                        this.imgPreview.setAttribute('src', '');
                        this.imgPreview.classList.add('hidden');
                        if (this.placeholder) this.placeholder.classList.remove('hidden');
                    }
                }
            });
        }

        // Intercepta el evento de selección de archivos.
        // Utiliza FileReader.readAsDataURL() para convertir el Blob binario en una cadena Base64 y asignarla nativamente al atributo 'src'.
        if (this.fileInput) {
            this.fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.addEventListener('load', (ev) => {
                        if (this.imgPreview) {
                            this.imgPreview.setAttribute('src', ev.target.result);
                            this.imgPreview.classList.remove('hidden');
                        }
                        if (this.placeholder) {
                            this.placeholder.classList.add('hidden');
                        }
                        if (this.eliminarFotoInput) this.eliminarFotoInput.value = '0';
                    });
                    reader.readAsDataURL(file);
                }
            });
        }

        if (this.avatarCircle) {
            this.avatarCircle.addEventListener('click', (e) => this.abrirModalAvatar(e));
        }

        if (this.imgPreview) {
            this.imgPreview.addEventListener('error', () => {
                this.imgPreview.classList.add('hidden');
                if (this.placeholder) {
                    this.placeholder.classList.remove('hidden');
                }
            });
        }

        if (this.modalCancelBtn) this.modalCancelBtn.addEventListener('click', () => this.cerrarModalAvatar());
        if (this.modalBackdrop) this.modalBackdrop.addEventListener('click', () => this.cerrarModalAvatar());

        if (this.modalUploadBtn && this.fileInput) {
            this.modalUploadBtn.addEventListener('click', () => {
                this.cerrarModalAvatar();
                this.fileInput.click();
            });
        }

        if (this.modalDeleteBtn) {
            this.modalDeleteBtn.addEventListener('click', () => {
                this.cerrarModalAvatar();
                if (this.eliminarFotoInput) this.eliminarFotoInput.value = '1';
                if (this.fileInput) this.fileInput.value = '';
                if (this.imgPreview) {
                    this.imgPreview.setAttribute('src', '');
                    this.imgPreview.classList.add('hidden');
                }
                if (this.placeholder) {
                    this.placeholder.classList.remove('hidden');
                }
            });
        }
    }
}
