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

        const rolSelect = document.getElementById('rol');
        if (rolSelect) {
            const toggleAdoptanteFields = (rol) => {
                const adoptanteFields = document.querySelectorAll('.adoptante-field');
                const labelName = document.getElementById('label-name');
                const inputName = document.getElementById('name');
                
                if (rol === 'adoptante') {
                    if (labelName) labelName.textContent = 'Nombre';
                    if (inputName) inputName.placeholder = 'Ingresá tu nombre';
                    adoptanteFields.forEach(field => {
                        field.classList.remove('oculto');
                        if (field.tagName === 'INPUT') field.required = true;
                    });
                } else {
                    if (labelName) labelName.textContent = 'Nombre de la institución';
                    if (inputName) inputName.placeholder = 'Ingresá el nombre de la institución';
                    adoptanteFields.forEach(field => {
                        field.classList.add('oculto');
                        if (field.tagName === 'INPUT') {
                            field.required = false;
                            field.value = '';
                        }
                    });
                }
            };

            rolSelect.addEventListener('change', (e) => toggleAdoptanteFields(e.target.value));
            
            // Verificamos el estado inicial en caso de que Twig no haya coincidido exactamente
            toggleAdoptanteFields(rolSelect.value);
        }
    }
}
