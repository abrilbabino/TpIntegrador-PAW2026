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
              } else if (tipoVista === "mapa") {
                  const filtroMapa = new PAWFiltros(container, {
                      urlAPI: "/api/mascotas",
                      tipoVista: "mapa",
                      filtrosConfig: [
                          { prop: "ubicacion", label: "Ubicación", type: "ubicacion" },
                          { prop: "edad", label: "Rango de Edad", type:"rango"},
                          { prop: "tamano", label: "Tamaño", type: "select" },
                          { prop: "especie", label: "Especie", type: "radio" },
                          { prop: "temperamento", label: "Temperamento", type: "select" }
                      ]
                  });

                  filtroMapa.visualizacion = {
                      actualizarDatos: (itemsFiltrados) => {
                          const carruselTrack = document.querySelector('.paw-carousel-track');
                          if (carruselTrack) {
                              carruselTrack.innerHTML = '';
                              if (itemsFiltrados.length === 0) {
                                  carruselTrack.innerHTML = '<li class="paw-carousel-slide"><p>No se encontraron mascotas.</p></li>';
                              } else {
                                  itemsFiltrados.forEach(m => {
                                      const li = document.createElement('li');
                                      li.className = 'paw-carousel-slide';
                                      
                                      const edad = m.edad || '0';
                                      const tamanoStr = m.tamano ? String(m.tamano) : 'Desconocido';
                                      const temperamentoStr = m.temperamento ? String(m.temperamento) : 'Desconocido';
                                      
                                      const tamanoText = tamanoStr.charAt(0).toUpperCase() + tamanoStr.slice(1);
                                      const temperamentoText = temperamentoStr.charAt(0).toUpperCase() + temperamentoStr.slice(1);

                                      li.innerHTML = `
                                        <article class="tarjeta-mascota">
                                            <figure class="tarjeta-imagen">
                                                <a href="/mascota?id=${m.id}" class="link-imagen">
                                                    <img src="/assets/img/${m.imagen || 'default-pet.jpg'}" alt="${m.nombre || 'Mascota'}">
                                                </a>
                                            </figure>
                                            <a href="/mascota?id=${m.id}" class="verPerfil">
                                                <section class="tarjeta-info">
                                                    <header class="tarjeta-info-header">
                                                        <h3>${m.nombre || 'Sin nombre'}</h3>
                                                    </header>
                                                    <p>${edad} años - ${tamanoText} - ${temperamentoText}</p>
                                                </section>
                                            </a>
                                        </article>
                                      `;
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

}
// Se instancia el objeto global para disparar el ciclo de vida de la aplicación
const app = new AppPAW();