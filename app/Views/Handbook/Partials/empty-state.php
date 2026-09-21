<section class="editor-content" data-handbook-overview aria-labelledby="editor-title">
    <div class="editor-introduction">
        <div class="introduction-icon material-symbols-outlined" aria-hidden="true">menu_book</div>
        <div>
            <div class="title-row">
                <h1 id="editor-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
                <span class="selection-status" data-selection-status>Sin selección</span>
            </div>
            <p>Sistema de gestión de manuales de procedimientos y operativas municipales. Versión de prueba</p>
        </div>
        <button class="button button-primary introduction-action" data-header-topic-action type="button" hidden disabled>
            <span class="material-symbols-outlined" aria-hidden="true"></span>
            Crear tópico
        </button>
    </div>
    <?php if ($moduleStatus === 'created'): ?>
        <p class="flash-message flash-success" role="status">El módulo fue creado correctamente.</p>
    <?php elseif ($moduleStatus === 'invalid'): ?>
        <p class="flash-message flash-error" role="alert">Ingresá un nombre de módulo de hasta 255 caracteres.</p>
    <?php elseif ($moduleStatus === 'desc-invalid'): ?>
        <p class="flash-message flash-error" role="alert">La descripción del módulo no puede superar los 2000 caracteres.</p>
    <?php elseif ($moduleStatus === 'error'): ?>
        <p class="flash-message flash-error" role="alert">No fue posible crear el módulo. Intentá nuevamente.</p>
    <?php elseif ($moduleStatus === 'updated'): ?>
        <p class="flash-message flash-success" role="status">El módulo fue actualizado correctamente.</p>
    <?php elseif ($moduleStatus === 'deleted'): ?>
        <p class="flash-message flash-success" role="status">El módulo y sus contenidos asociados fueron eliminados.</p>
    <?php endif; ?>
    <?php if ($topicStatus === 'created'): ?>
        <p class="flash-message flash-success" role="status">El tópico fue creado correctamente.</p>
    <?php elseif ($topicStatus === 'invalid'): ?>
        <p class="flash-message flash-error" role="alert">Ingresá un nombre de tópico de hasta 255 caracteres.</p>
    <?php elseif ($topicStatus === 'error'): ?>
        <p class="flash-message flash-error" role="alert">No fue posible crear el tópico. Intentá nuevamente.</p>
    <?php endif; ?>
    <div class="editor-empty-panel">
        <div class="editor-empty-copy" data-empty-module-state>
            <span class="editor-empty-icon material-symbols-outlined" aria-hidden="true">dashboard_customize</span>
            <h2><?= $modules === [] ? 'No hay ningún módulo seleccionado ni creado aún' : 'Seleccioná un módulo para empezar a editarlo' ?></h2>
            <p><?= $modules === [] ? 'Los módulos son las carpetas principales que agrupan tópicos, instructivos y guías del sistema municipal. Comenzá creando tu primer módulo temático.' : 'Ya creaste la estructura inicial del manual. El siguiente paso será seleccionar un módulo y agregar sus tópicos.' ?></p>
            <div class="empty-actions">
                <button class="button button-primary" type="button" data-open-module-dialog>
                    <span class="material-symbols-outlined" aria-hidden="true">library_add</span>
                    Crear módulo ahora
                </button>
                <a class="button button-muted" href="#how-it-works">
                    <span class="material-symbols-outlined" aria-hidden="true">help</span>
                    Ver documentación
                </a>
            </div>
        </div>
        <section id="how-it-works" class="how-it-works" aria-labelledby="how-it-works-title">
            <h2 id="how-it-works-title">Paso a paso de cómo funciona</h2>
            <ol class="steps-list">
                <li>
                    <span>1</span>
                    <h3>Crear módulo</h3>
                    <p>Agrupá áreas funcionales como agenda de licencias, flujos o atenciones de ventanilla.</p>
                </li>
                <li>
                    <span>2</span>
                    <h3>Agregar tópicos</h3>
                    <p>Seleccioná un módulo para registrar los procedimientos, instructivos y guías que contiene.</p>
                </li>
                <li>
                    <span>3</span>
                    <h3>Editar contenido</h3>
                    <p>Abrí un tópico para trabajar con texto enriquecido, tablas, llamados e imágenes de respaldo.</p>
                </li>
            </ol>
        </section>
    </div>
</section>
