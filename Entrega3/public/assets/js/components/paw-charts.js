class PAWCharts {
    constructor() {
        this.tooltip = document.createElement('div');
        this.tooltip.className = 'paw-chart-tooltip';
        document.body.appendChild(this.tooltip);

        this.charts = document.querySelectorAll('.paw-js-pie-chart');
        this.init();
    }

    init() {
        this.charts.forEach(chartContainer => {
            try {
                // Leemos los datos que nos paso PHP/Twig por el atributo HTML
                const rawData = chartContainer.getAttribute('data-chart-data');
                const type = chartContainer.getAttribute('data-chart-type') || 'pie';
                
                let rawParsed = JSON.parse(rawData);
                let data = [];
                
                // Si PHP nos manda un objeto en vez de un array (ej: estadisticas agrupadas),
                // lo convertimos a un array para poder recorrerlo facil.
                if (!Array.isArray(rawParsed)) {
                    for (const [key, value] of Object.entries(rawParsed)) {
                        data.push({ label: key, valor: value });
                    }
                } else {
                    data = rawParsed;
                }

                // Validamos que haya datos para dibujar
                if (!data || data.length === 0) {
                    chartContainer.innerHTML = '<p class="paw-chart-empty">No hay datos suficientes.</p>';
                    return;
                }

                // Calculamos el total sumando todos los valores (para sacar los porcentajes despues)
                const total = data.reduce((sum, item) => sum + Number(item.valor), 0);

                if(total === 0) {
                    chartContainer.innerHTML = '<p class="paw-chart-empty">No hay datos registrados an.</p>';
                    return;
                }

                // Limpiamos el texto de "Cargando..."
                chartContainer.innerHTML = '';
                chartContainer.classList.add('paw-pie-wrapper');

                // Configuramos las medidas base del grafico SVG
                const size = 140;
                const center = size / 2;
                const radius = center;
                const innerRadius = type === 'doughnut' ? center * 0.55 : 0;

                let cumulativePercent = 0;
                
                // Vamos a construir todo el HTML como un solo texto para inyectarlo de golpe (mejor performance)
                let legendHTML = "<ul class='paw-pie-legend'>";
                let svgHTML = `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" class="paw-pie-svg">`;
                
                // Rotamos el grafico -90 grados para que la primera porcion empiece desde arriba (las 12 del reloj)
                svgHTML += `<g transform="translate(${center}, ${center}) rotate(-90)">`;

                // Recorremos cada dato para dibujar su "porcion de torta"
                data.forEach((item, index) => {
                    const value = Number(item.valor);
                    if (value === 0) return; // No dibujamos porciones vacias
                    
                    const percent = value / total;
                    
                    // Asignamos un color segun el orden (si hay mas datos que colores, el modulo % hace que vuelvan a empezar)
                    const coloresCss = ['var(--color-azul)', 'var(--color-naranja)', 'var(--color-magenta)', 'var(--color-verde)', 'var(--color-amarillo)', 'var(--color-gris)'];
                    const color = coloresCss[index % coloresCss.length];
                    
                    // MATEMATICA: Usamos Seno y Coseno para calcular las coordenadas X e Y donde empieza y termina la porcin
                    const startX = Math.cos(2 * Math.PI * cumulativePercent) * radius;
                    const startY = Math.sin(2 * Math.PI * cumulativePercent) * radius;
                    
                    cumulativePercent += percent;
                    
                    const endX = Math.cos(2 * Math.PI * cumulativePercent) * radius;
                    const endY = Math.sin(2 * Math.PI * cumulativePercent) * radius;
                    
                    // Si la porcion ocupa mas de la mitad, el SVG necesita este flag en 1 para dibujarla bien
                    const largeArcFlag = percent > 0.5 ? 1 : 0;
                    
                    if (percent === 1) {
                        // Si es el 100%, dibujamos un circulo completo directamente
                        svgHTML += `<circle cx="0" cy="0" r="${radius}" fill="${color}" class="paw-pie-slice" data-index="${index}"></circle>`;
                    } else {
                        // Si no, dibujamos el camino (path) de la porcion
                        const pathData = `M 0 0 L ${startX} ${startY} A ${radius} ${radius} 0 ${largeArcFlag} 1 ${endX} ${endY} Z`;
                        svgHTML += `<path d="${pathData}" fill="${color}" class="paw-pie-slice" data-index="${index}"></path>`;
                    }

                    // Agregamos el item a la lista de referencias (leyenda)
                    legendHTML += `<li><span class='legend-dot' style='background: ${color}'></span> ${item.label} (${item.valor})</li>`;
                });

                // Si es un grafico de dona, le hacemos un circulo blanco en el centro para tapar
                if (type === 'doughnut') {
                    svgHTML += `<circle cx="0" cy="0" r="${innerRadius}" fill="var(--color-fondo)"></circle>`;
                }

                svgHTML += `</g></svg>`;
                legendHTML += "</ul>";

                // Inyectamos todo el codigo armado al navegador. (El navegador detecta solo que es un SVG)
                chartContainer.innerHTML = legendHTML + svgHTML;

                // 3. Interaccion (Eventos del mouse)
                // Ahora buscamos las porciones recien creadas para darles el efecto hover
                const slices = chartContainer.querySelectorAll('.paw-pie-slice');
                slices.forEach(slice => {
                    slice.addEventListener('mouseenter', (e) => {
                        const idx = slice.getAttribute('data-index');
                        const item = data[idx];
                        const percent = Number(item.valor) / total;
                        const percentageText = (percent * 100).toFixed(1) + '%';
                        
                        // SEGURIDAD: Usamos textContent y createElement en vez de innerHTML
                        // para armar el texto del tooltip. Esto evita inyecciones de codigo (XSS).
                        this.tooltip.textContent = '';
                        const strong = document.createElement('strong');
                        strong.textContent = item.label;
                        this.tooltip.appendChild(strong);
                        this.tooltip.appendChild(document.createElement('br'));
                        this.tooltip.appendChild(document.createTextNode(`${item.valor} (${percentageText})`));
                        
                        this.tooltip.style.opacity = '1';
                    });
                    
                    // Movemos el tooltip para que siga al cursor
                    slice.addEventListener('mousemove', (e) => {
                        this.tooltip.style.left = (e.clientX + 15) + 'px';
                        this.tooltip.style.top = (e.clientY + 15) + 'px';
                    });
                    
                    // Ocultamos el tooltip cuando el mouse sale de la porcion
                    slice.addEventListener('mouseleave', () => {
                        this.tooltip.style.opacity = '0';
                    });
                });

            } catch (error) {
                // Atrapamos errores para que no se rompa el resto del JS de la pagina
                console.error("Error dibujando grafico paw-charts:", error);
            }
        });
    }
}
