class PAWCarousel {
  // Inicializa el estado base. 
  // Utiliza la propiedad .dataset del DOM para leer configuraciones personalizadas (data-*) pasadas desde el HTML.
  constructor(contenedor) {
    this.contenedor = contenedor;
    this.efecto = contenedor.dataset.pawEffect || "slide";
    this.usarMiniaturas = contenedor.dataset.pawMiniaturas !== "false";
    this.indice = 0;
    this.diapositivas = [];
    this.carril = null;
    this.puntos = null;
    this.miniaturas = null;
    this.cargadas = 0;
    this.totalImagenes = 0;
    this.render();
  }

  render() {
    if (!this.contenedor) return;
    this._iniciar();
  }

  // Transforma el DOM. Utiliza el operador spread para convertir el HTMLCollection de hijos en un Array real, filtrando nodos no deseados.
  // Luego reconstruye la estructura semántica agrupándolos en un carril (<ul>).
  _iniciar() {
    this.contenedor.style.display = "";
    this.contenedor.classList.add("paw-carousel");
    if (this.contenedor.dataset.pawFull !== undefined) {
      this.contenedor.classList.add("paw-carousel-full");
    }
    this.contenedor.setAttribute("data-paw-effect", this.efecto);

    this.diapositivas = [...this.contenedor.children].filter(function(el) {
      return el.tagName !== "BR" && el.tagName !== "STYLE";
    });
    if (this.diapositivas.length === 0) return;

    this.carril = PAW.nuevoElemento("ul", "", { class: "paw-carousel-track" });
    this.diapositivas.forEach(function(hijo) {
      const slide = PAW.nuevoElemento("li", "", { class: "paw-carousel-slide" });
      slide.appendChild(hijo);
      this.carril.appendChild(slide);
    }, this);
    this.contenedor.appendChild(this.carril);
    this.diapositivas = [...this.carril.children];

    if (this.diapositivas.length <= 2) {
      this.carril.classList.add("paw-carousel-track-center");
    }

    const imagenes = this.contenedor.querySelectorAll("img");
    if (imagenes.length > 0) {
      this.mostrarProgreso(imagenes);
    } else {
      this.construir();
    }

    this.contenedor.pawCarousel = this;
  }

  // Escucha eventos asíncronos ('load', 'error') de las imágenes. 
  // Calcula el porcentaje matemáticamente para la etiqueta <progress> y utiliza setTimeout para sincronizar el remove del nodo con la transición CSS.
  mostrarProgreso(imagenes) {
    const capa = PAW.nuevoElemento("div", "", { class: "paw-carousel-progress" });
    const barra = PAW.nuevoElemento("progress", "", { class: "paw-carousel-progress-bar", value: 0, max: 100 });
    const etiqueta = PAW.nuevoElemento("output", "0%", { class: "paw-carousel-progress-text" });
    capa.appendChild(barra);
    capa.appendChild(etiqueta);
    this.contenedor.appendChild(capa);

    const avanzar = () => {
      this.cargadas++;
      const porcentaje = Math.round((this.cargadas / imagenes.length) * 100);
      barra.value = porcentaje;
      etiqueta.textContent = porcentaje + "%";
      if (porcentaje >= 100) {
        setTimeout(() => {
          capa.style.opacity = "0";
          setTimeout(() => { capa.remove(); this.construir(); }, 300);
        }, 400);
      }
    };

    this.cargadas = 0;
    imagenes.forEach(function(img) {
      if (img.complete) { avanzar(); return; }
      img.addEventListener("load", avanzar);
      img.addEventListener("error", avanzar);
    });
  }

  // Genera dinámicamente los botones y atajos de teclado (keydown).
  // Emplea el método Function.prototype.bind() en los listeners para preservar la instancia actual de la clase frente al scope del evento DOM.
  construir() {
    this.crearBotones();
    this.crearPuntos();
    this.irA(0, false);
    this.eventosSwipe();
    document.addEventListener("keydown", (e) => {
      if (e.key === "ArrowLeft") { this.anterior(); e.preventDefault(); }
      if (e.key === "ArrowRight") { this.siguiente(); e.preventDefault(); }
    });
  }

  crearBotones() {
    const anterior = PAW.nuevoElemento("button", "‹", {
      class: "paw-carousel-arrow paw-carousel-prev", "aria-label": "Anterior"
    });
    const siguiente = PAW.nuevoElemento("button", "›", {
      class: "paw-carousel-arrow paw-carousel-next", "aria-label": "Siguiente"
    });
    anterior.addEventListener("click", () => { this.anterior(); });
    siguiente.addEventListener("click", () => { this.siguiente(); });
    this.contenedor.appendChild(anterior);
    this.contenedor.appendChild(siguiente);
  }

  crearPuntos() {
    if (this.puntos) {
        this.puntos.remove();
    }
    this.puntos = PAW.nuevoElemento("nav", "", { class: "paw-carousel-dots", "aria-label": "Diapositivas" });
    for (let i = 0; i < this.diapositivas.length; i++) {
      const punto = PAW.nuevoElemento("button", "", {
        class: "paw-carousel-dot", "data-index": i, "aria-label": "Ir a diapositiva " + (i + 1)
      });
      punto.addEventListener("click", this.irA.bind(this, i));
      this.puntos.appendChild(punto);
    }
    this.contenedor.appendChild(this.puntos);
  }

  // Controla el estado del índice (manejando el desbordamiento).
  // Utiliza  Element.scrollIntoView() con scroll suave (smooth) delegando la animación de desplazamiento al motor del navegador.
  irA(indice, animar = true) {
    if (indice < 0) indice = this.diapositivas.length - 1;
    if (indice >= this.diapositivas.length) indice = 0;
    this.indice = indice;

    if (animar) this.diapositivas[indice].scrollIntoView({ behavior: "smooth", inline: "start", block: "nearest" });

    this._marcarActivos(this.diapositivas, indice);
    if (this.puntos) this._marcarActivos(this.puntos.children, indice);
  }

  // Itera sobre colecciones de nodos usando classList.toggle() evaluando la condición booleana para encender o apagar la clase activa.
  _marcarActivos(lista, indice) {
    for (let i = 0; i < lista.length; i++) {
      lista[i].classList.toggle("active", i === indice);
    }
  }

  siguiente() { this.irA(this.indice + 1); }
  anterior() { this.irA(this.indice - 1); }

  // Implementa Touch Events para soporte móvil.
  // Compara e.touches[0].clientX (touchstart vs touchend) para deducir el vector del arrastre. 
  // Usa .bind(this) para no perder el contexto de la instancia de clase.
  eventosSwipe() {
    let inicioX = 0;
    this.contenedor.addEventListener("touchstart", function(e) {
      inicioX = e.touches[0].clientX;
    });
    this.contenedor.addEventListener("touchend", function(e) {
      const diff = inicioX - e.changedTouches[0].clientX;
      if (diff > 50) {
        this.siguiente();
      } else if (diff < -50) {
        this.anterior();
      }
    }.bind(this));
  }
}
