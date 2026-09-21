<header class="editor-header">
    <nav class="header-nav" aria-label="Navegación del editor">
        <div class="header-back">
            <a class="button button-quiet" href="/">
                <span class="material-symbols-outlined" aria-hidden="true">arrow_back</span>
                Volver
            </a>
            <span class="header-divider" aria-hidden="true"></span>
        </div>
        <label class="search-field" for="handbook-search">
            <span class="material-symbols-outlined" aria-hidden="true">search</span>
            <input id="handbook-search" type="search" placeholder="Buscar servicios, citas o trámites...">
            <span class="search-shortcut" aria-hidden="true">Ctrl + K</span>
        </label>
        <div class="header-actions">
            <button class="button button-muted" type="button" data-preview>
                <span class="material-symbols-outlined" aria-hidden="true">visibility</span>
                <span>Ver previsualización</span>
            </button>
            <span class="header-divider header-divider-desktop" aria-hidden="true"></span>
            <button class="icon-button" type="button" aria-label="Centro de ayuda">
                <span class="material-symbols-outlined" aria-hidden="true">help_outline</span>
            </button>
            <button class="icon-button notification-button" type="button" aria-label="Notificaciones">
                <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
            </button>
            <div class="operator-summary">
                <span class="operator-name"><?= htmlspecialchars($operatorName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                <span class="operator-area"><?= htmlspecialchars($operatorArea, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
            </div>
            <span class="operator-avatar material-symbols-outlined" aria-hidden="true">person</span>
        </div>
    </nav>
</header>
