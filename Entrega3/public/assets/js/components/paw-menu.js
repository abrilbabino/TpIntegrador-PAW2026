class PAWMenu {
  constructor(contenedor) {
    this.contenedor = contenedor;
    this.estado = "cerrado";
    this.menuPanel = null;
    this.toggleButton = null;
  }

  render() {
    if (!this.contenedor) {
      console.warn("PAWMenu: contenedor inválido.");
      return;
    }

    this.crearBoton();
    this.inicializarMenu();
    this.registrarEventos();
  }

  crearBoton() {
    this.toggleButton = PAW.nuevoElemento("button", "", {
      type: "button",
      "aria-expanded": "false",
      "aria-label": "Abrir menú principal",
      class: "PAW-MenuAbrir",
    });

    this.iconoSpan = PAW.nuevoElemento("span", "menu", {
      class: "material-symbols-outlined",
    });

    this.toggleButton.appendChild(this.iconoSpan);
    const barraNavegacion = document.querySelector(".Barra-navegacion");
    
    if (barraNavegacion) {
      barraNavegacion.prepend(this.toggleButton); 
    } else {
      this.contenedor.prepend(this.toggleButton);
    }
  }

  inicializarMenu() {
    this.menuPanel = this.contenedor;

    this.menuPanel.classList.add("PAW-Menu", "PAW-MenuCerrado");
    this.menuPanel.setAttribute("aria-hidden", "true");
  }

  registrarEventos() {
    if (!this.toggleButton) {
      return;
    }

    this.toggleButton.addEventListener("click", () => {
      this.estado === "cerrado" ? this.abrirMenu() : this.cerrarMenu();
    });
  }

  abrirMenu() {
    if (!this.menuPanel || !this.toggleButton) {
      return;
    }

    this.menuPanel.classList.replace("PAW-MenuCerrado", "PAW-MenuAbierto");
    this.menuPanel.setAttribute("aria-hidden", "false");
    this.toggleButton.setAttribute("aria-expanded", "true");
    this.iconoSpan.textContent = "close";
    this.estado = "abierto";
  }

  cerrarMenu() {
    if (!this.menuPanel || !this.toggleButton) {
      return;
    }

    this.menuPanel.classList.replace("PAW-MenuAbierto", "PAW-MenuCerrado");
    this.menuPanel.setAttribute("aria-hidden", "true");
    this.toggleButton.setAttribute("aria-expanded", "false");
    this.iconoSpan.textContent = "menu";
    this.estado = "cerrado";
  }
}
