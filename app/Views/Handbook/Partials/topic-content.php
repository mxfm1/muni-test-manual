<?php $emptyTiptapDocument = '{"type":"doc","content":[{"type":"paragraph"}]}'; ?>
<section class="topic-content" data-topic-content hidden aria-live="polite">
    <?php if ($sectionStatus === 'created'): ?>
        <p class="flash-message flash-success" role="status">La sección fue creada correctamente.</p>
    <?php elseif ($sectionStatus === 'updated'): ?>
        <p class="flash-message flash-success" role="status">La sección fue actualizada correctamente.</p>
    <?php elseif ($sectionStatus === 'deleted'): ?>
        <p class="flash-message flash-success" role="status">La sección fue eliminada correctamente.</p>
    <?php elseif ($sectionStatus === 'error'): ?>
        <p class="flash-message flash-error" role="alert">No fue posible guardar los cambios de la sección.</p>
    <?php endif; ?>
    <?php if ($topicStatus === 'updated'): ?>
        <p class="flash-message flash-success" role="status">El tópico fue actualizado correctamente.</p>
    <?php elseif ($topicStatus === 'deleted'): ?>
        <p class="flash-message flash-success" role="status">El tópico fue eliminado correctamente.</p>
    <?php endif; ?>

    <div class="topic-no-selection" data-no-topic-selected hidden>
        <span class="editor-empty-icon material-symbols-outlined" aria-hidden="true">topic</span>
        <h2>Ningún tópico ha sido seleccionado para configurar</h2>
        <p>Seleccioná un tópico del panel lateral para organizar sus secciones, contenido y recursos asociados.</p>
        <div class="divider">
            <span class="divider-line"></span>
            <p>o bien</p>
            <span class="divider-line"></span>
        </div>
        <button class="button add-topic-button" data-open-topic-dialog type="button">
            <span class="material-symbols-outlined" aria-hidden="true">add</span>
            Crear tópico
        </button>
    </div>

    <?php foreach ($topics as $topic): ?>
        <?php $sections = $sectionsByTopic[$topic->id] ?? []; ?>
        <article class="topic-panel" data-topic-panel data-topic-id="<?= $topic->id ?>" hidden>
            <header class="topic-content-header">
                <div class="topic-content-title">
                    <span class="introduction-icon material-symbols-outlined" aria-hidden="true">topic</span>
                    <div>
                        <!-- <p class="selected-module-eyebrow">Tópico seleccionado</p> -->
                        <div class="topic-heading-row">
                            <h1><?= htmlspecialchars($topic->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
                            <!-- <span class="selection-status">Seleccionado</span> -->
                        </div>
                        <!-- <?php if ($topic->description !== null): ?>
                            <p><?= htmlspecialchars($topic->description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        <?php endif; ?> -->
                    </div>
                </div>
                <div class="topic-header-actions">
                    <button class="button button-primary" data-open-new-section-editor data-topic-id="<?= $topic->id ?>" data-topic-title="<?= htmlspecialchars($topic->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" type="button">
                        <span class="material-symbols-outlined" aria-hidden="true">add_box</span>
                        Añadir nueva sección
                    </button>
                    <button class="icon-button module-action" data-open-edit-topic-dialog data-topic-id="<?= $topic->id ?>" data-topic-title="<?= htmlspecialchars($topic->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" type="button" aria-label="Editar tópico">
                        <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                    </button>
                    <button class="icon-button module-action module-action-danger" data-open-delete-topic-dialog data-topic-id="<?= $topic->id ?>" data-topic-title="<?= htmlspecialchars($topic->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" type="button" aria-label="Eliminar tópico">
                        <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                    </button>
                </div>
            </header>

            <div class="topic-section-empty" data-topic-empty-state <?= $sections === [] ? '' : 'hidden' ?>>
                <span class="editor-empty-icon material-symbols-outlined" aria-hidden="true">article</span>
                <h2>Este tópico no tiene secciones configuradas aún</h2>
                <p>Organizá el procedimiento en bloques claros de contenido para que el manual sea fácil de consultar.</p>
                <button class="button button-primary" data-open-new-section-editor data-topic-id="<?= $topic->id ?>" data-topic-title="<?= htmlspecialchars($topic->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" type="button">
                    <span class="material-symbols-outlined" aria-hidden="true">add</span>
                    Añadir nueva sección a este tópico
                </button>
            </div>

            <div class="section-list" data-section-list <?= $sections === [] ? 'hidden' : '' ?>>
                <?php foreach ($sections as $section): ?>
                    <?php $sectionContent = $section->content ?? $emptyTiptapDocument; ?>
                    <article class="section-card" data-section-card data-section-id="<?= $section->id ?>">
                        <header class="section-card-header">
                            <div class="section-card-title">
                                <span class="section-drag-handle material-symbols-outlined" aria-hidden="true" title="Reordenamiento próximamente">drag_indicator</span>
                                <span class="section-position"><?= $section->position + 1 ?></span>
                                <h2><?= htmlspecialchars($section->title ?? 'Sección sin título', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                            </div>
                            <div class="section-card-actions">
                                <button class="button button-quiet" data-open-section-editor type="button">
                                    <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                                    Editar
                                </button>
                                <button class="icon-button module-action-danger" data-open-delete-section-dialog data-section-id="<?= $section->id ?>" data-section-title="<?= htmlspecialchars($section->title ?? 'Sección sin título', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" type="button" aria-label="Eliminar sección">
                                    <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                </button>
                            </div>
                        </header>
                        <div class="section-read-content" data-section-read-content>
                            <div class="tiptap-content" data-tiptap-display data-section-content="<?= htmlspecialchars($sectionContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></div>
                        </div>
                        <form class="section-edit-form" action="/handbook/sections/update" method="post" data-section-edit-form data-loading-form hidden>
                            <input name="csrf_token" type="hidden" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                            <input name="section_id" type="hidden" value="<?= $section->id ?>">
                            <input name="content" type="hidden" data-section-content-input value="<?= htmlspecialchars($sectionContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                            <label>
                                Título de la sección
                                <input name="title" type="text" maxlength="255" value="<?= htmlspecialchars($section->title ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Título de la sección">
                            </label>
                            <div class="rich-text-toolbar" data-editor-toolbar>
                                <button type="button" data-editor-command="toggleBold" data-editor-active="bold" aria-label="Negrita" title="Negrita"><span class="material-symbols-outlined" aria-hidden="true">format_bold</span></button>
                                <button type="button" data-editor-command="toggleItalic" data-editor-active="italic" aria-label="Cursiva" title="Cursiva"><span class="material-symbols-outlined" aria-hidden="true">format_italic</span></button>
                                <select class="toolbar-select" data-editor-heading-level aria-label="Encabezado" title="Encabezado">
                                    <option value="" selected>Texto</option>
                                    <option value="1">Título 1</option>
                                    <option value="2">Título 2</option>
                                    <option value="3">Título 3</option>
                                    <option value="4">Título 4</option>
                                </select>
                                <select class="toolbar-select" data-editor-font-family aria-label="Familia tipográfica" title="Familia tipográfica">
                                    <option value="" selected>Tipografía</option>
                                    <option value="Inter, system-ui, sans-serif">Sans (Inter)</option>
                                    <option value="&quot;Source Serif 4&quot;, Georgia, serif">Serif</option>
                                    <option value="&quot;JetBrains Mono&quot;, ui-monospace, monospace">Mono</option>
                                </select>
                                <span class="toolbar-divider" aria-hidden="true"></span>
                                <button type="button" data-editor-command="toggleBulletList" data-editor-active="bulletList" aria-label="Lista con viñetas" title="Lista con viñetas"><span class="material-symbols-outlined" aria-hidden="true">format_list_bulleted</span></button>
                                <button type="button" data-editor-command="toggleOrderedList" data-editor-active="orderedList" aria-label="Lista numerada" title="Lista numerada"><span class="material-symbols-outlined" aria-hidden="true">format_list_numbered</span></button>
                                <button type="button" data-editor-command="toggleBlockquote" data-editor-active="blockquote" aria-label="Cita" title="Cita"><span class="material-symbols-outlined" aria-hidden="true">format_quote</span></button>
                                <button type="button" data-editor-command="toggleCodeBlock" data-editor-active="codeBlock" aria-label="Bloque de código" title="Bloque de código"><span class="material-symbols-outlined" aria-hidden="true">code</span></button>
                                <span class="toolbar-divider" aria-hidden="true"></span>
                                <button type="button" data-editor-link data-editor-active="link" aria-label="Insertar enlace" title="Insertar enlace"><span class="material-symbols-outlined" aria-hidden="true">link</span></button>
                                <button type="button" data-editor-image aria-label="Insertar imagen" title="Insertar imagen"><span class="material-symbols-outlined" aria-hidden="true">image</span></button>
                                <input type="file" accept="image/jpeg,image/png,image/webp" data-upload-input hidden>
                            </div>
                            <div class="inline-link-input" data-inline-link hidden>
                                <input type="text" data-inline-link-url placeholder="Pegá o escribí la URL..." aria-label="URL del enlace" autocomplete="off">
                            </div>
                            <div class="tiptap-editor" data-tiptap-editor></div>
                            <footer>
                                <button class="button button-muted" data-cancel-section-edit type="button">Cancelar</button>
                                <button class="button button-primary" data-loading-submit type="submit"><span data-loading-label>Guardar cambios</span></button>
                            </footer>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        </article>
    <?php endforeach; ?>

    <article class="section-card section-new-editor" data-new-section-editor hidden>
        <header class="section-card-header">
            <div class="section-card-title">
                <span class="section-position material-symbols-outlined" aria-hidden="true">add</span>
                <h2>Nueva sección</h2>
            </div>
        </header>
        <form action="/handbook/sections" method="post" data-new-section-form data-loading-form>
            <input name="csrf_token" type="hidden" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <input name="topic_id" type="hidden" data-new-section-topic-id>
            <input name="content" type="hidden" data-section-content-input value="<?= htmlspecialchars($emptyTiptapDocument, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            <label>
                Título de la sección
                <input name="title" type="text" maxlength="255" placeholder="Ej.: Procedimiento de atención">
            </label>
            <div class="rich-text-toolbar" data-editor-toolbar>
                <button type="button" data-editor-command="toggleBold" data-editor-active="bold" aria-label="Negrita" title="Negrita"><span class="material-symbols-outlined" aria-hidden="true">format_bold</span></button>
                <button type="button" data-editor-command="toggleItalic" data-editor-active="italic" aria-label="Cursiva" title="Cursiva"><span class="material-symbols-outlined" aria-hidden="true">format_italic</span></button>
                <select class="toolbar-select" data-editor-heading-level aria-label="Encabezado" title="Encabezado">
                    <option value="" selected>Texto</option>
                    <option value="1">Título 1</option>
                    <option value="2">Título 2</option>
                    <option value="3">Título 3</option>
                    <option value="4">Título 4</option>
                </select>
                <select class="toolbar-select" data-editor-font-family aria-label="Familia tipográfica" title="Familia tipográfica">
                    <option value="" selected>Tipografía</option>
                    <option value="Inter, system-ui, sans-serif">Sans (Inter)</option>
                    <option value="&quot;Source Serif 4&quot;, Georgia, serif">Serif</option>
                    <option value="&quot;JetBrains Mono&quot;, ui-monospace, monospace">Mono</option>
                </select>
                <span class="toolbar-divider" aria-hidden="true"></span>
                <button type="button" data-editor-command="toggleBulletList" data-editor-active="bulletList" aria-label="Lista con viñetas" title="Lista con viñetas"><span class="material-symbols-outlined" aria-hidden="true">format_list_bulleted</span></button>
                <button type="button" data-editor-command="toggleOrderedList" data-editor-active="orderedList" aria-label="Lista numerada" title="Lista numerada"><span class="material-symbols-outlined" aria-hidden="true">format_list_numbered</span></button>
                <button type="button" data-editor-command="toggleBlockquote" data-editor-active="blockquote" aria-label="Cita" title="Cita"><span class="material-symbols-outlined" aria-hidden="true">format_quote</span></button>
                <button type="button" data-editor-command="toggleCodeBlock" data-editor-active="codeBlock" aria-label="Bloque de código" title="Bloque de código"><span class="material-symbols-outlined" aria-hidden="true">code</span></button>
                <span class="toolbar-divider" aria-hidden="true"></span>
                <button type="button" data-editor-link data-editor-active="link" aria-label="Insertar enlace" title="Insertar enlace"><span class="material-symbols-outlined" aria-hidden="true">link</span></button>
                <button type="button" data-editor-image disabled aria-label="Insertar imagen (guardá la sección antes)" title="Guardá la sección antes de insertar imágenes"><span class="material-symbols-outlined" aria-hidden="true">image</span></button>
            </div>
            <div class="inline-link-input" data-inline-link hidden>
                <input type="text" data-inline-link-url placeholder="Pegá o escribí la URL..." aria-label="URL del enlace" autocomplete="off">
            </div>
            <div class="tiptap-editor" data-tiptap-editor></div>
            <footer>
                <button class="button button-muted" data-cancel-new-section type="button">Cancelar</button>
                <button class="button button-primary" data-loading-submit type="submit"><span data-loading-label>Guardar sección</span></button>
            </footer>
        </form>
    </article>

</section>
