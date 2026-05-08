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

    btnSiguiente.addEventListener('click', function() {
        if (!isCurrentStepAnswered()) {
            alert('Por favor, seleccioná una opción antes de continuar.');
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
    form.addEventListener('submit', function(e) {
        if (!isCurrentStepAnswered()) {
            e.preventDefault();
            alert('Por favor, seleccioná una opción antes de ver los resultados.');
        }
    });

    updateUI();
});