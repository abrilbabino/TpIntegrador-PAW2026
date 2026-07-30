class PAWBusquedas {
  // Inicializa las referencias del DOM y define la clave de almacenamiento.
  constructor(formulario) {
    this.formulario = formulario;
    this.input = formulario.querySelector("input[name='busqueda']");
    if (!this.input) return;
    this.clave = "pawmap-2026-busquedas";
    this.dropdown = null;
    this.guardarSiHayTerminoEnURL();
    this.crearDropdown();
    this.registrarEventos();
  }

  // Utiliza  URLSearchParams para inspeccionar la query string (window.location.search) y guardar la búsqueda automáticamente.
  guardarSiHayTerminoEnURL() {
    const params = new URLSearchParams(window.location.search);
    const termino = params.get("busqueda");
    if (termino) this.guardar(termino);
  }

  // obtener: Lee de localStorage. Utiliza JSON.parse() porque el almacenamiento nativo del navegador solo admite Strings.
  obtener() {
    try {
      const datos = localStorage.getItem(this.clave);
      return datos ? JSON.parse(datos) : [];
    } catch (e) {
      return [];
    }
  }

  // Aplica filter, spread operator y slice para evitar  duplicados y mantener una cola estricta con las últimas 5 búsquedas. 
  // Luego persiste los datos serializándolos con JSON.stringify().
  guardar(termino) {
    if (!termino || termino.trim() === "") return;
    const anteriores = this.obtener().filter(busqueda => busqueda !== termino);
    const busquedas = [termino, ...anteriores].slice(0, 5);
    localStorage.setItem(this.clave, JSON.stringify(busquedas));
  }

  // Inyecta dinámicamente el contenedor en el DOM.
  crearDropdown() {
    this.dropdown = PAW.nuevoElemento("div", "", {
      class: "paw-busquedas-dropdown"
    });
    this.formulario.appendChild(this.dropdown);
  }

  // Asocia el guardado al submit y controla la visibilidad con focus/blur.
  registrarEventos() {
    this.formulario.addEventListener("submit", () => {
      const termino = this.input.value.trim();
      if (termino) this.guardar(termino);
    });

    this.input.addEventListener("focus", () => {
      this.mostrar();
    });

    this.input.addEventListener("blur", () => {
      setTimeout(() => {
        this.ocultar();
      }, 300);
    });

    this.dropdown.addEventListener("mousedown", (e) => {
      // Prevenimos siempre el mousedown. Esto evita que el input pierda el foco
      // (el evento blur no se dispara prematuramente) y permite que el evento click
      // sobre el enlace se ejecute con total normalidad inmediatamente después.
      e.preventDefault();
    });
  }

  // Renderiza el historial. Utiliza las propiedades de geometría del DOM
  mostrar() {
    const busquedas = this.obtener();
    if (busquedas.length === 0) return;
    
    // (offsetLeft, offsetTop, offsetWidth) para posicionar dinámicamente el dropdown debajo del input.
    this.dropdown.style.left = this.input.offsetLeft + "px";
    this.dropdown.style.top = (this.input.offsetTop + this.input.offsetHeight + 6) + "px";
    this.dropdown.style.width = this.input.offsetWidth + "px";
    this.dropdown.innerHTML = "";

    this.dropdown.appendChild(
      PAW.nuevoElemento("h3", "Últimas búsquedas", { class: "paw-busquedas-titulo" })
    );

    // Al construir el enlace, usa encodeURIComponent() para asegurar que el string de búsqueda viaje de forma segura por la URL HTTP sin romper la sintaxis.
    const lista = PAW.nuevoElemento("ul", "", { class: "paw-busquedas-lista" });
    busquedas.forEach(t => {
      const icono = PAW.nuevoElemento("span", "schedule", { class: "material-symbols-outlined paw-busquedas-icono" });
      const link = PAW.nuevoElemento("a", t, {
        href: "/buscar?busqueda=" + encodeURIComponent(t),
        class: "paw-busquedas-link"
      });
      const item = PAW.nuevoElemento("li", "", { class: "paw-busquedas-item" });
      item.append(icono, link);
      lista.appendChild(item);
    });
    this.dropdown.appendChild(lista);
    this.dropdown.style.display = "block";
  }

  // Remueve el elemento del flujo visual alterando el display.
  ocultar() {
    this.dropdown.style.display = "none";
  }
}
