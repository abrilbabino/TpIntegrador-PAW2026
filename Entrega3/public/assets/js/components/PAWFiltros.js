class PAWFiltros {
    constructor(contenedorRaiz, configFiltros) {
        this.container = contenedorRaiz;
        this.urlAPI = configFiltros.urlAPI;
        this.tipoVista = configFiltros.tipoVista;
        this.filtrosConfig = configFiltros.filtrosConfig;
        this.items = [];
        this.itemsFiltrados = [];
        this.estadoFiltros = {}; 
        this.inputsUI = {}; 

        this.filtrosConfig.forEach(filtro => {
            if (filtro.type === "rango") {
                this.estadoFiltros[filtro.prop] = { min: "", max: "" };
            } else {
                this.estadoFiltros[filtro.prop] = "";
            }
        });

        this.init();
    }

    async init() {
        try {
            const response = await fetch(this.urlAPI);
            const resultado = await response.json();
            if (!resultado.success) throw new Error("Error en API");
            
            this.items = resultado.data;

            this.datosAuxiliares = {};
            const sourcesCache = {};

            for (const filtro of this.filtrosConfig) {
                if (!filtro.sourceURL) continue;
                
                if (!sourcesCache[filtro.sourceURL]) {
                    const res = await fetch(filtro.sourceURL);
                    const json = await res.json();
                    sourcesCache[filtro.sourceURL] = json.success ? json.data : [];
                }
                
                this.datosAuxiliares[filtro.prop] = sourcesCache[filtro.sourceURL];
            }

            this.construirHTML();

            this.visualizacion = new PAWVisualizacion(
                this.contenedorGrilla,
                this.contenedorPaginacion,
                6, 
                this.tipoVista
            );

            this.registrarEventos();
            this.aplicarFiltros();

        } catch (error) {
            console.error("Error cargando el módulo de mascotas:", error);
        }
    }

    construirHTML() {
        this.container.innerHTML = ""; 
        const sectionPrincipal = PAW.nuevoElemento("section", "", { class: "seccion-filtros" });

        const aside = PAW.nuevoElemento("aside", "", { class: "seccion-filtros-aside" });
        const details = PAW.nuevoElemento("details", "", { class: "filtros", open: "true" });
        const summary = PAW.nuevoElemento("summary", "");
        summary.innerHTML = `<span class="material-symbols-outlined">filter_list</span><span>Filtros</span><span class="material-symbols-outlined filtros-simbolo">expand_more</span>`;
        details.appendChild(summary);

        const form = PAW.nuevoElemento("form", "", { onsubmit: "return false;" });

        this.filtrosConfig.forEach(filtro => {
            const fieldset = PAW.nuevoElemento("fieldset", "");
            fieldset.appendChild(PAW.nuevoElemento("legend", filtro.label));

            const valoresUnicos = new Set();
            // Si el filtro tiene datos auxiliares (sourceURL), usarlos para poblar las opciones
            const fuenteDatos = this.datosAuxiliares[filtro.prop] || this.items;
            fuenteDatos.forEach(item => {
                if (item[filtro.prop] != null && item[filtro.prop] !== "") valoresUnicos.add(item[filtro.prop]);
            });
            const opcionesOrdenadas = Array.from(valoresUnicos).sort();

            this.inputsUI[filtro.prop] = []; 

            if (filtro.type === "select") {
                const select = PAW.nuevoElemento("select", "", { name: filtro.prop });
                select.appendChild(PAW.nuevoElemento("option", "Todos", { value: "" }));
                
                opcionesOrdenadas.forEach(valor => {
                    const texto = valor.charAt(0).toUpperCase() + valor.slice(1).toLowerCase();
                    select.appendChild(PAW.nuevoElemento("option", texto, { value: valor }));
                });
                
                fieldset.appendChild(select);
                this.inputsUI[filtro.prop].push(select);
            } 
            else if (filtro.type === "radio") {
                const divRadios = PAW.nuevoElemento("div", "", { class: "grupo-radios" });
                
                const labelTodos = PAW.nuevoElemento("label", "", { class: `${filtro.prop}-radio` });
                const inputTodos = PAW.nuevoElemento("input", "", { type: "radio", name: filtro.prop, value: "", checked: "true" });
                labelTodos.appendChild(inputTodos);
                labelTodos.appendChild(PAW.nuevoElemento("span", "Todos"));
                divRadios.appendChild(labelTodos);
                this.inputsUI[filtro.prop].push(inputTodos);

                opcionesOrdenadas.forEach(valor => {
                    const texto = valor.charAt(0).toUpperCase() + valor.slice(1).toLowerCase();
                    const label = PAW.nuevoElemento("label", "", { class: `${filtro.prop}-radio` });
                    const input = PAW.nuevoElemento("input", "", { type: "radio", name: filtro.prop, value: valor });
                    label.appendChild(input);
                    label.appendChild(PAW.nuevoElemento("span", texto));
                    divRadios.appendChild(label);
                    this.inputsUI[filtro.prop].push(input);
                });
                fieldset.appendChild(divRadios);
            }
            // --- NUEVA LÓGICA PARA RANGO (EJ: EDAD) ---
            else if (filtro.type === "rango") {
                const divRango = PAW.nuevoElemento("div", "", { class: "edad-rango" });
                
                const inputMin = PAW.nuevoElemento("input", "", { 
                    type: "number", 
                    "data-prop": filtro.prop, // Vinculamos a "edad"
                    "data-rango": "min",      // Avisamos que es el mínimo
                    placeholder: "Mín", 
                    min: "0" 
                });
                
                const span = PAW.nuevoElemento("span", "a");
                
                const inputMax = PAW.nuevoElemento("input", "", { 
                    type: "number", 
                    "data-prop": filtro.prop, // Vinculamos a "edad"
                    "data-rango": "max",      // Avisamos que es el máximo
                    placeholder: "Máx", 
                    min: "0" 
                });

                divRango.appendChild(inputMin);
                divRango.appendChild(span);
                divRango.appendChild(inputMax);
                fieldset.appendChild(divRango);
                
                this.inputsUI[filtro.prop].push(inputMin, inputMax);
            }

            form.appendChild(fieldset);
        });

        this.btnLimpiar = PAW.nuevoElemento("button", "Limpiar Filtros", { type: "button", class: "btn-limpiar" });
        form.appendChild(this.btnLimpiar);
        details.appendChild(form);
        aside.appendChild(details);

        const sectionContenido = PAW.nuevoElemento("section", "", { class: this.tipoVista === "mascotas" ? "adoptar-contenido" : "refugios-contenido" });
        this.contenedorGrilla = PAW.nuevoElemento("div", "", { class: this.tipoVista === "mascotas" ? "grilla-mascotas" : "grilla-refugios" });
        this.contenedorPaginacion = PAW.nuevoElemento("div", "", { class: "paginacion" });
        
        sectionContenido.appendChild(this.contenedorGrilla);
        sectionContenido.appendChild(this.contenedorPaginacion);

        if (this.tipoVista === "refugios") {
            const createCTA = (isMobile) => {
                const cta = PAW.nuevoElemento("article", "", { class: `cta-refugio ${isMobile ? 'mobile' : 'desktop'}` });
                cta.innerHTML = `
                    <h3><span class="material-symbols-outlined">pets</span> ¿Representás a un Refugio?</h3>
                    <p>Sumate a nuestra red y dale visibilidad a tus mascotas.</p>
                    <a href="/registro-refugio" class="btn-registro-refugio">
                        <span class="material-symbols-outlined">add_circle</span> Registrate
                    </a>
                `;
                return cta;
            };
            aside.appendChild(createCTA(false)); // Desktop CTA en aside
            sectionContenido.appendChild(createCTA(true)); // Mobile CTA en contenido
        }

        sectionPrincipal.appendChild(aside);
        sectionPrincipal.appendChild(sectionContenido);
        this.container.appendChild(sectionPrincipal);
    }

    registrarEventos() {
        Object.values(this.inputsUI).forEach(nodosArray => {
            nodosArray.forEach(nodo => {
                nodo.addEventListener("input", (e) => { // Usamos input para que detecte al escribir
                    const prop = e.target.dataset.prop || e.target.name;
                    
                    // Si es parte de un rango, actualizamos su submódulo (min o max)
                    if (e.target.dataset.rango) {
                        this.estadoFiltros[prop][e.target.dataset.rango] = e.target.value;
                    } else {
                        this.estadoFiltros[prop] = e.target.value;
                    }
                    this.aplicarFiltros();
                });
            });
        });

        this.btnLimpiar.addEventListener("click", () => {
            Object.keys(this.estadoFiltros).forEach(prop => {
                // Reseteamos el estado según su tipo
                if (typeof this.estadoFiltros[prop] === "object") {
                    this.estadoFiltros[prop] = { min: "", max: "" };
                } else {
                    this.estadoFiltros[prop] = ""; 
                }
                
                // Reseteamos la vista
                this.inputsUI[prop].forEach(nodo => {
                    if (nodo.type === "radio" && nodo.value === "") {
                        nodo.checked = true;
                    } else if (nodo.tagName === "SELECT") {
                        nodo.value = "";
                    } else if (nodo.type === "number") {
                        nodo.value = "";
                    }
                });
            });
            this.aplicarFiltros();
        });
    }

    aplicarFiltros() {
        this.itemsFiltrados = this.items.filter(item => {
            let cumple = true;
            for (const prop in this.estadoFiltros) {
                const valorBuscado = this.estadoFiltros[prop];
                
                // 1. Evaluación matemática para RANGOS (ej: edad)
                if (typeof valorBuscado === "object") {
                    const valorItem = parseFloat(item[prop]);
                    const min = parseFloat(valorBuscado.min);
                    const max = parseFloat(valorBuscado.max);
                    
                    if (!isNaN(min) && valorItem < min) { cumple = false; break; }
                    if (!isNaN(max) && valorItem > max) { cumple = false; break; }
                } 
                // 2. Evaluación estricta para el resto (Selects, Radios)
                else {
                    if (valorBuscado !== "" && String(item[prop]) !== String(valorBuscado)) {
                        cumple = false;
                        break; 
                    }
                }
            }
            return cumple;
        });

        this.visualizacion.actualizarDatos(this.itemsFiltrados);
    }
}