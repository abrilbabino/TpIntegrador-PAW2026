class PAWModalExito {
  constructor(titulo, mensaje) {
    this.titulo = titulo;
    this.mensaje = mensaje;
  }
  
  mostrar() {
    // Fondo oscuro
    this.fondo = PAW.nuevoElemento("div", "", { class: "fondo-registro", style: "opacity:1; visibility:visible;" });
    
    // Panel del modal reusing styles from iniciar-sesion.css
    this.modal = PAW.nuevoElemento("aside", "", { 
      class: "registro-panel", 
      style: "opacity:1; visibility:visible; transform: translate(-50%, -50%) scale(1); text-align: center; max-width: 450px;" 
    });
    
    // Header con icono gigante
    const header = PAW.nuevoElemento("header", "", { 
      class: "registro-header", 
      style: "justify-content: center; flex-direction: column; border-bottom: none; margin-bottom: 0; padding-bottom: 0;" 
    });
    const icon = PAW.nuevoElemento("span", "check_circle", { 
      class: "material-symbols-outlined", 
      style: "font-size: 5rem; color: #4CAF50; margin-bottom: 1rem;" 
    });
    const h2 = PAW.nuevoElemento("h2", this.titulo, { style: "font-size: 1.8rem;" });
    
    header.appendChild(icon);
    header.appendChild(h2);
    
    // Párrafo de mensaje
    const p = PAW.nuevoElemento("p", this.mensaje, { 
      style: "margin: 1.5rem 0 2rem 0; font-size: 1.1rem; color: #555; line-height: 1.5;" 
    });
    
    // Botón de acción
    const btn = PAW.nuevoElemento("button", "Empezar", { 
      style: "padding: 0.8rem 2.5rem; background-color: var(--color-azul); color: white; border: none; border-radius: 4px; font-size: 1.1rem; cursor: pointer; transition: background-color 0.3s ease;" 
    });
    
    btn.addEventListener("mouseover", () => {
      btn.style.backgroundColor = "var(--color-azul-oscuro)";
    });
    btn.addEventListener("mouseout", () => {
      btn.style.backgroundColor = "var(--color-azul)";
    });
    
    btn.addEventListener("click", () => this.cerrar());
    
    this.modal.appendChild(header);
    this.modal.appendChild(p);
    this.modal.appendChild(btn);
    
    document.body.appendChild(this.fondo);
    document.body.appendChild(this.modal);
  }

  cerrar() {
    this.fondo.style.opacity = "0";
    this.fondo.style.visibility = "hidden";
    this.modal.style.opacity = "0";
    this.modal.style.visibility = "hidden";
    
    setTimeout(() => {
      this.fondo.remove();
      this.modal.remove();
    }, 300);
  }
}
