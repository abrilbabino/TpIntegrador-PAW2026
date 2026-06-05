class PAWModalConfirm {
  constructor(titulo, mensaje, textoAccion = 'Confirmar', claseBoton = 'bold-btn') {
    this.titulo = titulo;
    this.mensaje = mensaje;
    this.textoAccion = textoAccion;
    this.claseBoton = claseBoton;
  }
  
  mostrar() {
    return new Promise((resolve) => {
      this.backdrop = PAW.nuevoElemento("span", "", { class: "avatar-modal-backdrop" });
      this.modal = PAW.nuevoElemento("dialog", "", { class: "avatar-modal" });
      this.content = PAW.nuevoElemento("section", "", { class: "avatar-modal-content" });
      
      const h3 = PAW.nuevoElemento("h3", this.titulo);
      const p = PAW.nuevoElemento("p", this.mensaje, { style: "padding: 0 1.5rem 1.5rem; text-align: center; color: var(--color-texto); font-size: 1rem; margin: 0; line-height: 1.5;" });
      
      const btnAccion = PAW.nuevoElemento("button", this.textoAccion, { type: "button", class: `modal-option-btn ${this.claseBoton}` });
      const btnCancelar = PAW.nuevoElemento("button", "Cancelar", { type: "button", class: "modal-option-btn cancel-btn" });
      
      btnAccion.addEventListener('click', () => {
        this.cerrar();
        resolve(true);
      });
      
      btnCancelar.addEventListener('click', () => {
        this.cerrar();
        resolve(false);
      });
      
      this.content.appendChild(h3);
      this.content.appendChild(p);
      this.content.appendChild(btnAccion);
      this.content.appendChild(btnCancelar);
      
      this.modal.appendChild(this.backdrop);
      this.modal.appendChild(this.content);
      
      document.body.appendChild(this.modal);
    });
  }

  cerrar() {
    if (this.modal) {
      this.modal.remove();
    }
  }
}
