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

    PAW.cargarScript(
      "PAW-Paginacion-Script",
      "/assets/js/components/paw-paginacion.js",
      () => {
        PAW.cargarScript(
          "PAW-Visualizacion-Script",
          "/assets/js/components/PAWVisualizacion.js",
          () => {
            if (contenedorGrilla && contenedorPaginacion) {
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
}
// Se instancia el objeto global para disparar el ciclo de vida de la aplicación
const app = new AppPAW();