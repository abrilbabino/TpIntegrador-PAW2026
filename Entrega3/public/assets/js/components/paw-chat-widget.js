class PAWChatWidget {
    // Inicializa el estado base y recupera referencias al DOM necesarias para el widget flotante leyendo atributos data-*.
    constructor() {
        this.widget = document.getElementById('paw-chat-widget');
        if (!this.widget) return; // Si no existe (ej. no logueado), no hacemos nada

        this.isMessagesPage = this.widget.classList.contains('messages-page-widget');
        this.userId = this.widget.dataset.userId;
        this.chatWidgetRefreshInterval = null;
        this.currentChatSolicitudId = null;
        
        // Elementos de UI
        this.header = document.getElementById('chat-widget-header');
        this.toggleIcon = document.getElementById('chat-widget-toggle-icon');
        this.listView = document.getElementById('chat-widget-list-view');
        this.conversationView = document.getElementById('chat-widget-conversation-view');
        this.listContainer = document.getElementById('chat-widget-list');
        this.badge = document.getElementById('chat-widget-badge');
        this.menuBadge = document.getElementById('mensajes-menu-badge');
        this.backBtn = document.getElementById('chat-widget-back-btn');
        this.sendBtn = document.getElementById('chat-widget-send-btn');
        this.inputField = document.getElementById('chat-widget-input');
        this.searchInput = document.getElementById('chat-widget-search-input');
        
        this._bindEvents();
        this.cargarListaChats();
    }

    // Asocia de forma delegada los eventos de interfaz gráfica a sus respectivos métodos de clase.
    _bindEvents() {
        if (this.header && !this.isMessagesPage) {
            this.header.addEventListener('click', () => this.toggleChatWidget());
        }

        if (this.backBtn) {
            this.backBtn.addEventListener('click', () => this.cerrarConversacion());
        }

        if (this.sendBtn) {
            this.sendBtn.addEventListener('click', () => this.enviarMensajeWidget());
        }

        if (this.inputField) {
            this.inputField.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.enviarMensajeWidget();
                }
            });
        }

        if (this.searchInput) {
            // Implementa filtrado local en tiempo real sobre el DOM manipulando estilos, reaccionando al evento 'input'.
            // Utiliza normalize('NFD') para eliminar las tildes y hacer la búsqueda insensible a diacríticos.
            this.searchInput.addEventListener('input', (e) => {
                const normalizar = (str) => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
                
                const filter = normalizar(e.target.value);
                const items = this.listContainer.querySelectorAll('.chat-list-item');
                
                items.forEach(item => {
                    const text = normalizar(item.textContent);
                    if (text.includes(filter)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }

        // Expone un método global en el objeto window para permitir invocar o instanciar chats desde botones externos (ej: perfiles).
        window.abrirChat = (solicitudId, interlocutor = 'Chat', mascotaNombre = 'Mascota') => {
            if (!this.isMessagesPage && this.widget && this.widget.classList.contains('collapsed')) {
                this.toggleChatWidget();
            }
            this.abrirConversacion(solicitudId, interlocutor, mascotaNombre);
        };
    }

    // Gestiona las transiciones alterando dinámicamente la lista de clases (classList) y el texto de los íconos de Google Fonts.
    toggleChatWidget() {
        if (this.widget.classList.contains('collapsed')) {
            this.widget.classList.remove('collapsed');
            this.toggleIcon.textContent = 'expand_more';
            
            if (this.listView.classList.contains('active')) {
                this.cargarListaChats();
            }
        } else {
            this.widget.classList.add('collapsed');
            this.toggleIcon.textContent = 'expand_less';
        }
    }

    // Emplea la API fetch de manera asíncrona con promesas (.then/.catch) para descargar y renderizar los chats recientes.
    cargarListaChats() {
        fetch('/api/chat/list')
            .then(res => {
                if (!res.ok) throw new Error('Error al cargar chats');
                return res.json();
            })
            .then(chats => {
                this.listContainer.innerHTML = '';
                
                if (chats.length === 0) {
                    this.listContainer.innerHTML = '<div class="chat-widget-loading">No tienes mensajes.</div>';
                    this.actualizarBadges(0);
                    return;
                }

                let totalUnread = 0;

                chats.forEach(chat => {
                    totalUnread += chat.unread;
                    
                    const item = document.createElement('li');
                    item.className = 'chat-list-item';
                    item.addEventListener('click', () => {
                        this.abrirConversacion(chat.solicitud_id, chat.interlocutor, chat.mascota_nombre);
                    });
                    
                    let unreadBadge = chat.unread > 0 ? `<span class="badge-mensajes-pendientes chat-list-item-unread">${chat.unread}</span>` : '';
                    
                    let avatarHtml = '';
                    if (chat.foto_interlocutor) {
                        const avatarUrl = chat.foto_interlocutor.startsWith('http') ? chat.foto_interlocutor : `/assets/img/${chat.foto_interlocutor}`;
                        avatarHtml = `<div class="chat-list-item-avatar"><img src="${avatarUrl}" alt="${chat.interlocutor}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;"></div>`;
                    } else {
                        avatarHtml = `<div class="chat-list-item-avatar" style="background-color: var(--color-verde);"><span class="material-symbols-outlined">pets</span></div>`;
                    }
                    
                    item.innerHTML = `
                        ${avatarHtml}
                        <div class="chat-list-item-content">
                            <p class="chat-list-item-name">Adopción de ${chat.mascota_nombre}</p>
                            <p class="chat-list-item-mascota">con ${chat.interlocutor}</p>
                        </div>
                        ${unreadBadge}
                    `;
                    
                    this.listContainer.appendChild(item);
                });

                this.actualizarBadges(totalUnread);
            })
            .catch(err => {
                console.error(err);
                this.listContainer.innerHTML = '<div class="chat-widget-loading">Error al cargar mensajes.</div>';
            });
    }

    // Oculta la vista actual y revela la conversación simulando una transición de enrutamiento estático en cliente.
    actualizarBadges(totalUnread) {
        if (this.badge) {
            this.badge.style.display = totalUnread > 0 && !this.isMessagesPage ? 'inline-block' : 'none';
            this.badge.textContent = totalUnread;
        }

        if (this.menuBadge) {
            this.menuBadge.textContent = totalUnread;
            this.menuBadge.classList.toggle('oculto', totalUnread === 0);
        }
    }

    abrirConversacion(solicitudId, interlocutor, mascotaNombre) {
        this.currentChatSolicitudId = solicitudId;
        
        document.getElementById('chat-conv-name').textContent = 'Adopción de ' + mascotaNombre;
        document.getElementById('chat-conv-mascota').textContent = 'con ' + interlocutor;
        
        this.listView.classList.remove('active');
        this.conversationView.classList.add('active');
        
        this.cargarMensajesWidget();
        
        // Interrumpe cualquier intervalo previo con clearInterval() para evitar solapamiento de llamadas asíncronas concurrentes en la red.
        if (this.chatWidgetRefreshInterval) clearInterval(this.chatWidgetRefreshInterval);
        this.chatWidgetRefreshInterval = setInterval(() => this.cargarMensajesWidget(), 5000);
    }

    // Borra el identificador de estado activo y restaura el DOM regresando a la lista principal de bandeja de entrada.
    cerrarConversacion() {
        this.currentChatSolicitudId = null;
        if (this.chatWidgetRefreshInterval) clearInterval(this.chatWidgetRefreshInterval);
        
        this.conversationView.classList.remove('active');
        this.listView.classList.add('active');
        
        this.cargarListaChats();
    }

    // Descarga mediante fetch() el payload en formato JSON con el historial exacto correspondiente al ID de la solicitud activa.
    cargarMensajesWidget() {
        if (!this.currentChatSolicitudId) return;

        fetch(`/api/chat/mensajes?solicitud_id=${this.currentChatSolicitudId}`)
            .then(res => res.json())
            .then(mensajes => {
                const container = document.getElementById('chat-widget-messages');
                const wasAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 20;

                container.innerHTML = '';
                
                if (mensajes.length === 0) {
                    container.innerHTML = '<div class="chat-widget-loading" style="font-size:12px;">Envía un mensaje para iniciar el chat.</div>';
                } else {
                    mensajes.forEach(msg => {
                        const isMine = msg.remitente_id == this.userId;
                        const li = document.createElement('li');
                        li.className = `chat-msg ${isMine ? 'mine' : 'theirs'}`;
                        li.textContent = msg.contenido;
                        container.appendChild(li);
                    });
                }

                if (wasAtBottom) {
                    container.scrollTop = container.scrollHeight;
                }
            })
            .catch(err => console.error(err));
    }

    // Formatea los datos serializados nativamente mediante URLSearchParams y dispara una solicitud HTTP de método POST pura (x-www-form-urlencoded).
    enviarMensajeWidget() {
        const contenido = this.inputField.value.trim();
        if (!contenido || !this.currentChatSolicitudId) return;

        this.inputField.value = '';

        const formData = new URLSearchParams();
        formData.append('solicitud_id', this.currentChatSolicitudId);
        formData.append('contenido', contenido);

        const csrfTokenInput = document.querySelector('input[name="csrf_token"]');
        if (csrfTokenInput) {
            formData.append('csrf_token', csrfTokenInput.value);
        }

        fetch('/chat/enviar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.inputField.blur();
                window.setTimeout(() => window.scrollTo(0, 0), 0);
                this.cargarMensajesWidget();
            } else {
                alert('Error al enviar el mensaje');
            }
        })
        .catch(err => console.error('Error:', err));
    }
}
