function abrirModalSubida(tipo) {
    const selectTipo = document.getElementById('tipo_archivo');
    selectTipo.value = tipo;

    const fieldsetRegistro = document.getElementById('fieldset_registro_id');
    const selectRegistro = document.getElementById('registro_id');
    const opciones = selectRegistro.querySelectorAll('option:not([value=""])');

    if (tipo === 'foto') {
        fieldsetRegistro.classList.add('seguimiento-oculto');
        selectRegistro.required = false;
        selectRegistro.value = '';
    } else {
        fieldsetRegistro.classList.remove('seguimiento-oculto');
        selectRegistro.required = true;
        selectRegistro.value = '';

        opciones.forEach(opt => {
            const texto = opt.innerText.toLowerCase();
            if (tipo === 'certificado') {
                opt.style.display = texto.includes('vacuna') ? 'block' : 'none';
            } else if (tipo === 'comprobante') {
                opt.style.display = !texto.includes('vacuna') ? 'block' : 'none';
            }
        });
    }

    document.getElementById('modal-archivo').showModal();
}

function abrirModalEncuesta(etapa, titulo) {
    document.getElementById('modal-encuesta-titulo').innerText = 'Encuesta: ' + titulo;
    document.getElementById('input_etapa').value = etapa;
    
    document.querySelectorAll('.encuesta-fields').forEach(f => {
        f.classList.add('seguimiento-oculto');
        f.querySelectorAll('select, textarea').forEach(input => input.removeAttribute('required'));
    });
    
    const activeFieldset = document.getElementById('fields_' + etapa);
    if (activeFieldset) {
        activeFieldset.classList.remove('seguimiento-oculto');
        activeFieldset.querySelectorAll('select, textarea').forEach(input => input.setAttribute('required', 'required'));
    }
    
    document.getElementById('modal-encuesta').showModal();
}

window.abrirModalSubida = abrirModalSubida;
window.abrirModalEncuesta = abrirModalEncuesta;

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("dialog").forEach(modal => {
        modal.addEventListener("close", () => {
            const form = modal.querySelector("form");
            if (form) {
                form.reset();
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

    const mainContainer = document.querySelector(".seguimiento-main");
    if (mainContainer && mainContainer.dataset.errorUpload === 'true') {
        const modal = document.getElementById('modal-archivo');
        if (modal) {
            modal.showModal();
        }
    }
});
