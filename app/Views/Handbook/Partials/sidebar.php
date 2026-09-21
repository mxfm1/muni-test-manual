<aside class="module-sidebar" aria-labelledby="modules-heading">
    <div class="module-sidebar-heading">
        <span class="material-symbols-outlined module-heading-icon" aria-hidden="true">auto_stories</span>
        <div>
            <h2 id="modules-heading">Módulos del manual</h2>
            <p>Estructura de contenidos</p>
        </div>
        <span class="module-count"><?= $moduleCount ?> módulos</span>
    </div>
    <?php if ($modules === []): ?>
        <div class="module-empty">
            <span class="empty-icon material-symbols-outlined" aria-hidden="true">folder_open</span>
            <h3>No hay módulos registrados</h3>
            <p>Creá tu primer módulo para comenzar a estructurar los tópicos del manual.</p>
            <button class="button button-outline" type="button" data-open-module-dialog>
                <span class="material-symbols-outlined" aria-hidden="true">add_circle</span>
                Crear módulo
            </button>
        </div>
    <?php else: ?>
        <ul class="module-list">
            <?php foreach ($modules as $module): ?>
                <?php $moduleTopics = $topicsByModule[$module->id]; ?>
                <li class="module-list-item" data-module-row data-module-id="<?= $module->id ?>">
                    <div class="module-list-row">
                        <button
                            class="module-select-button"
                            data-select-module
                            data-module-id="<?= $module->id ?>"
                            data-module-title="<?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            type="button"
                            aria-pressed="false"
                        >
                            <span class="material-symbols-outlined" data-module-folder-icon aria-hidden="true">folder</span>
                            <span><?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                        </button>
                        <?php if ($moduleTopics !== []): ?>
                            <button
                                class="icon-button module-expand-action"
                                data-toggle-module-topics
                                type="button"
                                aria-expanded="false"
                                aria-controls="module-topics-<?= $module->id ?>"
                                aria-label="Mostrar tópicos de <?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            >
                                <span class="material-symbols-outlined" aria-hidden="true">expand_more</span>
                            </button>
                        <?php endif; ?>
                        <span class="module-row-actions">
                            <button
                                class="icon-button module-action"
                                data-open-edit-module-dialog
                                data-module-id="<?= $module->id ?>"
                                data-module-title="<?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                data-module-description="<?= htmlspecialchars($module->description ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                type="button"
                                aria-label="Editar <?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            >
                                <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                            </button>
                            <button
                                class="icon-button module-action module-action-danger"
                                data-open-delete-module-dialog
                                data-module-id="<?= $module->id ?>"
                                data-module-title="<?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                type="button"
                                aria-label="Eliminar <?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                            >
                                <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                            </button>
                        </span>
                    </div>
                    <?php if ($moduleTopics !== []): ?>
                        <div class="topic-collapse" data-topic-collapse aria-hidden="true">
                            <div>
                                <ul id="module-topics-<?= $module->id ?>" class="topic-list">
                                    <?php foreach ($moduleTopics as $topic): ?>
                                        <li>
                                            <button
                                                class="topic-select-button"
                                                data-select-topic
                                                data-topic-id="<?= $topic->id ?>"
                                                data-topic-title="<?= htmlspecialchars($topic->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                data-module-id="<?= $module->id ?>"
                                                data-module-title="<?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                type="button"
                                                aria-pressed="false"
                                            >
                                                <span class="material-symbols-outlined" aria-hidden="true">description</span>
                                                <span><?= htmlspecialchars($topic->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="module-sidebar-footer">
            <button class="button button-outline" type="button" data-open-module-dialog>
                <span class="material-symbols-outlined" aria-hidden="true">add_circle</span>
                Crear módulo
            </button>
        </div>
    <?php endif; ?>
</aside>
