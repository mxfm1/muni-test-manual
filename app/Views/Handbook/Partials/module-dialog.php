<dialog class="module-dialog" data-module-dialog aria-labelledby="module-dialog-title">
    <form action="/handbook/modules" method="post" data-loading-form>
        <header>
            <div>
                <h2 id="module-dialog-title">Crear módulo</h2>
                <p>Define aquellas temáticas principales que conformarán el manual.</p>
            </div>
            <button class="icon-button" data-close-dialog aria-label="Cerrar diálogo" type="button">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </header>
        <input name="csrf_token" type="hidden" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <label for="module-name">Nombre del módulo</label>
        <input id="module-name" name="title" type="text" placeholder="Ej.: Atención de ventanilla" maxlength="255" required>
        <label for="module-description">
            Descripción del módulo
            <span class="field-optional">Opcional</span>
        </label>
        <textarea id="module-description" name="description" rows="3" maxlength="2000" placeholder="Resumí brevemente qué procedimientos agrupa este módulo (ej.: pautas de atención en ventanilla y protocolos internos)."></textarea>
        <footer>
            <button class="button button-muted" data-close-dialog type="button">Cerrar</button>
            <button class="button button-primary" data-loading-submit type="submit">
                <span data-loading-label>Guardar módulo</span>
            </button>
        </footer>
    </form>
</dialog>
