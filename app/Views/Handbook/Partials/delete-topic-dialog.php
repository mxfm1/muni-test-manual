<dialog class="module-dialog" data-delete-topic-dialog aria-labelledby="delete-topic-dialog-title">
    <form action="/handbook/topics/delete" method="post" data-loading-form>
        <header>
            <div>
                <h2 id="delete-topic-dialog-title">Eliminar tópico</h2>
                <p>Esta acción no se puede deshacer.</p>
            </div>
            <button class="icon-button" data-close-dialog aria-label="Cerrar diálogo" type="button">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </header>
        <input name="csrf_token" type="hidden" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input name="topic_id" type="hidden" data-delete-topic-id>
        <p class="delete-confirmation">Vas a eliminar <strong data-delete-topic-title></strong>, sus secciones y media asociados.</p>
        <footer>
            <button class="button button-muted" data-close-dialog type="button">Cancelar</button>
            <button class="button button-danger" data-loading-submit type="submit"><span data-loading-label>Eliminar tópico</span></button>
        </footer>
    </form>
</dialog>
