class PAWBusquedaResultados {
    constructor() {
        this.contenedor = document.getElementById('busqueda-resultados');
        if (!this.contenedor) return;
        
        this.grilla = document.getElementById('grilla-resultados');
        this.navPaginacion = document.getElementById('paginacion-js');
        
        const datosStr = this.contenedor.getAttribute('data-resultados');
        if (this.grilla && this.navPaginacion && datosStr) {
            try {
                const datos = JSON.parse(datosStr);
                const visualizacion = new PAWVisualizacion(this.grilla, this.navPaginacion, 6, 'mixto');
                visualizacion.actualizarDatos(datos);
            } catch (e) {
                console.error("Error al parsear resultados de búsqueda:", e);
            }
        }
    }
}
