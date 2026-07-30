class PAWLibreta {
    constructor() {
        this.init();
    }

    init() {
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

        // Limpiar el formulario y los errores al cerrar cualquier modal
        document.querySelectorAll("dialog").forEach(modal => {
            modal.addEventListener("close", () => {
                const form = modal.querySelector("form");
                if (form) {
                    form.reset(); // Restaura los valores originales
                    form.dataset.submitted = "false";
                    const btnSubmit = form.querySelector("button[type='submit']");
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.textContent = btnSubmit.classList.contains('btn-guardar-registro') ? 
                            (form.action.includes('completar') ? 'Completar y Subir' : 'Guardar Registro') : 'Guardar';
                    }

                    // Limpiar clases, data-attributes y mensajes de error inyectados
                    form.querySelectorAll("input, select, textarea").forEach(input => {
                        input.classList.remove("input-invalido");
                        delete input.dataset.serverError;
                        input.setCustomValidity("");

                        const siguiente = input.nextElementSibling;
                        if (siguiente && siguiente.classList.contains("msg-error")) {
                            siguiente.remove();
                        }
                    });
                }
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

        // Prevenir el doble envío de formularios
        document.querySelectorAll("form.form-registro").forEach(form => {
            form.addEventListener("submit", (e) => {
                if (form.dataset.submitted === "true") {
                    e.preventDefault();
                    return;
                }
                const btnSubmit = form.querySelector("button[type='submit']");
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.textContent = "Procesando...";
                }
                form.dataset.submitted = "true";
            });
        });
    }
}
