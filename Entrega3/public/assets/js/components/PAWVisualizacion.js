class PAWVisualizacion {
    constructor(contenedorItems, contenedorPaginacion, itemsPorPagina = 6, tipoVista) {
        this.contenedorItems = contenedorItems;
        this.contenedorPaginacion = contenedorPaginacion;
        this.itemsPorPagina = itemsPorPagina;
        this.currentPage = 1;
        this.items = [];
        this.tipoVista = tipoVista;

        this.paginador = new PAWPaginacion(this.contenedorPaginacion, {
            itemsPorPagina: this.itemsPorPagina,
            onCambioPagina: (nuevaPagina) => this.irAPagina(nuevaPagina)
        });
    }

    init(apiUrl) {
        if (!this.contenedorItems) return;

        fetch(apiUrl)
            .then(res => res.json())
            .then(respuesta => {
                if (respuesta.success) {
                    this.items = respuesta.data;
                    this.render();
                }
            })
            .catch(err => console.error("Error al cargar la API:", err));
    }

    // Actúa como puente entre la capa lógica (Filtros) y la Vista. 
    // Reinicia el estado de paginación (currentPage = 1) para evitar índices fuera de rango al recibir un nuevo set de datos filtrados.
    actualizarDatos(nuevasItems) {
        this.items = nuevasItems;
        this.currentPage = 1;
        this.render();
    }

    render() {
        this.renderizarItems();
        this.paginador.actualizar(this.items.length, this.currentPage);
    }

    // Borra el DOM previo (innerHTML = "").
    // Emplea Template Literals (``) de ES6 para inyectar dinámicamente el contador.
    // Utiliza Array.prototype.slice() para extraer funcionalmente la sublista de items que corresponde estrictamente a la ventana matemática de la página actual.
    renderizarItems() {
        this.contenedorItems.innerHTML = "";
        
        if (!this.items || this.items.length === 0) {
            this.contenedorItems.innerHTML = "<p>No se encontraron resultados.</p>";
            return;
        }

        const inicio = (this.currentPage - 1) * this.itemsPorPagina;
        const itemsAMostrar = this.items.slice(inicio, inicio + this.itemsPorPagina);

        itemsAMostrar.forEach(m => {
            if(this.tipoVista === 'mascotas' || m.tipo_entidad === 'mascota'){
                this.contenedorItems.appendChild(this.crearTarjetaMascota(m));
            }
            else{
                this.contenedorItems.appendChild(this.crearTarjetaRefugio(m));
            }
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
            class: mascota.es_favorito ? "btn-favorito favorito-activo" : "btn-favorito",
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

    crearTarjetaRefugio(refugio) {
        const articulo = PAW.nuevoElemento("article", "", { class: "tarjeta-refugio" });

        const figure = PAW.nuevoElemento("figure", "", { class: "tarjeta-refugio-imagen" });
        const img = PAW.nuevoElemento("img", "", {
            src: `/assets/img/${refugio.imagen || 'default-refugio.jpg'}`,
            alt: refugio.nombre_institucion || "Refugio"
        });
        figure.appendChild(img);

        const info = PAW.nuevoElemento("article", "", { class: "tarjeta-refugio-info" });
        const header = PAW.nuevoElemento("header", "", { class: "tarjeta-refugio-header" });
        
        const h3 = PAW.nuevoElemento("h3", "", {});
        const idRefugio = refugio.id || refugio.usuario_id;
        const linkPerfil = PAW.nuevoElemento("a", refugio.nombre_institucion || 'Sin nombre', {
            href: `/refugio/perfil?id=${idRefugio}`,
            class: "stretched-link"
        });
        h3.appendChild(linkPerfil);

        const linkTel = PAW.nuevoElemento("a", "", {
            href: `tel:${refugio.telefono || ''}`,
            class: "icono-telefono",
            "aria-label": "Llamar al refugio",
            style: "position: relative; z-index: 2;"
        });
        const iconCall = PAW.nuevoElemento("span", "call", { class: "material-symbols-outlined" });
        linkTel.appendChild(iconCall);

        header.appendChild(h3);
        header.appendChild(linkTel);

        const ubicacion = PAW.nuevoElemento("p", `${refugio.ciudad || 'Desconocido'}, ${refugio.provincia || 'Desconocido'}`, {
            class: "refugio-ubicacion"
        });

        const adoptables = PAW.nuevoElemento("p", "", { class: "refugio-adoptables" });
        const strong = PAW.nuevoElemento("strong", `Adoptables disponibles: ${refugio.adoptables_disponibles || 0}`, {});
        adoptables.appendChild(strong);

        info.appendChild(header);
        info.appendChild(ubicacion);
        info.appendChild(adoptables);

        articulo.appendChild(figure);
        articulo.appendChild(info);

        return articulo;
    }

    // Ejecuta la validación de límites matemáticos antes de mutar el estado.
    irAPagina(pagina) {
    const totalPaginas = Math.ceil(this.items.length / this.itemsPorPagina);
        if (pagina >= 1 && pagina <= totalPaginas) {
            this.currentPage = pagina;
            this.render();
            
            this.contenedorItems.scrollIntoView({
                behavior: "smooth",
                block: "start" 
            });
        }
    }
}
