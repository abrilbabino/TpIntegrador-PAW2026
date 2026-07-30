class PAWResenas {
    constructor() {
        this.form = document.querySelector('[data-paw-resena-form]');
        this.modal = document.getElementById('modal-resena');
        this.btnAbrir = document.getElementById('btn-abrir-modal-resena');
        this.btnCerrar = document.getElementById('btn-cerrar-modal-resena');
        
        if (this.form || this.modal) {
            this.init();
        }
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
        }
    }
}

window.PAWResenas = PAWResenas;
