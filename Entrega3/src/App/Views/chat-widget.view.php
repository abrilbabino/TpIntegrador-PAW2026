<?php if (isset($_SESSION['user'])): ?>
    <link rel="stylesheet" href="/assets/css/chat-widget.css" />
    
    <aside id="paw-chat-widget" class="paw-chat-widget collapsed" data-user-id="<?= (int) $_SESSION['user']['id'] ?>">
        <header id="chat-widget-header" class="chat-widget-header">
            <hgroup class="chat-widget-title">
                <?php if (isset($_SESSION['user']['foto_perfil']) && !empty($_SESSION['user']['foto_perfil'])): ?>
                    <img src="/assets/img/<?= htmlspecialchars($_SESSION['user']['foto_perfil']) ?>" alt="Perfil" class="chat-widget-avatar">
                <?php else: ?>
                    <span class="material-symbols-outlined chat-widget-avatar-placeholder">account_circle</span>
                <?php endif; ?>
                <span>Mensajes</span>
                <span id="chat-widget-badge" class="chat-widget-badge chat-oculto">0</span>
            </hgroup>
            <nav class="chat-widget-controls">
                <span id="chat-widget-toggle-icon" class="material-symbols-outlined icon-btn">expand_less</span>
            </nav>
        </header>

        <section class="chat-widget-body">
            
            <article id="chat-widget-list-view" class="chat-widget-view active">
                <search class="chat-widget-search">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" id="chat-widget-search-input" placeholder="Buscar mensajes">
                </search>
                <ol id="chat-widget-list" class="chat-widget-list">
                    <p class="chat-widget-loading">Cargando mensajes...</p>
                </ol>
            </article>

            <article id="chat-widget-conversation-view" class="chat-widget-view">
                <header class="chat-conversation-header">
                    <span id="chat-widget-back-btn" class="material-symbols-outlined icon-btn">arrow_back</span>
                    <hgroup class="chat-conversation-info">
                        <span id="chat-conv-name" class="chat-conv-name">Cargando...</span>
                        <span id="chat-conv-mascota" class="chat-conv-mascota"></span>
                    </hgroup>
                </header>
                <ol id="chat-widget-messages" class="chat-widget-messages">
                </ol>
                <footer class="chat-widget-input-area">
                    <textarea id="chat-widget-input" placeholder="Escribe un mensaje..." rows="1"></textarea>
                    <button id="chat-widget-send-btn" class="btn-send-widget">
                        <span class="material-symbols-outlined">send</span>
                    </button>
                </footer>
            </article>

        </section>
    </aside>
<?php endif; ?>
