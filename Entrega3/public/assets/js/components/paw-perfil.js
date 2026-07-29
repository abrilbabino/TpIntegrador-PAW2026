class PAWPerfil {
    // Encapsula la lógica interactiva del perfil de usuario, incluyendo scroll spy, alertas y previsualización de imágenes.
    constructor(contenedor, config = {}) {
        this.contenedor = contenedor;
        this.formId = config.formId || 'perfil-form';
        this.editBtnId = config.editBtnId || 'btn-edit-perfil';
        this.cancelBtnId = config.cancelBtnId || 'btn-cancel-perfil';
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
        this.form = document.getElementById(this.formId);
        this.editBtn = document.getElementById(this.editBtnId);
        this.cancelBtn = document.getElementById(this.cancelBtnId);
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
        if (this.wasDragging) return;
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

                if (this.avatarCircle) this.avatarCircle.classList.remove('is-cropping');
                if (this.dragAbortController) this.dragAbortController.abort();

                if (this.imgPreview) {
                    this.imgPreview.style.left = '';
                    this.imgPreview.style.top = '';
                    this.imgPreview.style.width = '';
                    this.imgPreview.style.height = '';
                    this.imgPreview.style.cursor = '';
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
                            this.iniciarRecorte(ev.target.result, file);
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
                if (this.avatarCircle) this.avatarCircle.classList.remove('is-cropping');
                if (this.dragAbortController) this.dragAbortController.abort();
                if (this.imgPreview) {
                    this.imgPreview.style.left = '';
                    this.imgPreview.style.top = '';
                    this.imgPreview.style.width = '';
                    this.imgPreview.style.height = '';
                    this.imgPreview.style.cursor = '';
                    this.imgPreview.setAttribute('src', '');
                    this.imgPreview.classList.add('hidden');
                }
                if (this.placeholder) {
                    this.placeholder.classList.remove('hidden');
                }
            });
        }
    }

    iniciarRecorte(dataUrl, file) {
        if (!this.avatarCircle) return;
        this.avatarCircle.classList.add('is-cropping');
        
        // Reiniciar estilos en línea previos
        this.imgPreview.style.left = '0px';
        this.imgPreview.style.top = '0px';
        this.imgPreview.style.width = 'auto';
        this.imgPreview.style.height = 'auto';
        this.imgPreview.style.transform = 'none';
        
        const img = new Image();
        img.onload = () => {
            const containerSize = this.avatarCircle.offsetWidth || 100;
            const scale = Math.max(containerSize / img.width, containerSize / img.height);
            const scaledWidth = img.width * scale;
            const scaledHeight = img.height * scale;
            
            this.imgPreview.style.width = scaledWidth + 'px';
            this.imgPreview.style.height = scaledHeight + 'px';
            
            // Centrar por defecto
            let currentLeft = (containerSize - scaledWidth) / 2;
            let currentTop = (containerSize - scaledHeight) / 2;
            
            this.imgPreview.style.left = currentLeft + 'px';
            this.imgPreview.style.top = currentTop + 'px';
            this.imgPreview.style.cursor = 'grab';
            
            let isDragging = false;
            let hasMoved = false;
            let startX, startY, startLeft, startTop;
            
            const startDrag = (e) => {
                if (!this.avatarCircle.classList.contains('is-cropping')) return;
                e.preventDefault();
                isDragging = true;
                hasMoved = false;
                this.avatarCircle.classList.add('is-dragging');
                this.imgPreview.style.cursor = 'grabbing';
                startX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
                startY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;
                startLeft = currentLeft;
                startTop = currentTop;
            };
            
            const doDrag = (e) => {
                if (!isDragging) return;
                e.preventDefault();
                hasMoved = true;
                const clientX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
                const clientY = e.type.includes('mouse') ? e.clientY : e.touches[0].clientY;
                
                let newLeft = startLeft + (clientX - startX);
                let newTop = startTop + (clientY - startY);
                
                // Restringir arrastre para no salir del contenedor
                if (newLeft > 0) newLeft = 0;
                if (newTop > 0) newTop = 0;
                if (newLeft < containerSize - scaledWidth) newLeft = containerSize - scaledWidth;
                if (newTop < containerSize - scaledHeight) newTop = containerSize - scaledHeight;
                
                currentLeft = newLeft;
                currentTop = newTop;
                this.imgPreview.style.left = newLeft + 'px';
                this.imgPreview.style.top = newTop + 'px';
            };
            
            const endDrag = (e) => {
                if (!isDragging) return;
                isDragging = false;
                
                if (hasMoved) {
                    this.wasDragging = true;
                    setTimeout(() => { this.wasDragging = false; }, 100);
                }
                
                this.avatarCircle.classList.remove('is-dragging');
                this.imgPreview.style.cursor = 'grab';
                this.aplicarRecorte(img, currentLeft, currentTop, scaledWidth, scale, containerSize, file);
            };
            
            // Eliminar listeners anteriores si los hay
            if (this.dragAbortController) this.dragAbortController.abort();
            this.dragAbortController = new AbortController();
            const signal = this.dragAbortController.signal;
            
            this.imgPreview.addEventListener('mousedown', startDrag, {signal});
            this.imgPreview.addEventListener('touchstart', startDrag, {signal, passive: false});
            
            document.addEventListener('mousemove', doDrag, {signal});
            document.addEventListener('touchmove', doDrag, {signal, passive: false});
            
            document.addEventListener('mouseup', endDrag, {signal});
            document.addEventListener('touchend', endDrag, {signal});
            
            // Aplicar primer recorte automático centrado
            this.aplicarRecorte(img, currentLeft, currentTop, scaledWidth, scale, containerSize, file);
        };
        img.src = dataUrl;
    }

    aplicarRecorte(originalImg, currentLeft, currentTop, scaledWidth, scale, containerSize, file) {
        const canvas = document.createElement('canvas');
        canvas.width = containerSize;
        canvas.height = containerSize;
        const ctx = canvas.getContext('2d');
        
        const realScale = originalImg.width / scaledWidth;
        const sourceX = Math.abs(currentLeft) * realScale;
        const sourceY = Math.abs(currentTop) * realScale;
        const sourceSize = containerSize * realScale;
        
        ctx.drawImage(originalImg, sourceX, sourceY, sourceSize, sourceSize, 0, 0, containerSize, containerSize);
        
        canvas.toBlob((blob) => {
            if (blob && this.fileInput) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(new File([blob], file.name, { type: file.type }));
                this.fileInput.files = dataTransfer.files;
            }
        }, file.type, 0.9);
    }
}
