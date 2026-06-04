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

            if (this.tipoVista !== "mapa") {
                this.visualizacion = new PAWVisualizacion(
                    this.contenedorGrilla,
                    this.contenedorPaginacion,
                    6, 
                    this.tipoVista
                );
            }

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
            // --- NUEVA LÓGICA PARA UBICACIÓN (AUTOCOMPLETE) ---
            else if (filtro.type === "ubicacion") {
                const divUbi = PAW.nuevoElemento("div", "", { class: "input-con-icono" });
                const inputUbi = PAW.nuevoElemento("input", "", {
                    type: "text",
                    "data-prop": filtro.prop,
                    placeholder: "Ingresá tu ubicación",
                    autocomplete: "off"
                });
                
                const ulSugerencias = PAW.nuevoElemento("ul", "", { class: "sugerencias-ubicacion", style: "display:none; position:absolute; z-index:1000; background:white; list-style:none; padding:0; margin:0; border:1px solid #ccc; max-height:200px; overflow-y:auto; width:100%; top:100%; left:0;" });
                
                divUbi.style.position = "relative";
                divUbi.appendChild(inputUbi);
                divUbi.appendChild(ulSugerencias);
                fieldset.appendChild(divUbi);
                
                this.inputsUI[filtro.prop].push(inputUbi);
                
                let timeoutId;
                inputUbi.addEventListener("input", (e) => {
                    const query = e.target.value;
                    this.estadoFiltros[filtro.prop] = query; // Actualizamos estado normal
                    
                    clearTimeout(timeoutId);
                    if (query.length < 3) {
                        ulSugerencias.style.display = "none";
                        // Si borró, reiniciamos coordenadas y aplicamos filtros normales
                        if (query.length === 0) {
                            delete this.estadoFiltros['ubicacion_lat'];
                            delete this.estadoFiltros['ubicacion_lon'];
                            this.aplicarFiltros();
                        }
                        return;
                    }
                    timeoutId = setTimeout(() => {
                        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5`)
                            .then(r => r.json())
                            .then(data => {
                                ulSugerencias.innerHTML = "";
                                if (data.length === 0) {
                                    ulSugerencias.style.display = "none";
                                    return;
                                }
                                data.forEach(item => {
                                    const li = PAW.nuevoElemento("li", item.display_name, { style: "padding:8px; cursor:pointer; border-bottom:1px solid #eee; font-size:0.85rem;" });
                                    li.addEventListener("click", () => {
                                        inputUbi.value = item.display_name;
                                        ulSugerencias.style.display = "none";
                                        
                                        const lat = parseFloat(item.lat);
                                        const lon = parseFloat(item.lon);
                                        
                                        this.estadoFiltros['ubicacion_lat'] = lat;
                                        this.estadoFiltros['ubicacion_lon'] = lon;
                                        this.estadoFiltros[filtro.prop] = inputUbi.value;

                                        if (window.map) {
                                            window.map.setView([lat, lon], 13);
                                            if (window.userMarker) {
                                                window.map.removeLayer(window.userMarker);
                                            }
                                            const userIcon = L.divIcon({
                                                className: 'user-location-marker',
                                                html: '<span class="custom-marker-icon"></span>',
                                                iconSize: [24, 24],
                                                iconAnchor: [12, 12]
                                            });
                                            window.userMarker = L.marker([lat, lon], {icon: userIcon}).addTo(window.map)
                                             .bindPopup("<strong>Ubicación seleccionada</strong>").openPopup();
                                        }
                                        
                                        this.aplicarFiltros();
                                    });
                                    ulSugerencias.appendChild(li);
                                });
                                ulSugerencias.style.display = "block";
                            });
                    }, 400);
                });
                
                document.addEventListener("click", (e) => {
                    if (!divUbi.contains(e.target)) {
                        ulSugerencias.style.display = "none";
                    }
                });
            }

            form.appendChild(fieldset);
        });

        this.btnLimpiar = PAW.nuevoElemento("button", "Limpiar Filtros", { type: "button", class: "btn-limpiar" });
        form.appendChild(this.btnLimpiar);
        details.appendChild(form);
        aside.appendChild(details);

        if (this.tipoVista !== "mapa") {
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
        } else {
            // Para el mapa, solo inyectamos los filtros
            this.container.appendChild(aside);
        }
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
                if (this.inputsUI[prop]) {
                    this.inputsUI[prop].forEach(nodo => {
                        if (nodo.type === "radio" && nodo.value === "") {
                            nodo.checked = true;
                        } else if (nodo.tagName === "SELECT") {
                            nodo.value = "";
                        } else if (nodo.type === "number") {
                            nodo.value = "";
                        } else if (nodo.type === "text") {
                            nodo.value = "";
                        }
                    });
                }
            });

            delete this.estadoFiltros['ubicacion_lat'];
            delete this.estadoFiltros['ubicacion_lon'];
            
            if (window.map && window.userMarker) {
                window.map.removeLayer(window.userMarker);
                window.userMarker = null;
            }

            this.aplicarFiltros();
        });
    }

    aplicarFiltros() {
        const queryUbicacion = (this.estadoFiltros['ubicacion'] || "").toLowerCase().trim();
        const todasLasCiudades = [...new Set(this.items.map(i => (i.ciudad || "").toLowerCase().trim()).filter(c=>c))];
        const queryTieneCiudadExacta = queryUbicacion ? todasLasCiudades.some(c => queryUbicacion.includes(c)) : false;

        this.itemsFiltrados = this.items.filter(item => {
            let cumple = true;
            for (const prop in this.estadoFiltros) {
                if (prop === 'ubicacion_lat' || prop === 'ubicacion_lon' || prop === 'ubicacion') continue;
                
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

            // 3. Filtrar por ubicación (prioridad ciudad sobre provincia)
            if (cumple && queryUbicacion) {
                const ciudad = (item.ciudad || "").toLowerCase().trim();
                const provincia = (item.provincia || "").toLowerCase().trim();
                
                const matchCiudad = ciudad && (queryUbicacion.includes(ciudad) || ciudad.includes(queryUbicacion));
                const matchProvincia = provincia && (queryUbicacion.includes(provincia) || provincia.includes(queryUbicacion));
                
                if (queryTieneCiudadExacta) {
                    // Si el usuario especificó una ciudad que existe en nuestra base, exigimos que sea esa ciudad.
                    // Esto evita que "Mercedes, Buenos Aires" muestre mascotas de "Luján, Buenos Aires" solo porque comparten provincia.
                    if (!matchCiudad) {
                        cumple = false;
                    }
                } else {
                    // Si no hay ciudad exacta (ej: búsqueda parcial "Merce" o búsqueda genérica "Buenos Aires"),
                    // permitimos que coincida por ciudad o por provincia.
                    if (!matchCiudad && !matchProvincia) {
                        cumple = false;
                    }
                }
            }

            return cumple;
        });

        this.visualizacion.actualizarDatos(this.itemsFiltrados);
    }
}