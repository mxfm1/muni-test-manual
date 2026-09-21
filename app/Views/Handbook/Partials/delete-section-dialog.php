<dialog class="module-dialog" data-delete-section-dialog aria-labelledby="delete-section-dialog-title">
    <form action="/handbook/sections/delete" method="post" data-loading-form>
        <header>
            <div>
                <h2 id="delete-section-dialog-title">Eliminar sección</h2>
                <p>Esta acción no se puede deshacer.</p>
            </div>
            <button class="icon-button" data-close-dialog aria-label="Cerrar diálogo" type="button">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </header>
        <input name="csrf_token" type="hidden" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input name="section_id" type="hidden" data-delete-section-id>
        <p class="delete-confirmation">Vas a eliminar <strong data-delete-section-title></strong> y su media asociada.</p>
        <footer>
            <button class="button button-muted" data-close-dialog type="button">Cancelar</button>
            <button class="button button-danger" data-loading-submit type="submit"><span data-loading-label>Eliminar sección</span></button>
        </footer>
    </form>
</dialog>
