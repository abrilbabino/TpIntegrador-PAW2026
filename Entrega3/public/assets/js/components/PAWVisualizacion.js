class PAWVisualizacion {
    constructor(contenedorMascotas, contenedorPaginacion, itemsPorPagina = 6) {
        this.contenedorMascotas = contenedorMascotas;
        this.contenedorPaginacion = contenedorPaginacion;
        this.itemsPorPagina = itemsPorPagina;
        this.currentPage = 1;
        this.mascotas = [];

        this.paginador = new PAWPaginacion(this.contenedorPaginacion, {
            itemsPorPagina: this.itemsPorPagina,
            onCambioPagina: (nuevaPagina) => this.irAPagina(nuevaPagina)
        });
    }

    init(apiUrl) {
        if (!this.contenedorMascotas) return;

        fetch(apiUrl)
            .then(res => res.json())
            .then(respuesta => {
                if (respuesta.success) {
                    this.mascotas = respuesta.data;
                    this.render();
                }
            })
            .catch(err => console.error("Error al cargar la API:", err));
    }

    // Actúa como puente entre la capa lógica (Filtros) y la Vista. 
    // Reinicia el estado de paginación (currentPage = 1) para evitar índices fuera de rango al recibir un nuevo set de datos filtrados.
    actualizarDatos(nuevasMascotas) {
        this.mascotas = nuevasMascotas;
        this.currentPage = 1;
        this.render();
    }

    render() {
        this.renderizarMascotas();
        this.paginador.actualizar(this.mascotas.length, this.currentPage);
    }

    // Borra el DOM previo (innerHTML = "").
    // Emplea Template Literals (``) de ES6 para inyectar dinámicamente el contador.
    // Utiliza Array.prototype.slice() para extraer funcionalmente la sublista de mascotas que corresponde estrictamente a la ventana matemática de la página actual.
    renderizarMascotas() {
        this.contenedorMascotas.innerHTML = "";
        const inicio = (this.currentPage - 1) * this.itemsPorPagina;
        const mascotasAMostrar = this.mascotas.slice(inicio, inicio + this.itemsPorPagina);

        mascotasAMostrar.forEach(m => {
            this.contenedorMascotas.appendChild(this.crearTarjetaMascota(m));
        });
    }

    crearTarjetaMascota(mascota) {
        const articulo = PAW.nuevoElemento("article", "", { class: "tarjeta-mascota" });

        const figure = PAW.nuevoElemento("figure", "", { class: "tarjeta-imagen" });

        const linkImagen = PAW.nuevoElemento("a", "", {
            href: `/mascota?id=${mascota.id}`,
            class: "link-imagen"
        });

        const img = PAW.nuevoElemento("img", "", {
            src: `/assets/img/${mascota.imagen}`,
            alt: "",
        });

        linkImagen.appendChild(img);
        figure.appendChild(linkImagen);

        const formFavorito = PAW.nuevoElemento("form", "", {
            method: "POST",
            action: "/favorito",
            class: "form-favorito-tarjeta"
        });

        const inputHidden = PAW.nuevoElemento("input", "", {
            type: "hidden",
            name: "mascota_id",
            value: mascota.id
        });

        const btnFavorito = PAW.nuevoElemento("button", "", {
            type: "submit",
            class: "btn-favorito",
            "aria-label": "Agregar a favoritos"
        });

        const iconHeart = PAW.nuevoElemento("span", "favorite", {
            class: "material-symbols-outlined"
        });

        btnFavorito.appendChild(iconHeart);
        formFavorito.appendChild(inputHidden);
        formFavorito.appendChild(btnFavorito);
        figure.appendChild(formFavorito);

        const seccionInfo = PAW.nuevoElemento("section", "", { class: "tarjeta-info" });

        const linkPerfil = PAW.nuevoElemento("a", "", { href: `/mascota?id=${mascota.id}`, class: "verPerfil" });

        const nombre = PAW.nuevoElemento("h3", mascota.nombre || 'Sin nombre', {});

        const tamano = mascota.tamano ? mascota.tamano.charAt(0).toUpperCase() + mascota.tamano.slice(1).toLowerCase() : 'Desconocido';
        const temperamento = mascota.temperamento ? mascota.temperamento.charAt(0).toUpperCase() + mascota.temperamento.slice(1).toLowerCase() : 'Desconocido';
        const edad = mascota.edad || '0';

        const textoDetalles = `${edad} años - ${tamano} - ${temperamento}`;
        const detalles = PAW.nuevoElemento("p", textoDetalles, {});

        seccionInfo.appendChild(nombre);
        seccionInfo.appendChild(detalles);
        linkPerfil.appendChild(seccionInfo);

        articulo.appendChild(figure);
        articulo.appendChild(linkPerfil);

        return articulo;
    }

    // Ejecuta la validación de límites matemáticos antes de mutar el estado.
    irAPagina(pagina) {
    const totalPaginas = Math.ceil(this.mascotas.length / this.itemsPorPagina);
        if (pagina >= 1 && pagina <= totalPaginas) {
            this.currentPage = pagina;
            this.render();
            
            this.contenedorMascotas.scrollIntoView({
                behavior: "smooth",
                block: "start" 
            });
        }
    }
}
