import { createModuleSelectionState, createTopicSelectionState } from './handbook-editor-state.js';

let Editor;
let Link;
let Image;
let TextStyle;
let FontFamily;
let StarterKit;
const tiptapReady = Promise.all([
    import('https://esm.sh/@tiptap/core@2.11.5'),
    import('https://esm.sh/@tiptap/extension-link@2.11.5'),
    import('https://esm.sh/@tiptap/extension-image@2.11.5'),
    import('https://esm.sh/@tiptap/extension-text-style@2.11.5'),
    import('https://esm.sh/@tiptap/extension-font-family@2.11.5'),
    import('https://esm.sh/@tiptap/starter-kit@2.11.5'),
]).then(([core, link, image, textStyle, fontFamily, starterKit]) => {
    Editor = core.Editor;
    Link = link.default;
    Image = image.default;
    TextStyle = textStyle.default;
    FontFamily = fontFamily.default;
    StarterKit = starterKit.default;
}).catch(() => {
    console.error('No fue posible cargar el editor de texto.');
});

function buildExtensions() {
    return [
        StarterKit,
        Link.configure({
            openOnClick: false,
            autolink: true,
            linkOnPaste: true,
            HTMLAttributes: { rel: 'noopener noreferrer nofollow', target: '_blank' },
        }),
        Image,
        TextStyle,
        FontFamily,
    ];
}

const emptyDocument = { type: 'doc', content: [{ type: 'paragraph' }] };
const moduleDialog = document.querySelector('[data-module-dialog]');
const topicDialog = document.querySelector('[data-topic-dialog]');
const editModuleDialog = document.querySelector('[data-edit-module-dialog]');
const deleteModuleDialog = document.querySelector('[data-delete-module-dialog]');
const deleteSectionDialog = document.querySelector('[data-delete-section-dialog]');
const editTopicDialog = document.querySelector('[data-edit-topic-dialog]');
const deleteTopicDialog = document.querySelector('[data-delete-topic-dialog]');
const search = document.querySelector('#handbook-search');
const searchField = search?.closest('.search-field');
const searchDialog = document.querySelector('[data-search-dialog]');
const searchInput = document.querySelector('[data-search-input]');
const searchResultsContainer = document.querySelector('[data-search-results]');
const searchPlaceholder = document.querySelector('[data-search-placeholder]');
const moduleButtons = document.querySelectorAll('[data-select-module]');
const topicButtons = document.querySelectorAll('[data-select-topic]');
const topicToggles = document.querySelectorAll('[data-toggle-module-topics]');
const handbookOverview = document.querySelector('[data-handbook-overview]');
const topicContent = document.querySelector('[data-topic-content]');
const topicPanels = document.querySelectorAll('[data-topic-panel]');
const noTopicSelected = document.querySelector('[data-no-topic-selected]');
const emptyModuleState = document.querySelector('[data-empty-module-state]');
const selectionStatus = document.querySelector('[data-selection-status]');
const headerTopicAction = document.querySelector('[data-header-topic-action]');
const topicModuleId = document.querySelector('[data-topic-module-id]');
const editModuleId = document.querySelector('[data-edit-module-id]');
const editModuleTitle = document.querySelector('[data-edit-module-title]');
const editModuleDescription = document.querySelector('[data-edit-module-description]');
const deleteModuleId = document.querySelector('[data-delete-module-id]');
const deleteModuleTitle = document.querySelector('[data-delete-module-title]');
const newSectionEditor = document.querySelector('[data-new-section-editor]');
const newSectionForm = document.querySelector('[data-new-section-form]');
const newSectionTopicId = document.querySelector('[data-new-section-topic-id]');
const deleteSectionId = document.querySelector('[data-delete-section-id]');
const deleteSectionTitle = document.querySelector('[data-delete-section-title]');
const editTopicId = document.querySelector('[data-edit-topic-id]');
const editTopicTitle = document.querySelector('[data-edit-topic-title]');
const deleteTopicId = document.querySelector('[data-delete-topic-id]');
const deleteTopicTitle = document.querySelector('[data-delete-topic-title]');
const moduleSelection = createModuleSelectionState();
const topicSelection = createTopicSelectionState();
const formEditors = new WeakMap();
const readOnlyEditors = [];
let currentModule = null;
let currentTopic = null;

function openDialog(dialog, input) {
    dialog?.showModal();
    input?.focus();
}

const searchIconMap = { module: 'folder', topic: 'description', section: 'article' };
let searchTimer = null;
let activeSearchRequest = null;
let searchResultItems = [];
let searchActiveIndex = -1;

function openSearchDialog() {
    searchDialog?.showModal();
    searchInput?.focus();
}

function closeSearchDialog() {
    clearTimeout(searchTimer);
    searchDialog?.close();
    if (activeSearchRequest) {
        activeSearchRequest.abort();
        activeSearchRequest = null;
    }
    searchInput.value = '';
    renderSearchResults(null);
    searchResultItems = [];
    searchActiveIndex = -1;
}

function resolveResultPath(result) {
    if (result.type === 'module') {
        return ['Módulo'];
    }

    if (result.type === 'topic') {
        const topicBtn = document.querySelector('[data-select-topic][data-topic-id="' + result.id + '"]');
        return ['Módulo', topicBtn?.dataset.moduleTitle];
    }

    if (result.type === 'section') {
        const card = document.querySelector('[data-section-card][data-section-id="' + result.id + '"]');
        const panel = card?.closest('[data-topic-panel]');
        const topicId = panel?.dataset.topicId;
        const topicBtn = topicId ? document.querySelector('[data-select-topic][data-topic-id="' + topicId + '"]') : null;
        return ['Módulo', topicBtn?.dataset.moduleTitle, topicBtn?.dataset.topicTitle];
    }

    return [];
}

function renderSearchPlaceholder(mode, query) {
    if (mode === 'results') {
        searchPlaceholder.classList.remove('search-placeholder-empty');
        searchPlaceholder.hidden = true;
        return;
    }

    const isEmpty = mode === 'empty';
    searchPlaceholder.classList.toggle('search-placeholder-empty', isEmpty);
    searchPlaceholder.hidden = false;

    const icon = isEmpty ? 'search_off' : 'manage_search';
    const text = isEmpty
        ? '<strong>Sin resultados</strong><span>No se encontraron coincidencias para &quot;' + escapeHtml(query) + '&quot;.</span>'
        : mode === 'loading'
            ? '<span>Buscando…</span>'
            : '<span>Escribí al menos dos caracteres para buscar.</span>';

    searchPlaceholder.innerHTML =
        '<span class="material-symbols-outlined search-placeholder-icon" aria-hidden="true">' + icon + '</span>' +
        text;
}

function renderSearchResults(state) {
    const existingItems = searchResultsContainer.querySelectorAll('.search-result-item');
    existingItems.forEach((item) => item.remove());

    searchResultItems = [];
    searchActiveIndex = -1;

    if (!state) {
        renderSearchPlaceholder('hint');
        return;
    }

    if (state.loading) {
        renderSearchPlaceholder('loading');
        return;
    }

    if (state.results.length === 0) {
        renderSearchPlaceholder('empty', state.query);
        return;
    }

    renderSearchPlaceholder('results');

    for (const result of state.results) {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'search-result-item';
        item.dataset.searchResultType = result.type;
        item.dataset.searchResultId = result.id;
        item.dataset.searchResultParentId = result.parentId ?? '';

        const icon = searchIconMap[result.type] ?? 'description';
        const pathParts = resolveResultPath(result).filter(Boolean);
        const pathHtml = pathParts.length > 0
            ? '<span class="search-result-path">' + escapeHtml(pathParts.join(' › ')) + '</span>'
            : '';
        const excerptHtml = result.excerpt ? '<p class="search-result-excerpt">' + escapeHtml(result.excerpt) + '</p>' : '';

        item.innerHTML =
            '<span class="material-symbols-outlined search-result-icon" aria-hidden="true">' + icon + '</span>' +
            '<div class="search-result-body">' +
                pathHtml +
                '<span class="search-result-title">' + escapeHtml(result.title) + '</span>' +
                excerptHtml +
            '</div>';

        searchResultsContainer.appendChild(item);
        searchResultItems.push(item);
    }

    if (searchResultItems.length > 0) {
        updateSearchActiveIndex(0);
    }
}

function updateSearchActiveIndex(newIndex) {
    if (searchResultItems.length === 0) return;
    searchActiveIndex = ((newIndex % searchResultItems.length) + searchResultItems.length) % searchResultItems.length;
    searchResultItems.forEach((item, i) => item.classList.toggle('is-active', i === searchActiveIndex));
    searchResultItems[searchActiveIndex]?.scrollIntoView({ block: 'nearest' });
}

function escapeHtml(str) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' };
    return str.replace(/[&<>"]/g, (c) => map[c]);
}

async function executeSearch(query) {
    if (activeSearchRequest) {
        activeSearchRequest.abort();
    }

    const trimmed = query.trim();
    if (trimmed.length < 2) {
        renderSearchResults(null);
        return;
    }

    renderSearchResults({ loading: true, results: [], query: trimmed });

    const controller = new AbortController();
    activeSearchRequest = controller;

    try {
        const res = await fetch('/handbook/search?q=' + encodeURIComponent(trimmed), {
            headers: { Accept: 'application/json' },
            signal: controller.signal,
        });
        const payload = await res.json();
        renderSearchResults({ loading: false, results: payload.ok ? payload.results : [], query: trimmed });
    } catch (err) {
        if (err?.name !== 'AbortError') {
            renderSearchResults({ loading: false, results: [], query: trimmed });
        }
    } finally {
        activeSearchRequest = null;
    }
}

function navigateSearchResult(result) {
    closeSearchDialog();

    if (result.type === 'module') {
        topicSelection.clear();
        moduleSelection.select({ id: String(result.id), title: result.title });
        setModuleTopicsExpanded(String(result.id), true);
        return;
    }

    if (result.type === 'topic') {
        const topicBtn = document.querySelector('[data-select-topic][data-topic-id="' + result.id + '"]');
        if (topicBtn) {
            moduleSelection.select({ id: topicBtn.dataset.moduleId, title: topicBtn.dataset.moduleTitle });
            setModuleTopicsExpanded(topicBtn.dataset.moduleId, true);
            topicSelection.select({ id: String(result.id), title: result.title });
        }
        return;
    }

    if (result.type === 'section') {
        const card = document.querySelector('[data-section-card][data-section-id="' + result.id + '"]');
        if (!card) return;
        const panel = card.closest('[data-topic-panel]');
        if (!panel) return;
        const topicId = panel.dataset.topicId;
        const topicBtn = document.querySelector('[data-select-topic][data-topic-id="' + topicId + '"]');
        if (topicBtn) {
            moduleSelection.select({ id: topicBtn.dataset.moduleId, title: topicBtn.dataset.moduleTitle });
            setModuleTopicsExpanded(topicBtn.dataset.moduleId, true);
            topicSelection.select({ id: topicId, title: topicBtn.dataset.topicTitle });
            requestAnimationFrame(() => {
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                card.classList.add('is-search-highlighted');
                setTimeout(() => card.classList.remove('is-search-highlighted'), 2200);
            });
        }
    }
}

function parseDocument(value) {
    try {
        const document = JSON.parse(value);

        return document?.type === 'doc' ? document : emptyDocument;
    } catch {
        return emptyDocument;
    }
}

function setModuleTopicsExpanded(moduleId, isExpanded) {
    const row = [...document.querySelectorAll('[data-module-row]')]
        .find((item) => item.dataset.moduleId === moduleId);
    const toggle = [...topicToggles].find((button) => button.closest('[data-module-row]') === row);
    const collapse = row?.querySelector('[data-topic-collapse]');

    if (!row || !toggle || !collapse) {
        return;
    }

    row.classList.toggle('is-topics-expanded', isExpanded);
    toggle.setAttribute('aria-expanded', String(isExpanded));
    toggle.setAttribute('aria-label', `${isExpanded ? 'Ocultar' : 'Mostrar'} tópicos de ${row.querySelector('[data-select-module]').dataset.moduleTitle}`);
    row.querySelector('[data-module-folder-icon]').textContent = isExpanded ? 'folder_open' : 'folder';
    collapse.setAttribute('aria-hidden', String(!isExpanded));
}

function parseJsonAttr(value, fallback) {
    if (!value) {
        return fallback;
    }

    try {
        return JSON.parse(value);
    } catch {
        return fallback;
    }
}

let savedLinkSelection = null;
async function ensureEditor(form) {
    const existingEditor = formEditors.get(form);
    if (existingEditor) {
        return existingEditor;
    }

    await tiptapReady;
    if (!Editor || !StarterKit || !Link || !Image || !TextStyle || !FontFamily) {
        return null;
    }

    const contentInput = form.querySelector('[data-section-content-input]');
    const editor = new Editor({
        element: form.querySelector('[data-tiptap-editor]'),
        extensions: buildExtensions(),
        content: parseDocument(contentInput.value),
        editorProps: {
            attributes: {
                class: 'tiptap-editable-content',
            },
        },
    });

    formEditors.set(form, editor);

    const fontSelect = form.querySelector('[data-editor-font-family]');
    const headingSelect = form.querySelector('[data-editor-heading-level]');
    const inlineLinkContainer = form.querySelector('[data-inline-link]');
    const inlineLinkUrl = form.querySelector('[data-inline-link-url]');
    const linkButton = form.querySelector('[data-editor-link]');
    const toolbarButtons = form.querySelectorAll('[data-editor-command], [data-editor-link], [data-editor-image]');

    function syncToolbarState() {
        toolbarButtons.forEach((button) => {
            if (button.dataset.editorImage !== undefined) {
                return;
            }

            const activeName = button.dataset.editorActive;
            if (!activeName) {
                return;
            }

            const isActive = editor.isActive(activeName);
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });

        if (fontSelect) {
            fontSelect.value = editor.getAttributes('textStyle').fontFamily ?? '';
        }

        if (headingSelect) {
            const attrs = editor.getAttributes('heading');
            headingSelect.value = attrs.level ? String(attrs.level) : '';
        }
    }

    form.querySelectorAll('[data-editor-command]').forEach((button) => {
        button.addEventListener('click', () => {
            const command = button.dataset.editorCommand;
            const args = parseJsonAttr(button.dataset.editorArgs, null);
            const chain = editor.chain().focus();

            if (typeof chain[command] === 'function') {
                (args ? chain[command](args) : chain[command]()).run();
            }
        });
    });

    headingSelect?.addEventListener('change', () => {
        const level = parseInt(headingSelect.value, 10);

        if (Number.isNaN(level)) {
            editor.chain().focus().setParagraph().run();
        } else {
            editor.chain().focus().toggleHeading({ level }).run();
        }
    });

    linkButton?.addEventListener('click', () => {
        if (!inlineLinkContainer || !inlineLinkUrl) {
            return;
        }

        if (inlineLinkContainer.hidden) {
            savedLinkSelection = { from: editor.state.selection.from, to: editor.state.selection.to };
            inlineLinkUrl.value = editor.isActive('link') ? editor.getAttributes('link').href ?? '' : '';
            inlineLinkContainer.hidden = false;
            inlineLinkUrl.focus();
            inlineLinkUrl.select();
        } else {
            inlineLinkContainer.hidden = true;
            editor.commands.focus();
            savedLinkSelection = null;
        }
    });

    inlineLinkUrl?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            const href = inlineLinkUrl.value.trim();

            if (href === '' && editor.isActive('link')) {
                if (savedLinkSelection) {
                    editor.chain().focus().setTextSelection(savedLinkSelection).run();
                }
                editor.chain().focus().extendMarkRange('link').unsetLink().run();
            } else if (href !== '') {
                if (savedLinkSelection) {
                    editor.chain().focus().setTextSelection(savedLinkSelection).run();
                }
                editor.chain().focus().extendMarkRange('link').setLink({ href }).run();
            }

            inlineLinkContainer.hidden = true;
            savedLinkSelection = null;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            inlineLinkContainer.hidden = true;
            editor.commands.focus();
            savedLinkSelection = null;
        }
    });

    form.querySelector('[data-editor-image]')?.addEventListener('click', () => {
        const uploadInput = form.querySelector('[data-upload-input]');
        if (uploadInput) {
            uploadInput.click();
        }
    });

    fontSelect?.addEventListener('change', () => {
        if (fontSelect.value === '') {
            editor.chain().focus().unsetFontFamily().run();
        } else {
            editor.chain().focus().setFontFamily(fontSelect.value).run();
        }
    });

    editor.on('transaction', syncToolbarState);
    syncToolbarState();

    return editor;
}

function prepareEditorSubmit(form) {
    const editor = formEditors.get(form);
    const contentInput = form.querySelector('[data-section-content-input]');

    if (editor && contentInput) {
        contentInput.value = JSON.stringify(editor.getJSON());
    }
}

function bindLoadingState(form) {
    form.addEventListener('submit', (event) => {
        prepareEditorSubmit(form);

        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }

        form.dataset.submitting = 'true';
        form.setAttribute('aria-busy', 'true');
        form.querySelector('[data-loading-submit]')?.classList.add('is-loading');
        form.querySelector('[data-loading-submit]')?.setAttribute('disabled', '');
        form.querySelector('[data-loading-label]').textContent = form.querySelector('.button-danger') ? 'Eliminando...' : 'Guardando...';
        form.querySelectorAll('[data-close-dialog], [data-cancel-section-edit], [data-cancel-new-section]').forEach((button) => button.setAttribute('disabled', ''));
    });
}

document.querySelectorAll('[data-loading-form]').forEach(bindLoadingState);

tiptapReady.then(() => {
    if (!Editor || !StarterKit || !Link || !Image || !TextStyle || !FontFamily) {
        return;
    }

    document.querySelectorAll('[data-tiptap-display]').forEach((element) => {
        readOnlyEditors.push(new Editor({
            element,
            extensions: buildExtensions(),
            content: parseDocument(element.dataset.sectionContent),
            editable: false,
            editorProps: { attributes: { class: 'tiptap-rendered-content' } },
        }));
    });
});

document.querySelectorAll('[data-open-module-dialog]').forEach((button) => {
    button.addEventListener('click', () => openDialog(moduleDialog, document.querySelector('#module-name')));
});

headerTopicAction?.addEventListener('click', () => openDialog(topicDialog, document.querySelector('#topic-name')));

document.querySelectorAll('[data-open-topic-dialog]').forEach((button) => {
    button.addEventListener('click', () => openDialog(topicDialog, document.querySelector('#topic-name')));
});

document.querySelectorAll('[data-close-dialog]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
});

document.querySelectorAll('[data-upload-input]').forEach((input) => {
    input.addEventListener('change', async () => {
        const file = input.files[0];

        if (!file) {
            return;
        }

        const form = input.closest('form');
        const editor = formEditors.get(form);
        const sectionId = form.closest('[data-section-card]')?.dataset.sectionId;
        const csrfToken = form.querySelector('input[name="csrf_token"]')?.value;

        if (!editor || !sectionId || !csrfToken) {
            return;
        }

        const imageButton = form.querySelector('[data-editor-image]');
        const body = new FormData();
        body.append('image', file);
        body.append('csrf_token', csrfToken);

        imageButton?.classList.add('is-loading');
        imageButton?.setAttribute('disabled', '');

        try {
            const response = await fetch(`/sections/${sectionId}/media`, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body,
            });
            const payload = await response.json();

            if (!response.ok || payload.ok !== true) {
                throw new Error('La subida de la imagen falló.');
            }

            const alt = file.name.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' ').trim();
            editor.chain().focus().setImage({ src: payload.url, alt: alt || null }).run();
        } catch {
            window.alert('No fue posible subir la imagen. Verificá que sea JPEG, PNG o WebP de hasta 5 MB.');
        } finally {
            imageButton?.classList.remove('is-loading');
            imageButton?.removeAttribute('disabled');
            input.value = '';
        }
    });
});

moduleButtons.forEach((button) => {
    button.addEventListener('click', () => {
        topicSelection.clear();
        moduleSelection.select({ id: button.dataset.moduleId, title: button.dataset.moduleTitle });
        setModuleTopicsExpanded(button.dataset.moduleId, true);
    });
});

topicToggles.forEach((button) => {
    button.addEventListener('click', () => {
        const row = button.closest('[data-module-row]');

        setModuleTopicsExpanded(row.dataset.moduleId, button.getAttribute('aria-expanded') !== 'true');
    });
});

topicButtons.forEach((button) => {
    button.addEventListener('click', () => {
        moduleSelection.select({ id: button.dataset.moduleId, title: button.dataset.moduleTitle });
        setModuleTopicsExpanded(button.dataset.moduleId, true);
        topicSelection.select({ id: button.dataset.topicId, title: button.dataset.topicTitle });
    });
});

document.querySelectorAll('[data-open-edit-module-dialog]').forEach((button) => {
    button.addEventListener('click', () => {
        editModuleId.value = button.dataset.moduleId;
        editModuleTitle.value = button.dataset.moduleTitle;
        editModuleDescription.value = button.dataset.moduleDescription ?? '';
        openDialog(editModuleDialog, editModuleTitle);
    });
});

document.querySelectorAll('[data-open-delete-module-dialog]').forEach((button) => {
    button.addEventListener('click', () => {
        deleteModuleId.value = button.dataset.moduleId;
        deleteModuleTitle.textContent = button.dataset.moduleTitle;
        openDialog(deleteModuleDialog);
    });
});

document.querySelectorAll('[data-open-edit-topic-dialog]').forEach((button) => {
    button.addEventListener('click', () => {
        editTopicId.value = button.dataset.topicId;
        editTopicTitle.value = button.dataset.topicTitle;
        openDialog(editTopicDialog, editTopicTitle);
    });
});

document.querySelectorAll('[data-open-delete-topic-dialog]').forEach((button) => {
    button.addEventListener('click', () => {
        deleteTopicId.value = button.dataset.topicId;
        deleteTopicTitle.textContent = button.dataset.topicTitle;
        openDialog(deleteTopicDialog);
    });
});

document.querySelectorAll('[data-open-new-section-editor]').forEach((button) => {
    button.addEventListener('click', async () => {
        newSectionTopicId.value = button.dataset.topicId;
        newSectionForm.reset();
        newSectionTopicId.value = button.dataset.topicId;
        newSectionForm.querySelector('[data-section-content-input]').value = JSON.stringify(emptyDocument);
        const editor = await ensureEditor(newSectionForm);
        editor?.commands.setContent(emptyDocument);
        newSectionEditor.hidden = false;
        newSectionForm.querySelector('input[name="title"]').focus();
    });
});

document.querySelector('[data-cancel-new-section]')?.addEventListener('click', () => {
    newSectionEditor.hidden = true;
});

document.querySelectorAll('[data-open-section-editor]').forEach((button) => {
    button.addEventListener('click', async () => {
        const card = button.closest('[data-section-card]');
        const form = card.querySelector('[data-section-edit-form]');

        card.querySelector('[data-section-read-content]').hidden = true;
        form.hidden = false;
        await ensureEditor(form);
    });
});

document.querySelectorAll('[data-cancel-section-edit]').forEach((button) => {
    button.addEventListener('click', () => {
        const card = button.closest('[data-section-card]');

        card.querySelector('[data-section-read-content]').hidden = false;
        card.querySelector('[data-section-edit-form]').hidden = true;
    });
});

document.querySelectorAll('[data-open-delete-section-dialog]').forEach((button) => {
    button.addEventListener('click', () => {
        deleteSectionId.value = button.dataset.sectionId;
        deleteSectionTitle.textContent = button.dataset.sectionTitle;
        openDialog(deleteSectionDialog);
    });
});

moduleSelection.subscribe((module) => {
    currentModule = module;
    moduleButtons.forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.moduleId === module?.id));
    });

    emptyModuleState.hidden = module !== null;
    selectionStatus.textContent = module?.title ?? 'Sin selección';
    topicModuleId.value = module?.id ?? '';
    headerTopicAction.hidden = module === null;
    headerTopicAction.disabled = module === null;
    updateTopicContent();
});

topicSelection.subscribe((topic) => {
    currentTopic = topic;
    topicButtons.forEach((button) => {
        button.setAttribute('aria-pressed', String(button.dataset.topicId === topic?.id));
    });

    updateTopicContent();
});

function updateTopicContent() {
    const hasModule = currentModule !== null;
    const hasTopic = currentTopic !== null;

    handbookOverview.hidden = hasModule;
    topicContent.hidden = !hasModule;
    noTopicSelected.hidden = !hasModule || hasTopic;
    topicContent.dataset.state = hasTopic ? 'topic-selected' : hasModule ? 'module-selected' : 'none';
    newSectionEditor.hidden = true;
    topicPanels.forEach((panel) => {
        panel.hidden = !hasTopic || panel.dataset.topicId !== currentTopic.id;
    });
}

const initialTopicId = document.body.dataset.initialSelectedTopicId;
const initialTopic = [...topicButtons].find((button) => button.dataset.topicId === initialTopicId);
const initialModuleId = document.body.dataset.initialSelectedModuleId;
const initialModule = [...moduleButtons].find((button) => button.dataset.moduleId === initialModuleId);

if (initialTopic) {
    initialTopic.click();
} else if (initialModule) {
    initialModule.click();
}

document.querySelector('[data-preview]')?.addEventListener('click', () => {
    window.alert('La previsualización estará disponible cuando existan módulos publicados.');
});

window.addEventListener('keydown', (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        if (searchDialog?.open) {
            closeSearchDialog();
        } else {
            openSearchDialog();
        }
    }
});

searchField?.addEventListener('click', (event) => {
    event.preventDefault();
    openSearchDialog();
});

searchDialog?.addEventListener('click', (event) => {
    if (event.target === searchDialog) {
        closeSearchDialog();
    }
});

searchDialog?.addEventListener('cancel', (event) => {
    event.preventDefault();
    closeSearchDialog();
});

searchInput?.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => executeSearch(searchInput.value), 180);
});

searchInput?.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        updateSearchActiveIndex(searchActiveIndex + 1);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        updateSearchActiveIndex(searchActiveIndex - 1);
    } else if (event.key === 'Enter') {
        event.preventDefault();
        const item = searchResultItems[searchActiveIndex];
        if (item) {
            navigateSearchResult({
                type: item.dataset.searchResultType,
                id: item.dataset.searchResultId,
                title: item.querySelector('.search-result-title')?.textContent ?? '',
                parentId: item.dataset.searchResultParentId || null,
            });
        }
    } else if (event.key === 'Escape') {
        event.preventDefault();
        closeSearchDialog();
    }
});

searchResultsContainer?.addEventListener('click', (event) => {
    const item = event.target.closest('.search-result-item');
    if (!item) return;
    navigateSearchResult({
        type: item.dataset.searchResultType,
        id: item.dataset.searchResultId,
        title: item.querySelector('.search-result-title')?.textContent ?? '',
        parentId: item.dataset.searchResultParentId || null,
    });
});

window.setTimeout(() => {
    document.querySelectorAll('.flash-message.flash-success').forEach((message) => {
        message.classList.add('is-dismissing');
        window.setTimeout(() => message.remove(), 250);
    });
}, 5000);
