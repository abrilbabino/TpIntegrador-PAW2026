(function() {
    var carrusel = document.querySelector('.carrusel-contenedor');
    var indicadores = document.querySelectorAll('.item-carrusel');
    if (!carrusel || indicadores.length === 0) return;

    function actualizarIndicador() {
        var anchoSlide = carrusel.scrollWidth / indicadores.length;
        var idx = Math.round(carrusel.scrollLeft / anchoSlide);
        if (idx >= indicadores.length) idx = indicadores.length - 1;
        indicadores.forEach(function(el, i) {
            el.classList.toggle('active', i === idx);
        });
    }

    carrusel.addEventListener('scroll', actualizarIndicador);

    function reproducir(video) {
        var promesa = video.play();
        if (promesa) promesa.catch(function(){});
    }

    var figurasVideo = document.querySelectorAll('.carrusel-slide--video');
    figurasVideo.forEach(function(figure) {
        var video = figure.querySelector('video');
        if (!video) return;

        video.addEventListener('play', function() {
            figure.classList.add('playing');
        });
        video.addEventListener('pause', function() {
            figure.classList.remove('playing');
        });
        video.addEventListener('ended', function() {
            figure.classList.remove('playing');
        });

        figure.addEventListener('click', function(e) {
            if (video.paused) {
                e.preventDefault();
                reproducir(video);
            } else {
                video.pause();
            }
        });
    });

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                var figure = entry.target;
                var video = figure.querySelector('video');
                if (!video) return;
                if (entry.isIntersecting) {
                    if (video.paused) reproducir(video);
                } else {
                    if (!video.paused) video.pause();
                }
            });
        }, { threshold: 0.6 });

        figurasVideo.forEach(function(figure) {
            observer.observe(figure);
        });
    }

    var svgMascota = document.querySelector('.svg-mascota');
    if (svgMascota) {
        var imgAnim = svgMascota.querySelector('.svg-mascota-img');
        var animando = false;

        function animarSvg() {
            if (animando || !imgAnim) return;
            animando = true;
            imgAnim.classList.remove('animando');
            void imgAnim.offsetWidth;
            imgAnim.classList.add('animando');
            setTimeout(function() {
                imgAnim.classList.remove('animando');
                animando = false;
            }, 600);
        }

        svgMascota.addEventListener('touchstart', animarSvg, {passive: true});

        document.addEventListener('touchmove', function(e) {
            if (animando || !imgAnim) return;
            var touch = e.touches[0];
            if (!touch) return;
            var rect = svgMascota.getBoundingClientRect();
            if (touch.clientX >= rect.left && touch.clientX <= rect.right &&
                touch.clientY >= rect.top && touch.clientY <= rect.bottom) {
                animarSvg();
            }
        }, {passive: true});
    }
})();
