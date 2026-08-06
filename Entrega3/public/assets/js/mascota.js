(function() {
    const carrusel = document.querySelector('.carrusel-mascota') || document.querySelector('.carrusel-contenedor');
    const indicadores = document.querySelectorAll('.item-carrusel');
    
    if (carrusel && indicadores.length > 0) {
        function actualizarIndicador() {
            const anchoSlide = carrusel.scrollWidth / indicadores.length;
            let idx = Math.round(carrusel.scrollLeft / anchoSlide);
            if (idx >= indicadores.length) idx = indicadores.length - 1;
            indicadores.forEach((el, i) => {
                el.classList.toggle('active', i === idx);
            });
        }
        carrusel.addEventListener('scroll', actualizarIndicador);
    }



    const svgMascota = document.querySelector('.svg-mascota');
    if (svgMascota) {
        const imgAnim = svgMascota.querySelector('.svg-mascota-img');
        let animando = false;

        function animarSvg() {
            if (animando || !imgAnim) return;
            animando = true;
            imgAnim.classList.remove('animando');
            void imgAnim.offsetWidth;
            imgAnim.classList.add('animando');
            setTimeout(() => {
                imgAnim.classList.remove('animando');
                animando = false;
            }, 600);
        }

        svgMascota.addEventListener('touchstart', animarSvg, {passive: true});

        document.addEventListener('touchmove', (e) => {
            if (animando || !imgAnim) return;
            const touch = e.touches[0];
            if (!touch) return;
            const rect = svgMascota.getBoundingClientRect();
            if (touch.clientX >= rect.left && touch.clientX <= rect.right &&
                touch.clientY >= rect.top && touch.clientY <= rect.bottom) {
                animarSvg();
            }
        }, {passive: true});
    }
})();
