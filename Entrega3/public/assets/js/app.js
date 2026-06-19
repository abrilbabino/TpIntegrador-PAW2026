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
    this._initModalExito();
    this._initChatWidget();
    this._initChatPage();
    this._initLibreta();
    this._initIniciarSesion();
    this._initSeguimiento();
    this._initBusquedaResultados();
  }

  _initSeguimiento() {
    const contenedorSeguimiento = document.querySelector(".seguimiento-layout");
    if (contenedorSeguimiento) {
      PAW.cargarScript(
        "PAW-Seguimiento-Script",
        "/assets/js/components/paw-seguimiento.js",
        () => {}
      );
    }
  }

  _initBusquedaResultados() {
    const contenedor = document.getElementById("busqueda-resultados");
    if (contenedor) {
      PAW.cargarScript(
        "PAW-Busqueda-Resultados-Script",
        "/assets/js/components/paw-busqueda-resultados.js",
        () => {
          new PAWBusquedaResultados();
        }
      );
    }
  }

  _initIniciarSesion() {
    const contenedorSesion = document.querySelector(".registro-container");
    if (contenedorSesion) {
      PAW.cargarScript(
        "PAW-Iniciar-Sesion-Script",
        "/assets/js/components/paw-iniciar-sesion.js",
        () => {
          new PAWIniciarSesion();
        }
      );
    }
  }

  _initLibreta() {
    const contenedorLibreta = document.querySelector(".libreta-main");
    if (contenedorLibreta) {
      PAW.cargarScript(
        "PAW-Libreta-Script",
        "/assets/js/components/paw-libreta.js",
        () => {
          new PAWLibreta();
        }
      );
    }
  }

  _initChatWidget() {
    PAW.cargarScript(
      "script-paw-chat-widget",
      "/assets/js/components/paw-chat-widget.js",
      () => {
        window.chatWidget = new PAWChatWidget();
      }
    );
  }

  _initChatPage() {
    PAW.cargarScript(
      "script-paw-chat-page",
      "/assets/js/components/paw-chat-page.js",
      () => {
        new PAWChatPage();
      }
    );
  }

  _initModalExito() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('registro_exitoso')) {
      PAW.cargarScript("paw-modal-exito", "/assets/js/components/paw-modal-exito.js", () => {
        const modal = new PAWModalExito('¡Registro exitoso!', 'Tu cuenta ha sido creada correctamente. ¡Bienvenido a PawMap!');
        modal.mostrar();

        // Limpiar la URL para que no vuelva a aparecer al recargar
        const newUrl = window.location.pathname + window.location.search.replace(/[\?&]registro_exitoso=1/, '').replace(/^&/, '?');
        window.history.replaceState({}, document.title, newUrl || window.location.pathname);
      });
    }
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

                  let hayFiltrosActivos = false;
                  for (const prop in filtroMapa.estadoFiltros) {
                    const val = filtroMapa.estadoFiltros[prop];
                    if (typeof val === "object") {
                      if (val.min !== "" || val.max !== "") hayFiltrosActivos = true;
                    } else if (val !== "") {
                      hayFiltrosActivos = true;
                    }
                  }

                  // Llamar al mapa para que filtre los pines
                  if (window.actualizarPinesMapa) {
                    window.actualizarPinesMapa(itemsFiltrados, hayFiltrosActivos);
                  }
                }
              };
            } else if (tipoVista === "libreta") {
              const mascotaId = container.dataset.mascotaId;
              const filtroLibreta = new PAWFiltros(container, {
                urlAPI: `/api/mascota/libreta?mascota_id=${mascotaId}`,
                tipoVista: "libreta",
                filtrosConfig: [
                  { prop: "anio", label: "Año", type: "select" },
                  { prop: "mes", label: "Mes", type: "select" },
                  { prop: "categoria", label: "Categoría", type: "select" }
                ]
              });

              filtroLibreta.visualizacion = {
                actualizarDatos: (itemsFiltrados) => {
                  const pendientesCont = document.getElementById("pendientes-container");
                  const historialCont = document.getElementById("historial-container");

                  if (pendientesCont) pendientesCont.innerHTML = "";
                  if (historialCont) historialCont.innerHTML = "";

                  const tituloPendientes = PAW.nuevoElemento("header", "", { class: "titulo" });
                  tituloPendientes.appendChild(PAW.nuevoElemento("span", "event_upcoming", { class: "material-symbols-outlined" }));
                  tituloPendientes.appendChild(document.createTextNode(" Próximos turnos"));
                  if (pendientesCont) pendientesCont.appendChild(tituloPendientes);

                  const tituloHistorial = PAW.nuevoElemento("header", "", { class: "titulo" });
                  tituloHistorial.appendChild(PAW.nuevoElemento("span", "history", { class: "material-symbols-outlined" }));
                  tituloHistorial.appendChild(document.createTextNode(" Historial"));
                  if (historialCont) historialCont.appendChild(tituloHistorial);

                  let pendientesCount = 0;
                  let historialCount = 0;

                  itemsFiltrados.forEach(registro => {
                    const tarjeta = PAWVisualizacion.crearTarjetaLibreta(registro);
                    if (registro.estado === 'PENDIENTE') {
                      if (pendientesCont) pendientesCont.appendChild(tarjeta);
                      pendientesCount++;
                    } else {
                      if (historialCont) historialCont.appendChild(tarjeta);
                      historialCount++;
                    }
                  });

                  if (pendientesCount === 0 && pendientesCont) {
                    pendientesCont.appendChild(PAW.nuevoElemento("p", "No hay eventos próximos.", { class: "no-registros" }));
                  }
                  if (historialCount === 0 && historialCont) {
                    historialCont.appendChild(PAW.nuevoElemento("p", "No hay registros en el historial.", { class: "no-registros" }));
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
    const forms = document.querySelectorAll(".login-form, .registro-form, .form-adopcion, #testForm, .formulario-donaciones, form[action='/contacto/enviar'], #perfil-form, #perfil-refugio-form, #form-publicar-mascota, #form-editar-mascota"); if (forms.length === 0) {
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
