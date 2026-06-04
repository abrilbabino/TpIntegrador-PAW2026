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
    this._initPerfil();
    this._initMapa();
    this._initBusquedas();
    this.initValidador();
    this._initPerfilRefugio();
    this._initFavoritos();
  }

  _initFavoritos() {
    PAW.cargarScript(
      "paw-favoritos",
      "/assets/js/components/paw-favoritos.js",
      () => {
        new PAWFavoritos();
      }
    );
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
                  { prop: "edad", label: "Edad", type: "rango" },
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
            } else if (tipoVista === "mapa") {
              const filtroMapa = new PAWFiltros(container, {
                urlAPI: "/api/mascotas",
                tipoVista: "mapa",
                filtrosConfig: [
                  { prop: "ubicacion", label: "Ubicación", type: "ubicacion" },
                  { prop: "edad", label: "Rango de Edad", type: "rango" },
                  { prop: "tamano", label: "Tamaño", type: "select" },
                  { prop: "especie", label: "Especie", type: "radio" },
                  { prop: "temperamento", label: "Temperamento", type: "select" }
                ]
              });

                  filtroMapa.visualizacion = {
                      actualizarDatos: (itemsFiltrados) => {
                          const carruselTrack = document.querySelector('.paw-carousel-track');
                          if (carruselTrack) {
                              while (carruselTrack.firstChild) {
                                  carruselTrack.removeChild(carruselTrack.firstChild);
                              }
                              
                              if (itemsFiltrados.length === 0) {
                                  const li = PAW.nuevoElemento('li', '', { class: 'paw-carousel-slide' });
                                  const p = PAW.nuevoElemento('p', 'No se encontraron mascotas.', {});
                                  li.appendChild(p);
                                  carruselTrack.appendChild(li);
                              } else {
                                  itemsFiltrados.forEach(m => {
                                      const li = PAW.nuevoElemento('li', '', { class: 'paw-carousel-slide' });
                                      const tarjeta = PAWVisualizacion.crearTarjetaMascota(m);
                                      li.appendChild(tarjeta);
                                      carruselTrack.appendChild(li);
                                  });
                              }
                              
                              const contenedorCarrusel = document.querySelector('[data-paw-carousel]');
                              if (contenedorCarrusel && contenedorCarrusel.pawCarousel) {
                                  contenedorCarrusel.pawCarousel.diapositivas = [...carruselTrack.children];
                                  contenedorCarrusel.pawCarousel.irA(0, false);
                                  contenedorCarrusel.pawCarousel.crearPuntos(); // recrear puntos si cambian
                              }
                          }

                  // Llamar al mapa para que filtre los pines
                  if (window.actualizarPinesMapa) {
                    window.actualizarPinesMapa(itemsFiltrados);
                  }
                }
              };
            }
          });

        });
      });
    });
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

  _initBusquedas() {
    const contenedores = document.querySelectorAll("[data-paw-busquedas]");
    if (contenedores.length === 0) return;
    PAW.cargarScript(
      "PAW-Busquedas-Script",
      "/assets/js/components/paw-busquedas.js",
      () => {
        contenedores.forEach(function (container) {
          new PAWBusquedas(container);
        });
      },
    );
  }

  initValidador() {
    const forms = document.querySelectorAll(".login-form, .registro-form, .form-adopcion, #testForm, .formulario-donaciones, form[action='/contacto/enviar'], #perfil-form");
    if (forms.length === 0) {
      return;
    }

    PAW.cargarScript(
      "paw-validador",
      "/assets/js/components/paw-validador.js",
      () => {
        forms.forEach(form => new PAWValidador(form));
      },
    );
  }

  _initPerfilRefugio() {
    const contenedor = document.querySelector(".perfil-refugio-container");
    if (!contenedor) return;
    PAW.cargarScript(
      "PAW-PerfilRefugio-Script",
      "/assets/js/components/paw-perfil-refugio.js",
      () => {
        const perfilRefugio = new PAWRefugioPerfil(contenedor);
        perfilRefugio.render();
      },
    );
  }

}
// Se instancia el objeto global para disparar el ciclo de vida de la aplicación
const app = new AppPAW();