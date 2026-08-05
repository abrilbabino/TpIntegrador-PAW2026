class PAWGenerarQR {
    constructor() {
        this.select      = document.getElementById('mascota-select');
        this.estadoVacio = document.getElementById('qr-estado-vacio');
        this.resultado   = document.getElementById('qr-resultado');
        this.qrContainer = document.getElementById('qr-image-container');
        this.nombreEl    = document.getElementById('qr-nombre-mascota');
        this.selectSize  = document.getElementById('print-size-select');
        this.printZone   = document.getElementById('print-zone');
        this.printQrEl   = document.getElementById('print-qr-container');
        this.printNombre = document.getElementById('print-nombre');

        if (!this.select) return;

        this.init();
    }

    mostrarResultado() {
        this.estadoVacio.classList.add('oculto');
        this.resultado.removeAttribute('hidden');
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                this.resultado.classList.add('visible');
            });
        });
    }

    mostrarVacio() {
        this.resultado.classList.remove('visible');
        this.estadoVacio.classList.remove('oculto');
        setTimeout(() => {
            if (!this.resultado.classList.contains('visible')) {
                this.resultado.setAttribute('hidden', '');
            }
        }, 310);
    }

    init() {
        if (this.selectSize) {
            this.selectSize.addEventListener('change', (e) => {
                if (this.printZone) {
                    this.printZone.classList.remove('size-poster', 'size-medium', 'size-small');
                    this.printZone.classList.add(e.target.value);
                }
            });
            if (this.printZone) this.printZone.classList.add(this.selectSize.value);
        }

        this.select.addEventListener('change', (e) => {
            const opt = e.target.options[e.target.selectedIndex];
            const id  = opt.value;

            if (!id) {
                this.mostrarVacio();
                return;
            }

            const nombre  = opt.getAttribute('data-nombre') || 'Mascota';
            
            const baseUrl = window.location.href.split('/generar-qr')[0];
            const urlQr   = baseUrl + '/mascota?id=' + encodeURIComponent(id);
            const imgUrl  = baseUrl + '/generar-qr/imagen?data=' + encodeURIComponent(urlQr);

            if (this.nombreEl) this.nombreEl.textContent = nombre;
            this.qrContainer.innerHTML = `<img src="${imgUrl}" alt="Codigo QR de ${nombre}" class="qr-code-img">`;

            if (this.printNombre) this.printNombre.textContent = nombre;
            if (this.printQrEl)   this.printQrEl.innerHTML     = `<img src="${imgUrl}" alt="Codigo QR" class="qr-code-img">`;

            this.mostrarResultado();
        });
    }
}