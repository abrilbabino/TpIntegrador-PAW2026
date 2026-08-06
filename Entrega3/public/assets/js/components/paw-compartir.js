class PAWCompartir {
    constructor(wrapper) {
        this.wrapper = wrapper;
        if (!this.wrapper) return;

        this.nombre  = this.wrapper.dataset.nombre;
        this.especie = this.wrapper.dataset.especie;
        this.url     = window.location.href;
        this.msg     = '¡Mirá a ' + this.nombre + ' buscando hogar! Es un/a ' + this.especie + ' en adopción en PawMap. \nEntrá acá para conocerlo/a mejor:\n' + this.url;
        
        this.btn      = this.wrapper.querySelector('.boton-compartir');
        this.dropdown = this.wrapper.querySelector('.compartir-dropdown');

        this.init();
    }

    init() {
        this.btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const abierto = !this.dropdown.hidden;
            this.dropdown.hidden = abierto;
            this.btn.setAttribute('aria-expanded', String(!abierto));
        });

        document.addEventListener('click', () => {
            this.dropdown.hidden = true;
            this.btn.setAttribute('aria-expanded', 'false');
        });
        
        this.dropdown.addEventListener('click', (e) => { e.stopPropagation(); });

        const wppLink = this.wrapper.querySelector('.compartir-wpp');
        wppLink.href = 'https://wa.me/?text=' + encodeURIComponent(this.msg);

        this.wrapper.querySelector('.compartir-ig').addEventListener('click', (e) => { 
            e.preventDefault();
            this.abrirApp('Instagram', 'https://instagram.com'); 
        });
        
        this.wrapper.querySelector('.compartir-tt').addEventListener('click', (e) => { 
            e.preventDefault();
            this.abrirApp('TikTok', 'https://tiktok.com'); 
        });
    }

    abrirApp(label, appUrl) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(this.msg).then(() => {
                this.mostrarToast('¡Mensaje copiado! ' + label + ' 📋');
            }).catch(() => {
                this.mostrarToast('No se pudo copiar (requiere HTTPS). URL: ' + this.url);
            });
        } else {
            this.mostrarToast('No se pudo copiar automáticamente.');
        }
        this.dropdown.hidden = true;
        this.btn.setAttribute('aria-expanded', 'false');
        setTimeout(() => { window.location.assign(appUrl); }, 1000);
    }

    mostrarToast(texto) {
        const toast = document.createElement('div');
        toast.className = 'compartir-toast';
        toast.textContent = texto;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('compartir-toast--visible'); }, 10);
        setTimeout(() => {
            toast.classList.remove('compartir-toast--visible');
            setTimeout(() => { toast.remove(); }, 400);
        }, 3000);
    }
}
