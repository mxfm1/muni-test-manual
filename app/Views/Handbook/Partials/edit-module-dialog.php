<dialog class="module-dialog" data-edit-module-dialog aria-labelledby="edit-module-dialog-title">
    <form action="/handbook/modules/update" method="post" data-loading-form>
        <header>
            <div>
                <h2 id="edit-module-dialog-title">Editar módulo</h2>
                <p>Actualizá el nombre que identifica esta carpeta del manual.</p>
            </div>
            <button class="icon-button" data-close-dialog aria-label="Cerrar diálogo" type="button">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </header>
        <input name="csrf_token" type="hidden" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <input name="module_id" type="hidden" data-edit-module-id>
        <label for="edit-module-name">Nombre del módulo</label>
        <input id="edit-module-name" name="title" type="text" maxlength="255" required data-edit-module-title>
        <label for="edit-module-description">
            Descripción del módulo
            <span class="field-optional">Opcional</span>
        </label>
        <textarea id="edit-module-description" name="description" rows="3" maxlength="2000" data-edit-module-description placeholder="Resumí brevemente qué procedimientos agrupa este módulo (ej.: pautas de atención en ventanilla y protocolos internos)."></textarea>
        <footer>
            <button class="button button-muted" data-close-dialog type="button">Cancelar</button>
            <button class="button button-primary" data-loading-submit type="submit"><span data-loading-label>Guardar cambios</span></button>
        </footer>
    </form>
</dialog>
