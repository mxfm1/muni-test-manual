<dialog class="module-dialog" data-topic-dialog aria-labelledby="topic-dialog-title">
    <form action="/handbook/topics" method="post" data-loading-form>
        <header>
            <div>
                <h2 id="topic-dialog-title">Crear tópico</h2>
                <p>Añade un tópico dentro del módulo para organizar el manual</p>
            </div>
            <button class="icon-button" data-close-dialog aria-label="Cerrar diálogo" type="button">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </header>
        <input name="csrf_token" type="hidden" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input name="module_id" type="hidden" data-topic-module-id>
        <label for="topic-name">Nombre del tópico</label>
        <input id="topic-name" name="title" type="text" placeholder="Ej.: Configurar permisos" maxlength="255" required>
        <footer>
            <button class="button button-muted" data-close-dialog type="button">Cerrar</button>
            <button class="button button-primary" data-loading-submit type="submit"><span data-loading-label>Guardar tópico</span></button>
        </footer>
    </form>
</dialog>
