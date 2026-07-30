class PAWVisualizacion {
    constructor(contenedorItems, contenedorPaginacion, itemsPorPagina = 6, tipoVista) {
        this.contenedorItems = contenedorItems;
        this.contenedorPaginacion = contenedorPaginacion;
        this.itemsPorPagina = itemsPorPagina;
        this.currentPage = 1;
        this.items = [];
        this.tipoVista = tipoVista;
        this.puedeModificar = false;

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
                    this.puedeModificar = respuesta.puedeModificar || false;
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
            this.contenedorItems.innerHTML = "";
            const sectionVacio = PAW.nuevoElemento("section", "", { 
                class: `estado-vacio-moderno ${this.tipoVista === 'mascotas' ? 'estado-vacio-mascotas' : ''}` 
            });
            
            const figureIcono = PAW.nuevoElemento("figure", "", { class: "icono-vacio-wrapper" });
            const icono = PAW.nuevoElemento("span", "pets", { class: "material-symbols-outlined icono-vacio-animado" });
            figureIcono.appendChild(icono);

            const textoContenedor = PAW.nuevoElemento("header", "", { class: "texto-vacio-contenedor" });
            const titulo = PAW.nuevoElemento("h3", "¡Ups! No encontramos coincidencias", { class: "titulo-vacio" });
            
            const texto = PAW.nuevoElemento("p", "", { class: "subtitulo-vacio" });

            if (this.tipoVista === 'mascotas') {
                titulo.innerText = "¡Ups! No se encontraron mascotas";
                texto.appendChild(document.createTextNode("Actualmente no tenemos mascotas publicadas que coincidan"));
                texto.appendChild(PAW.nuevoElemento("br"));
                texto.appendChild(document.createTextNode("con todas estas características."));
            } else if (this.tipoVista === 'refugios') {
                texto.innerText = "No encontramos refugios en esta ubicación.";
            } else if (this.tipoVista === 'libreta') {
                titulo.innerText = "Tu libreta está vacía";
                texto.innerText = "Aún no tenés registros médicos guardados para esta mascota.";
            } else {
                texto.innerText = "No hay resultados que coincidan con los filtros actuales.";
            }
            
            textoContenedor.appendChild(titulo);
            textoContenedor.appendChild(texto);
            
            sectionVacio.appendChild(figureIcono);
            sectionVacio.appendChild(textoContenedor);
            
            if (this.tipoVista === 'mascotas') {
                const btnAvisame = PAW.nuevoElemento("button", "", { class: "btn-cola-espera-moderno" });
                
                const iconBtn = PAW.nuevoElemento("span", "notifications_active", { class: "material-symbols-outlined" });
                btnAvisame.appendChild(iconBtn);
                btnAvisame.appendChild(document.createTextNode(" Activar notificaciones"));
                
                btnAvisame.addEventListener("click", () => {
                    document.dispatchEvent(new CustomEvent('paw-cola-espera-solicitada'));
                });
                sectionVacio.appendChild(btnAvisame);
            }
            
            this.contenedorItems.appendChild(sectionVacio);
            return;
        }

        const inicio = (this.currentPage - 1) * this.itemsPorPagina;
        const itemsAMostrar = this.items.slice(inicio, inicio + this.itemsPorPagina);

        itemsAMostrar.forEach(m => {
            if (this.tipoVista === 'mascotas' || m.tipo_entidad === 'mascota') {
                this.contenedorItems.appendChild(PAWVisualizacion.crearTarjetaMascota(m));
            } else if (this.tipoVista === 'libreta') {
                this.contenedorItems.appendChild(PAWVisualizacion.crearTarjetaLibreta(m, this.puedeModificar));
            } else {
                this.contenedorItems.appendChild(PAWVisualizacion.crearTarjetaRefugio(m));
            }
        });
    }

    static crearTarjetaMascota(mascota) {
        const articulo = PAW.nuevoElemento("article", "", { class: "tarjeta-mascota" });

        const figure = PAW.nuevoElemento("figure", "", { class: "tarjeta-imagen" });

        const linkImagen = PAW.nuevoElemento("a", "", {
            href: `/mascota?id=${mascota.id}`,
            class: "link-imagen"
        });

        const imgSrc = mascota.imagen && mascota.imagen.startsWith('http') ? mascota.imagen : `/assets/img/${mascota.imagen || 'default-pet.jpg'}`;
        const img = PAW.nuevoElemento("img", "", {
            src: imgSrc,
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
        const rawNombre = mascota.nombre || 'Sin nombre';
        const nombreCapitalizado = rawNombre.charAt(0).toUpperCase() + rawNombre.slice(1).toLowerCase();
        const nombre = PAW.nuevoElemento("h3", nombreCapitalizado, {});
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

    static crearTarjetaRefugio(refugio) {
        const articulo = PAW.nuevoElemento("article", "", { class: "tarjeta-refugio" });

        const figure = PAW.nuevoElemento("figure", "", { class: "tarjeta-refugio-imagen" });
        const imgSrc = refugio.imagen && refugio.imagen.startsWith('http') ? refugio.imagen : `/assets/img/${refugio.imagen || 'default-refugio.jpg'}`;
        const img = PAW.nuevoElemento("img", "", {
            src: imgSrc,
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

    static crearTarjetaLibreta(registro, puedeModificar = false) {
        const esPendiente = registro.estado === 'PENDIENTE';
        const cardClass = esPendiente ? "card-pendiente" : "card-completado";
        const iconClass = esPendiente ? "icon-pendiente" : "icon-completado";

        const articulo = PAW.nuevoElemento("article", "", { class: `card-registro ${cardClass}` });

        const figure = PAW.nuevoElemento("figure", "", { class: `card-icon-container ${iconClass}` });

        let iconName = registro.icono || "medical_services";
        if (!iconName) iconName = "medical_services";

        const icon = PAW.nuevoElemento("span", iconName, { class: "material-symbols-outlined" });
        figure.appendChild(icon);

        const section = PAW.nuevoElemento("section", "", { class: "card-content" });
        const header = PAW.nuevoElemento("header", "", {});

        const h3 = PAW.nuevoElemento("h3", registro.titulo || "Registro", {});
        const pDate = PAW.nuevoElemento("p", "", { class: "card-date" });
        pDate.appendChild(PAW.nuevoElemento("span", "calendar_today", { class: "material-symbols-outlined" }));

        const fechaAUsar = registro.fecha_realizada || registro.fecha_programada || "";
        pDate.appendChild(document.createTextNode(` ${fechaAUsar}`));

        header.appendChild(h3);
        header.appendChild(pDate);
        section.appendChild(header);

        const artObs = PAW.nuevoElemento("article", "", { class: "card-obs" });
        artObs.appendChild(PAW.nuevoElemento("strong", "Observaciones:", {}));
        const pObs = PAW.nuevoElemento("p", "", {});
        if (registro.observaciones && registro.observaciones.trim() !== '') {
            pObs.innerText = registro.observaciones;
        } else {
            pObs.innerText = "Sin observaciones adicionales.";
            pObs.style.fontStyle = "italic";
            pObs.style.opacity = "0.7";
        }
        artObs.appendChild(pObs);
        section.appendChild(artObs);

        articulo.appendChild(figure);
        articulo.appendChild(section);

        if (esPendiente) {
            if (puedeModificar) {
                const btn = PAW.nuevoElemento("button", "", { type: "button", class: "btn-completar", title: "Marcar como completado" });
                btn.appendChild(PAW.nuevoElemento("span", "check_circle", { class: "material-symbols-outlined" }));
                btn.addEventListener('click', () => {
                    const idInput = document.getElementById('completar_registro_id');
                    const modal = document.getElementById('modal-completar-registro');
                    if (idInput && modal) {
                        idInput.value = registro.id;
                        modal.showModal();
                    }
                });
                articulo.appendChild(btn);
            }

            const footer = PAW.nuevoElemento("footer", "Pendiente", { class: "badge badge-pendiente" });
            articulo.appendChild(footer);
        } else {
            const iconCheck = PAW.nuevoElemento("span", "check_circle", { class: "icono-completado material-symbols-outlined", "aria-label": "Completado" });
            articulo.appendChild(iconCheck);

            const footer = PAW.nuevoElemento("footer", "Completado", { class: "badge badge-completado" });
            articulo.appendChild(footer);
        }

        return articulo;
    }

    irAPagina(pagina) {
        const totalPaginas = Math.ceil(this.items.length / this.itemsPorPagina);
        if (pagina >= 1 && pagina <= totalPaginas) {
            this.currentPage = pagina;
            this.render();

            // Calculamos la posicion considerando un margen superior para que el navbar/header no tape la grilla
            const rect = this.contenedorItems.getBoundingClientRect();
            const scrollTop = window.scrollY || document.documentElement.scrollTop;
            window.scrollTo({
                top: rect.top + scrollTop - 220, // Aumentado a 220px para asegurar espacio bajo el navbar
                behavior: "smooth"
            });
        }
    }
}
