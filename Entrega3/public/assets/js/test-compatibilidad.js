document.addEventListener('DOMContentLoaded', function() {
    const steps = document.querySelectorAll('.paso');
    const btnAnterior = document.getElementById('btnAnterior');
    const btnSiguiente = document.getElementById('btnSiguiente');
    const btnResultados = document.getElementById('btnResultados');
    const progressFill = document.getElementById('progressFill');
    const stepIndicator = document.getElementById('indicador-paso');
    const totalSteps = steps.length;
    let currentStep = 0;

    function updateUI() {
        steps.forEach((step, index) => {
            step.classList.toggle('active', index === currentStep);
        });

        // Limpiar errores al cambiar de paso
        const erroresExistentes = document.querySelectorAll('.msg-error.test-error');
        erroresExistentes.forEach(err => err.remove());

        btnAnterior.style.display = currentStep === 0 ? 'none' : 'inline-block';
        btnSiguiente.style.display = currentStep === totalSteps - 1 ? 'none' : 'inline-block';
        btnResultados.style.display = currentStep === totalSteps - 1 ? 'inline-block' : 'none';

        const progress = ((currentStep + 1) / totalSteps) * 100;
        progressFill.style.width = progress + '%';
        stepIndicator.textContent = 'Pregunta ' + (currentStep + 1) + ' de ' + totalSteps;
    }

    function isCurrentStepAnswered() {
        const currentStepElement = steps[currentStep];
        const radioButtons = currentStepElement.querySelectorAll('input[type="radio"]');
        return Array.from(radioButtons).some(radio => radio.checked);
    }

    function mostrarErrorTest(mensaje) {
        const currentStepElement = steps[currentStep];
        
        // Remover error previo si existe
        const errorPrevio = currentStepElement.querySelector('.msg-error.test-error');
        if (errorPrevio) errorPrevio.remove();

        const errorSpan = document.createElement('span');
        errorSpan.className = 'msg-error test-error';
        errorSpan.style.display = 'block';
        errorSpan.style.marginTop = '1rem';
        errorSpan.style.textAlign = 'center';
        errorSpan.textContent = mensaje;
        
        currentStepElement.appendChild(errorSpan);
    }
    
    // Escuchar cambios en los radios para borrar el error si el usuario selecciona algo
    steps.forEach(step => {
        const radios = step.querySelectorAll('input[type="radio"]');
        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                const errorPrevio = step.querySelector('.msg-error.test-error');
                if (errorPrevio) errorPrevio.remove();
            });
        });
    });

    btnSiguiente.addEventListener('click', function() {
        if (!isCurrentStepAnswered()) {
            mostrarErrorTest('Por favor, seleccioná una opción antes de continuar.');
            return;
        }
        if (currentStep < totalSteps - 1) {
            currentStep++;
            updateUI();
        }
    });

    btnAnterior.addEventListener('click', function() {
        if (currentStep > 0) {
            currentStep--;
            updateUI();
        }
    });

    const form = document.getElementById('testForm');
    if(form) {
        form.addEventListener('submit', function(e) {
            if (!isCurrentStepAnswered()) {
                e.preventDefault();
                mostrarErrorTest('Por favor, seleccioná una opción antes de ver los resultados.');
            }
        });
    }

    updateUI();
});