<dialog class="search-dialog" data-search-dialog aria-labelledby="search-dialog-title">
    <div class="search-dialog-inner">
        <header class="search-dialog-header">
            <span class="material-symbols-outlined search-dialog-icon" aria-hidden="true">search</span>
            <input
                data-search-input
                type="search"
                placeholder="Buscar módulos, tópicos o secciones..."
                autocomplete="off"
                aria-label="Buscar en el manual"
                maxlength="60"
            >
            <kbd class="search-dialog-shortcut">Esc</kbd>
        </header>
        <div class="search-results-container">
            <div class="search-results" data-search-results>
                <div class="search-placeholder-container">
                    <div class="search-placeholder" data-search-placeholder>
                        <span class="material-symbols-outlined search-placeholder-icon" aria-hidden="true">manage_search</span>
                        <span data-search-placeholder-text>Escribí al menos dos caracteres para buscar.</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- <footer class="search-dialog-footer">
            <span><kbd>↑</kbd> <kbd>↓</kbd> navegar</span>
            <span><kbd>Enter</kbd> abrir</span>
            <span><kbd>Esc</kbd> cerrar</span>
        </footer> -->
    </div>
</dialog>
