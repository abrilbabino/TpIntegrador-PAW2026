class PAWFavoritos {
  constructor() {
    this.init();
  }

  init() {
    document.body.addEventListener('submit', (e) => {
      const form = e.target.closest('.form-favorito-tarjeta, .form-favorito, .form-quitar-fav');
      if (!form) return;
      
      e.preventDefault();
      
      const formData = new FormData(form);
      const mascotaId = formData.get('mascota_id');
      const btn = form.querySelector('.btn-favorito, .boton-favorito, .btn-corazon');
      
      if (!mascotaId || !btn) return;
      
      const csrfTokenInput = document.querySelector('input[name="csrf_token"]');
      const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';

      fetch('/api/favorito/toggle', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ mascota_id: mascotaId, csrf_token: csrfToken })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          if (form.classList.contains('form-quitar-fav')) {
            const card = form.closest('li');
            if (card) {
                card.remove();
                const ul = document.querySelector('.perfil-cards-grid');
                if (ul && ul.children.length === 0) {
                    window.location.reload();
                }
            }
          } else {
            if (data.action === 'added') {
              btn.classList.add('favorito-activo');
            } else if (data.action === 'removed') {
              btn.classList.remove('favorito-activo');
            }
          }
        } else {
          if (data.error === 'No autorizado') {
            window.location.href = '?auth=login';
          } else {
            console.error('Error al modificar favoritos:', data.error);
          }
        }
      })
      .catch(err => {
        console.error('Error de red al alternar favorito:', err);
      });
    });
  }
}
