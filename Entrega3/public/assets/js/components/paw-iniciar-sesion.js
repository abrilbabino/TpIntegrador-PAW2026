class PAWIniciarSesion {
    constructor() {
        this.initEventListeners();
    }

    initEventListeners() {
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
}
