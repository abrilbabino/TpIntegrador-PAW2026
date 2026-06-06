class PAWChatPage {
    // Inicializa las referencias del DOM y vincula el ID del usuario actual leyendo el dataset.
    constructor() {
        this.mainContainer = document.querySelector('.chat-main-container');
        if (!this.mainContainer) return;

        this.userId = this.mainContainer.dataset.userId;
        this.chatContainer = document.getElementById('chat-messages-container');
        this.chatForm = document.getElementById('chat-form');
        this.chatInput = document.getElementById('chat-input');
        
        const solicitudInput = document.getElementById('solicitud_id');
        if (!solicitudInput) return;
        this.solicitudId = solicitudInput.value;

        this._init();
    }

    // Vincula los manejadores de eventos (event listeners) e inicializa el auto-scroll al final del contenedor.
    _init() {
        this.chatContainer.scrollTop = this.chatContainer.scrollHeight;

        this.chatInput.addEventListener('input', () => {
            this.chatInput.style.height = 'auto';
            this.chatInput.style.height = (this.chatInput.scrollHeight < 120 ? this.chatInput.scrollHeight : 120) + 'px';
        });

        this.chatInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.chatInput.value.trim() !== '') {
                    this.chatForm.dispatchEvent(new Event('submit'));
                }
            }
        });

        // Intercepta el envío del formulario nativo usando preventDefault() para manejarlo asíncronamente.
        this.chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const content = this.chatInput.value.trim();
            if (!content) return;

            this.chatInput.value = '';
            this.chatInput.style.height = 'auto';

            try {
                const formData = new FormData();
                formData.append('solicitud_id', this.solicitudId);
                formData.append('contenido', content);

                // Emplea fetch API nativa con FormData para enviar el contenido de forma segura al servidor.
                const res = await fetch('/chat/enviar', {
                    method: 'POST',
                    body: formData
                });

                if (res.ok) {
                    this.refreshMessages();
                } else {
                    alert('Error al enviar el mensaje');
                }
            } catch (err) {
                console.error('Error:', err);
            }
        });

        // Implementa polling clásico mediante setInterval para consultar nuevos mensajes periódicamente en segundo plano.
        setInterval(() => this.refreshMessages(), 5000);
    }

    // Solicita asíncronamente el historial de chat actualizado y delega la actualización visual.
    async refreshMessages() {
        try {
            const res = await fetch('/api/chat/mensajes?solicitud_id=' + this.solicitudId);
            if (res.ok) {
                const messages = await res.json();
                this.renderMessages(messages);
            }
        } catch(e) {
            console.error(e);
        }
    }

    // Construye y renderiza dinámicamente el DOM a partir del array de mensajes.
    // Utiliza comprobación de scroll previo para mantener al usuario enfocado si ya estaba leyendo abajo.
    renderMessages(messages) {
        if (messages.length === 0) return;
        
        const wasAtBottom = this.chatContainer.scrollHeight - this.chatContainer.scrollTop <= this.chatContainer.clientHeight + 20;
        this.chatContainer.innerHTML = '';
        
        messages.forEach(msg => {
            const isMine = msg.remitente_id == this.userId;
            const rowClass = isMine ? 'message-mine' : 'message-other';
            const time = new Date(msg.fecha_envio).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            const senderHtml = !isMine ? `<span class="message-sender-name">${this.escapeHtml(msg.remitente_nombre)}</span>` : '';
            const contentHtml = this.escapeHtml(msg.contenido).replace(/\n/g, '<br>');

            // Utiliza Template Literals (``) para inyectar los datos en etiquetas HTML5 semánticas (li, article).
            const html = `
                <li class="chat-message-row ${rowClass}">
                    <article class="chat-bubble">
                        ${senderHtml}
                        <p class="message-content">${contentHtml}</p>
                        <span class="message-time">${time}</span>
                    </article>
                </li>
            `;
            this.chatContainer.insertAdjacentHTML('beforeend', html);
        });

        if (wasAtBottom) {
            this.chatContainer.scrollTop = this.chatContainer.scrollHeight;
        }
    }

    // Sanitiza el contenido recibido reemplazando caracteres reservados HTML.
    // Esto previene inyecciones de código (ataques XSS) al renderizar contenido dinámico del usuario.
    escapeHtml(unsafe) {
        return (unsafe || "").toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}
