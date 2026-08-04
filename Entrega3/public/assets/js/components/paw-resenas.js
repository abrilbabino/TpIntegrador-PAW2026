class PAWResenas {
    constructor() {
        this.form = document.querySelector('[data-paw-resena-form]');
        this.modal = document.getElementById('modal-resena');
        this.btnAbrir = document.getElementById('btn-abrir-modal-resena');
        this.btnCerrar = document.getElementById('btn-cerrar-modal-resena');
        
        if (this.form || this.modal) {
            this.init();
        }
        
        this.initEditDelete();
    }

    init() {
        if (this.form) {
            const selectMascota = this.form.querySelector('[data-resena-select]');
            const inputRefugio = this.form.querySelector('#refugio_id');

            if (selectMascota && inputRefugio) {
                selectMascota.addEventListener('change', (e) => {
                    const selectedOption = e.target.options[e.target.selectedIndex];
                    const refugioId = selectedOption.dataset.refugio;
                    if (refugioId) {
                        inputRefugio.value = refugioId;
                    }
                });
            }
        }

        if (this.btnAbrir && this.modal) {
            this.btnAbrir.addEventListener('click', () => this.modal.showModal());
        }

        if (this.btnCerrar && this.modal) {
            this.btnCerrar.addEventListener('click', () => this.modal.close());
        }

        if (this.modal) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) this.modal.close();
            });

            const urlParams = new URLSearchParams(window.location.search);
            const errorResena = urlParams.get('error_resena');

            if (errorResena) {
                this.modal.showModal();
                
                urlParams.delete('error_resena');
                const newSearch = urlParams.toString();
                const newUrl = window.location.pathname + (newSearch ? '?' + newSearch : '') + window.location.hash;
                window.history.replaceState({}, document.title, newUrl);
            }
        }
    }

    initEditDelete() {
        const modalEditar = document.getElementById('modal-editar-resena');
        const formEliminar = document.getElementById('form-eliminar-resena');
        
        if (modalEditar) {
            const btnCerrarEditar = document.getElementById('btn-cerrar-modal-editar-resena');
            if (btnCerrarEditar) btnCerrarEditar.addEventListener('click', () => modalEditar.close());
            
            modalEditar.addEventListener('click', (e) => {
                if (e.target === modalEditar) modalEditar.close();
            });
        }

        document.querySelectorAll('.btn-icon[data-action="editar-resena"]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!modalEditar) return;
                
                const id = btn.dataset.id;
                const calificacion = btn.dataset.calificacion;
                const comentario = btn.dataset.comentario;
                
                document.getElementById('editar_resena_id').value = id;
                document.getElementById('editar_comentario').value = comentario;
                
                const radio = document.getElementById('edit_star' + calificacion);
                if (radio) radio.checked = true;
                
                modalEditar.showModal();
            });
        });

        const modalEliminar = document.getElementById('modal-confirmar-eliminar-resena');
        if (modalEliminar) {
            const btnCancelarEliminar = document.getElementById('btn-cancelar-eliminar-resena');
            const btnConfirmarEliminar = document.getElementById('btn-confirmar-eliminar-resena');

            const cerrarModalEliminar = () => modalEliminar.close();

            if (btnCancelarEliminar) btnCancelarEliminar.addEventListener('click', cerrarModalEliminar);
            
            modalEliminar.addEventListener('click', (e) => {
                if (e.target === modalEliminar) cerrarModalEliminar();
            });

            if (btnConfirmarEliminar) {
                btnConfirmarEliminar.addEventListener('click', () => {
                    if (formEliminar) {
                        formEliminar.submit();
                    }
                });
            }
        }

        document.querySelectorAll('.btn-icon[data-action="eliminar-resena"]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (modalEliminar) {
                    document.getElementById('eliminar_resena_id').value = btn.dataset.id;
                    modalEliminar.showModal();
                }
            });
        });
    }
}

window.PAWResenas = PAWResenas;
