(function() {
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar el mapa
        var latCenter = -34.6037;
        var lngCenter = -58.3816;
        var zoomLevel = 12;

        var inputLat = document.getElementById('lat_usuario');
        var inputLng = document.getElementById('lng_usuario');
        var latUsuario = inputLat ? inputLat.value : '';
        var lngUsuario = inputLng ? inputLng.value : '';

        if (latUsuario && lngUsuario) {
            latCenter = parseFloat(latUsuario);
            lngCenter = parseFloat(lngUsuario);
            zoomLevel = 11;
        }

        var mapElement = document.getElementById('leaflet-map');
        if (!mapElement) return;

        var map = L.map('leaflet-map').setView([latCenter, lngCenter], zoomLevel); 

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Leer los datos desde el atributo data-refugios del HTML
        var refugiosJSON = mapElement.getAttribute('data-refugios');
        var refugios = refugiosJSON ? JSON.parse(refugiosJSON) : [];

        var refugioIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-violet.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });

        if (refugios && refugios.length > 0) {
            var bounds = [];
            refugios.forEach(function(r) {
                var lat = parseFloat(r.latitud);
                var lng = parseFloat(r.longitud);
                
                if (!isNaN(lat) && !isNaN(lng)) {
                    var marker = L.marker([lat, lng], {icon: refugioIcon}).addTo(map);
                    
                    var imgUrl = r.imagen ? '/assets/img/' + r.imagen : '/assets/img/default-refugio.jpg';
                    var telHtml = r.telefono ? '<p class="popup-refugio-tel"><span class="material-symbols-outlined">call</span> ' + r.telefono + '</p>' : '';
                    
                    var popupContent = `
                        <article class="popup-refugio-card">
                            <img src="${imgUrl}" class="popup-refugio-img" alt="Logo Refugio">
                            <section class="popup-refugio-body">
                                <h3>${r.nombre_institucion || 'Refugio'}</h3>
                                <p class="popup-refugio-loc"><span class="material-symbols-outlined">location_on</span> ${r.ciudad || ''}</p>
                                ${telHtml}
                                <a href="/refugio/perfil?id=${r.id}" class="popup-refugio-btn">Ver Perfil</a>
                            </section>
                        </article>
                    `;
                    marker.bindPopup(popupContent, {
                        closeButton: true,
                        minWidth: 200,
                        className: 'custom-popup'
                    });
                    bounds.push([lat, lng]);
                }
            });

            if (bounds.length > 0 && !(latUsuario && lngUsuario)) {
                map.fitBounds(bounds);
            }
        }

        if (latUsuario && lngUsuario) {
            var userIcon = L.divIcon({
                className: 'user-location-marker',
                html: '<span class="custom-marker-icon"></span>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });
            L.marker([parseFloat(latUsuario), parseFloat(lngUsuario)], {icon: userIcon}).addTo(map)
             .bindPopup("<strong>Tú estás aquí</strong>").openPopup();
        }

        var btnGps = document.getElementById('btn-gps-flotante');
        if (btnGps) {
            btnGps.addEventListener('click', function() {
                var btn = this;
                var icon = btn.querySelector('span');
                if (icon) icon.innerText = "hourglass_empty";

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        if (inputLat) inputLat.value = position.coords.latitude;
                        if (inputLng) inputLng.value = position.coords.longitude;
                        var form = document.getElementById('form-filtros');
                        if (form) form.submit();
                    }, function(error) {
                        alert("No se pudo obtener la ubicación exacta. Verifica los permisos de tu navegador.");
                        if (icon) icon.innerText = "my_location";
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    });
                } else {
                    alert("Tu navegador no soporta geolocalización.");
                    if (icon) icon.innerText = "my_location";
                }
            });
        }
    });
})();
