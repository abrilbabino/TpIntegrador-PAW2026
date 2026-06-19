class PAWLibreta {
    constructor() {
        this.init();
    }

    init() {
        const formularios = document.querySelectorAll(".form-registro");
        if (typeof PAWValidador !== 'undefined') {
            formularios.forEach(form => new PAWValidador(form));
        }

        const btnAgregar = document.getElementById("btn-abrir-agregar-registro");
        if (btnAgregar) {
            btnAgregar.addEventListener("click", () => {
                const modal = document.getElementById("modal-agregar-registro");
                if (modal) modal.showModal();
            });
        }

        document.querySelectorAll(".js-cerrar-modal").forEach(btn => {
            btn.addEventListener("click", function() {
                const modalId = this.getAttribute("data-modal");
                const modal = document.getElementById(modalId);
                if (modal) modal.close();
            });
        });

        // Leer datos inyectados en el DOM desde PHP
        const mainContainer = document.querySelector(".libreta-main");
        if (mainContainer && mainContainer.dataset.errorCarga === 'true' && mainContainer.dataset.errorRegistroId) {
            const idInput = document.getElementById('completar_registro_id');
            const modal = document.getElementById('modal-completar-registro');
            if (idInput && modal) {
                idInput.value = mainContainer.dataset.errorRegistroId;
                modal.showModal();
            }
        }
    }
}
