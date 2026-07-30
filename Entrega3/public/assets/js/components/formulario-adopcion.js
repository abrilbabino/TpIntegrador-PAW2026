class PAWFormularioAdopcion {
    constructor() {
        this.initDescargaPDF();
    }

    initDescargaPDF() {
        const btnDescarga = document.getElementById('btn-descarga-pdf');
        if (btnDescarga) {
            // En lugar de poner el iframe feo en el HTML, lo inyectamos dinámicamente
            // Esto dispara la descarga automática en segundo plano
            const iframe = document.createElement('iframe');
            iframe.src = btnDescarga.href;
            iframe.style.display = 'none';
            iframe.title = 'Descarga de PDF automatica';
            document.body.appendChild(iframe);
        }
    }
}
