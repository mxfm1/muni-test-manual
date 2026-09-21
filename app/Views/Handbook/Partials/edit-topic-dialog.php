<dialog class="module-dialog" data-edit-topic-dialog aria-labelledby="edit-topic-dialog-title">
    <form action="/handbook/topics/update" method="post" data-loading-form>
        <header>
            <div>
                <h2 id="edit-topic-dialog-title">Editar tópico</h2>
                <p>Actualizá el nombre que identifica este contenido del manual.</p>
            </div>
            <button class="icon-button" data-close-dialog aria-label="Cerrar diálogo" type="button">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </header>
        <input name="csrf_token" type="hidden" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input name="topic_id" type="hidden" data-edit-topic-id>
        <label for="edit-topic-name">Nombre del tópico</label>
        <input id="edit-topic-name" name="title" type="text" maxlength="255" required data-edit-topic-title>
        <footer>
            <button class="button button-muted" data-close-dialog type="button">Cancelar</button>
            <button class="button button-primary" data-loading-submit type="submit"><span data-loading-label>Guardar cambios</span></button>
        </footer>
    </form>
</dialog>
