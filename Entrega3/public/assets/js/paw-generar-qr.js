document.addEventListener('DOMContentLoaded', () => {

    const select      = document.getElementById('mascota-select');
    const estadoVacio = document.getElementById('qr-estado-vacio');
    const resultado   = document.getElementById('qr-resultado');
    const qrContainer = document.getElementById('qr-image-container');
    const nombreEl    = document.getElementById('qr-nombre-mascota');
    const imgPreview  = document.getElementById('img-mascota-preview');
    const selectSize  = document.getElementById('print-size-select');
    const printZone   = document.getElementById('print-zone');
    const printQrEl   = document.getElementById('print-qr-container');
    const printNombre = document.getElementById('print-nombre');

    if (!select) return;

    // Mostrar resultado con animacion suave
    const mostrarResultado = () => {
        estadoVacio.classList.add('oculto');
        resultado.removeAttribute('hidden');
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                resultado.classList.add('visible');
            });
        });
    };

    // Volver al estado vacio con animacion
    const mostrarVacio = () => {
        resultado.classList.remove('visible');
        estadoVacio.classList.remove('oculto');
        setTimeout(() => {
            if (!resultado.classList.contains('visible')) {
                resultado.setAttribute('hidden', '');
            }
        }, 310);
    };

    // Cambio de tamano de impresion
    if (selectSize) {
        selectSize.addEventListener('change', (e) => {
            if (printZone) {
                printZone.classList.remove('size-poster', 'size-medium', 'size-small');
                printZone.classList.add(e.target.value);
            }
        });
        if (printZone) printZone.classList.add(selectSize.value);
    }

    select.addEventListener('change', (e) => {
        const opt = e.target.options[e.target.selectedIndex];
        const id  = opt.value;

        if (!id) {
            mostrarVacio();
            return;
        }

        const nombre  = opt.getAttribute('data-nombre') || 'Mascota';
        const imagen  = opt.getAttribute('data-imagen') || 'default-pet.jpg';
        
        const baseUrl = window.location.href.split('/generar-qr')[0];
        const urlQr   = baseUrl + '/mascota?id=' + encodeURIComponent(id);
        const imgUrl  = baseUrl + '/generar-qr/imagen?data=' + encodeURIComponent(urlQr);

        if (imgPreview) {
            const srcImagen = imagen.indexOf('http') === 0 ? imagen : '/assets/img/' + imagen;
            imgPreview.src = srcImagen;
            imgPreview.onerror = () => {
                imgPreview.src = '/assets/img/qr_placeholder.jpg';
            };
        }

        // Nombre y QR
        if (nombreEl) nombreEl.textContent = nombre;
        qrContainer.innerHTML = `<img src="${imgUrl}" alt="Codigo QR de ${nombre}" class="qr-code-img">`;

        // Actualizar zona de impresion (contenido, no visibilidad — la maneja @media print)
        if (printNombre) printNombre.textContent = nombre;
        if (printQrEl)   printQrEl.innerHTML     = `<img src="${imgUrl}" alt="Codigo QR" class="qr-code-img">`;

        mostrarResultado();
    });

});