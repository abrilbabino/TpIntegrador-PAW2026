class AppPAW {
  constructor() {
    document.addEventListener("DOMContentLoaded", () => {
      this.init();
    });
  }

  init() {
    this._initMenu();
    this._initCarousel();
    this._initVisualizacion();
    this._initFiltros();
  }

  _initMenu() {
    const navElement = document.querySelector("nav");
    if (navElement) {
      PAW.cargarScript(
        "PAW-Menu-Script",
        "/assets/js/components/paw-menu.js",
        () => {
          // Instanciamos el menú pasándole el nodo del contenedor
          let menu = new PAWMenu(navElement);
          menu.render();
        },
      );
    }
  }

  _initCarousel() {
    const carruseles = document.querySelectorAll("[data-paw-carousel]");
    if (carruseles.length === 0) return;
    PAW.cargarScript(
      "PAW-Carousel-Script",
      "/assets/js/components/paw-carousel.js",
      () => {
        carruseles.forEach((container) => {
          new PAWCarousel(container);
        });
      },
    );
  }

  _initVisualizacion() {
    const contenedorGrilla = document.getElementById("contenedor-grilla");
    const contenedorPaginacion = document.getElementById("contenedor-paginacion");

    const contenedorRefugios = document.getElementById("contenedor-grilla-refugios");
    const paginacionRefugios = document.getElementById("contenedor-paginacion-refugios");

    if (!contenedorGrilla && !contenedorRefugios) return; 

    // Si PAWFiltros va a gestionar mascotas, no crear un visualizador duplicado
    const filtrosMascotas = document.querySelector('[data-paw-filtros="mascotas"]');

    PAW.cargarScript(
      "PAW-Paginacion-Script",
      "/assets/js/components/paw-paginacion.js",
      () => {
        PAW.cargarScript(
          "PAW-Visualizacion-Script",
          "/assets/js/components/PAWVisualizacion.js",
          () => {
            if (contenedorGrilla && contenedorPaginacion && !filtrosMascotas) {
              const visualizador = new PAWVisualizacion(
                  contenedorGrilla, 
                  contenedorPaginacion, 
                  6,
                  'mascotas'
              );
              visualizador.init('/api/mascotas');
          }

            if (contenedorRefugios && paginacionRefugios) {
                const visualizadorRefugios = new PAWVisualizacion(
                    contenedorRefugios, 
                    paginacionRefugios, 
                    6,
                    'refugios'
                );
                visualizadorRefugios.init('/api/refugios');
            }
          }
        );
      }
    );
  }

  _initFiltros() {
    const contenedores = document.querySelectorAll("[data-paw-filtros]");
    if (contenedores.length === 0) return;

    PAW.cargarScript("PAW-Paginacion-Script", "/assets/js/components/paw-paginacion.js", () => {
      PAW.cargarScript("PAW-Visualizacion-Script", "/assets/js/components/PAWVisualizacion.js", () => {
        PAW.cargarScript("PAW-Filtros-Script", "/assets/js/components/PAWFiltros.js", () => {
          
          contenedores.forEach(container => {
              const tipoVista = container.dataset.pawFiltros; 
              
              if (tipoVista === "mascotas") {
                  new PAWFiltros(container, {
                      urlAPI: "/api/mascotas",
                      tipoVista: "mascotas",
                      filtrosConfig: [
                          { prop: "ciudad", label: "Ciudad", type: "select", sourceURL: "/api/refugios" },
                          { prop: "provincia", label: "Provincia", type: "select", sourceURL: "/api/refugios" },
                          { prop: "edad", label: "Edad", type:"rango"},
                          { prop: "tamano", label: "Tamaño", type: "select" },
                          { prop: "especie", label: "Especie", type: "radio" },
                          { prop: "temperamento", label: "Temperamento", type: "select" }
                      ]
                  });
              } else if (tipoVista === "refugios") {
                  new PAWFiltros(container, {
                      urlAPI: "/api/refugios",
                      tipoVista: "refugios",
                      filtrosConfig: [
                          { prop: "provincia", label: "Provincia", type: "select" },
                          { prop: "ciudad", label: "Ciudad", type: "select" }
                      ]
                  });
              }
          });

        });
      });
    });
  }
}
// Se instancia el objeto global para disparar el ciclo de vida de la aplicación
const app = new AppPAW();