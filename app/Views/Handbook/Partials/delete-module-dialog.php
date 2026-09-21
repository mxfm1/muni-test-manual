<dialog class="module-dialog" data-delete-module-dialog aria-labelledby="delete-module-dialog-title">
    <form action="/handbook/modules/delete" method="post" data-loading-form>
        <header>
            <div>
                <h2 id="delete-module-dialog-title">Eliminar módulo</h2>
                <p>Esta acción no se puede deshacer.</p>
            </div>
            <button class="icon-button" data-close-dialog aria-label="Cerrar diálogo" type="button">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </header>
        <input name="csrf_token" type="hidden" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input name="module_id" type="hidden" data-delete-module-id>
        <p class="delete-confirmation">Vas a eliminar <strong data-delete-module-title></strong>, sus tópicos, secciones y media asociados.</p>
        <footer>
            <button class="button button-muted" data-close-dialog type="button">Cancelar</button>
            <button class="button button-danger" data-loading-submit type="submit"><span data-loading-label>Eliminar módulo</span></button>
        </footer>
    </form>
</dialog>
