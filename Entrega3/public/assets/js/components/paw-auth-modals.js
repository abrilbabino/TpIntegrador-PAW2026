class PAWAuthModals {
    constructor() {
        this.loginModal = document.getElementById('modal-login');
        this.registerModal = document.getElementById('modal-register');
        this.btnLogin = document.getElementById('btn-open-login');
        this.btnSwitchRegister = document.getElementById('btn-switch-register');
        
        if (!this.loginModal || !this.registerModal) return;

        this.initEventListeners();
        this.checkUrlParams();
    }

    initEventListeners() {
        // Abrir login desde la barra de navegación
        if (this.btnLogin) {
            this.btnLogin.addEventListener('click', () => {
                this.loginModal.showModal();
            });
        }

        // Cambiar de login a registro
        if (this.btnSwitchRegister) {
            this.btnSwitchRegister.addEventListener('click', () => {
                this.loginModal.close();
                this.registerModal.showModal();
            });
        }

        // Cerrar al hacer click afuera (backdrop)
        [this.loginModal, this.registerModal].forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.close();
                }
            });
        });

        // Alternar visibilidad de contraseñas
        document.querySelectorAll('.mostrar-contraseña').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    this.textContent = 'visibility';
                } else {
                    input.type = 'password';
                    this.textContent = 'visibility_off';
                }
            });
        });
    }

    checkUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        const auth = urlParams.get('auth');
        const registro = urlParams.get('registro');

        if (error || auth === 'login') {
            if (registro || error === 'email_existente' || error === 'usuario_existente') {
                this.registerModal.showModal();
            } else {
                this.loginModal.showModal();
            }

            urlParams.delete('error');
            urlParams.delete('auth');
            urlParams.delete('registro');
            
            const newSearch = urlParams.toString();
            const newUrl = window.location.pathname + (newSearch ? '?' + newSearch : '');
            window.history.replaceState({}, document.title, newUrl);
        }
    }
}
