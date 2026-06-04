class AppPAW {
  constructor() {
    document.addEventListener("DOMContentLoaded", () => {
      this.init();
    });
  }

  init() {
    this._initMenu();
    this._initCarousel();
    this._initPerfil();
    this._initMapa();
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

  _initPerfil() {
    const contenedor = document.querySelector(".perfil-container");
    if (!contenedor) return;
    PAW.cargarScript(
      "PAW-Perfil-Script",
      "/assets/js/components/paw-perfil.js",
      () => {
        const perfil = new PAWPerfil(contenedor);
        perfil.render();
      },
    );
  }

  _initMapa() {
    const mapElement = document.getElementById("leaflet-map");
    if (!mapElement) return;
    PAW.cargarScript(
      "PAW-Mapa-Script",
      "/assets/js/mapa.js"
    );
  }

}
// Se instancia el objeto global para disparar el ciclo de vida de la aplicación
const app = new AppPAW();