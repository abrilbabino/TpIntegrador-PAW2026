class PAWPaginacion {
  // Inicializa el estado fusionando opciones por defecto con el spread operator (...).
  // Implementa el Patrón de Delegación de Eventos en el contenedor padre, utilizando e.target.closest() para capturar clics en los botones dinámicos sin necesidad de reasignar listeners en cada renderizado.
  constructor(container, opciones = {}) {
    this.container = container;
    this.opciones = {
      itemsPorPagina: opciones.itemsPorPagina || 6,
      onCambioPagina: opciones.onCambioPagina || null,
    };
    this.totalItems = 0;
    this.paginaActual = 1;

    this.container.addEventListener("click", (e) => {
      const btn = e.target.closest(".paw-paginacion-btn");
      if (!btn) return;

      e.preventDefault();
      this.irAPagina(parseInt(btn.dataset.pagina));
    });
  }

  actualizar(totalItems, paginaActual = this.paginaActual) {
    this.totalItems = totalItems;
    this.paginaActual = paginaActual;
    this.renderizar();
  }

  irAPagina(pagina) {
    if (this.opciones.onCambioPagina) {
      this.opciones.onCambioPagina(pagina);
    }
  }

  // Reconstruye la barra de navegación. 
  // Accede a window.innerWidth para aplicar lógica responsive en JS, reduciendo la cantidad de botones en dispositivos móviles. 
  // Implementa un algoritmo de "ventana deslizante" con (...) para manejar volúmenes grandes.
  renderizar() {
    this.container.innerHTML = "";
        const totalPaginas = Math.ceil(this.totalItems / this.opciones.itemsPorPagina);
        if (totalPaginas <= 1) return;

        const nav = PAW.nuevoElemento("nav", "", { class: "paginacion" });

        // Botón Anterior
        const btnAnt = this.crearBoton("Anterior", this.paginaActual - 1, ["paw-filtros-btn-anterior"]);
        if (this.paginaActual === 1) btnAnt.disabled = true;
        nav.appendChild(btnAnt);

        const esMovil = window.innerWidth < 994;
        const maxPaginas = esMovil ? 1 : 3;

        const crearElipsis = () => {
            return PAW.nuevoElemento("span", "...", {
                class: "paw-paginacion-ellipsis",
            });
        };

        // Números de página
        if (totalPaginas <= maxPaginas + 1) {
            for (let i = 1; i <= totalPaginas; i++) {
                const clases = i === this.paginaActual ? ["paw-paginacion-btn", "pagina-activa"] : ["paw-paginacion-btn"];
                const btnNum = this.crearBoton(String(i), i, clases);
                nav.appendChild(btnNum);
            }
        } else {
            nav.appendChild(this.crearBoton(1));

            let inicio = this.currentPage - Math.floor(maxPaginas / 2);
            let fin = inicio + maxPaginas - 1;

            if (inicio < 2) {
                fin += (2 - inicio);
                inicio = 2;
            }
            if (fin > totalPaginas - 1) {
                inicio -= (fin - (totalPaginas - 1));
                fin = totalPaginas - 1;
            }
            if (inicio < 2) inicio = 2;

            if (inicio > 2) {
                nav.appendChild(crearElipsis());
            }

            for (let i = inicio; i <= fin; i++) {
                nav.appendChild(this.crearBoton(i));
            }

            if (fin < totalPaginas - 1) {
                nav.appendChild(crearElipsis());
            }

            nav.appendChild(this.crearBoton(totalPaginas));
        }

        // Botón Siguiente
        const btnSig = this.crearBoton("Siguiente", this.paginaActual + 1, ["paw-filtros-btn-siguiente"]);
        if (this.paginaActual === totalPaginas) btnSig.disabled = true;
        nav.appendChild(btnSig);

        this.container.appendChild(nav);
  }

  // fábrica de nodos) que construye las etiquetas <a>.
    crearBoton(texto, pagina, clasesExtra = []) {
      const btn = PAW.nuevoElemento("button", texto, {
            class: ["paw-paginacion-btn", ...clasesExtra].join(" "), 
            "data-pagina": pagina
        });
        return btn;
  }
}
