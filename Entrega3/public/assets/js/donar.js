const refugioSelect = document.getElementById('refugio_id');
const aliasInput = document.getElementById('alias');
const cvuInput = document.getElementById('cvu');
const montoInput = document.getElementById('monto_personalizado');

function actualizarDatosRefugio() {
    const option = refugioSelect.options[refugioSelect.selectedIndex];
    aliasInput.value = option?.dataset.alias || 'Sin alias cargado';
    cvuInput.value = option?.dataset.cvu || 'Sin CVU cargado';
}

function setMonto(monto) {
    montoInput.value = monto;
}

refugioSelect.addEventListener('change', actualizarDatosRefugio);
actualizarDatosRefugio();
