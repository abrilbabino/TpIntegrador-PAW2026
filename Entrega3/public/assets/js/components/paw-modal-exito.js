class PAWModalExito {
  constructor(titulo, mensaje) {
    this.titulo = titulo;
    this.mensaje = mensaje;
  }
  
  mostrar() {
    this.dialog = PAW.nuevoElemento("dialog", "", { class: "modal-auth" });
    
    const section = PAW.nuevoElemento("section", "", { class: "modal-auth-content modal-exito-content" });
    
    const icon = PAW.nuevoElemento("span", "check_circle", { 
      class: "material-symbols-outlined modal-exito-icon"
    });
    
    const h2 = PAW.nuevoElemento("h2", this.titulo, { class: "modal-exito-title" });
    
    const p = PAW.nuevoElemento("p", this.mensaje, { class: "modal-exito-text" });
    
    const btn = PAW.nuevoElemento("button", "Empezar", { 
      class: "btn-auth"
    });
    
    btn.addEventListener("click", () => this.cerrar());
    
    section.appendChild(icon);
    section.appendChild(h2);
    section.appendChild(p);
    section.appendChild(btn);
    
    this.dialog.appendChild(section);
    document.body.appendChild(this.dialog);
    
    this.dialog.showModal();
  }

  cerrar() {
    this.dialog.close();
    setTimeout(() => {
      this.dialog.remove();
    }, 100);
  }
}
