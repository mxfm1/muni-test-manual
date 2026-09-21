(() => {
    'use strict';

    // ── Referencias ───────────────────────────────────────────
    const searchDialog = document.querySelector('[data-search-dialog]');
    const searchInput = document.querySelector('[data-search-input]');
    const searchResultsContainer = document.querySelector('[data-search-results]');
    const searchPlaceholder = document.querySelector('[data-search-placeholder]');
    const searchTrigger = document.querySelector('[data-search-trigger]');
    const filterInput = document.querySelector('[data-topic-filter]');
    const topicItems = document.querySelectorAll('[data-topic-item]');

    // ── Búsqueda global ───────────────────────────────────────
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

    function escapeHtml(str) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' };
        return str.replace(/[&<>"]/g, (c) => map[c]);
    }

    function resolveResultPath(result) {
        if (result.type === 'module') {
            return ['Módulo'];
        }
        if (result.type === 'topic') {
            return ['Módulo', result.moduleTitle];
        }
        if (result.type === 'section') {
            return ['Módulo', result.moduleTitle, result.topicTitle];
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
        searchResultsContainer.querySelectorAll('.search-result-item').forEach((item) => item.remove());

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

            item.addEventListener('click', () => navigateSearchResult(result));

            searchResultsContainer.appendChild(item);
            searchResultItems.push(item);
        }

        if (searchResultItems.length > 0) {
            updateSearchActiveIndex(0);
        }
    }

    function updateSearchActiveIndex(newIndex) {
        if (searchResultItems.length === 0) {
            return;
        }
        searchActiveIndex = ((newIndex % searchResultItems.length) + searchResultItems.length) % searchResultItems.length;
        searchResultItems.forEach((item, i) => item.classList.toggle('is-active', i === searchActiveIndex));
        searchResultItems[searchActiveIndex]?.scrollIntoView({ block: 'nearest' });
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

    function topicResultUrl(result) {
        if (result.type === 'module') {
            return '/handbook/' + result.id;
        }
        if (result.type === 'topic') {
            return '/handbook/' + result.moduleId + '/topics/' + result.id;
        }
        if (result.type === 'section') {
            return '/handbook/' + result.moduleId + '/topics/' + result.parentId + '#section-' + result.id;
        }
        return null;
    }

    function navigateSearchResult(result) {
        closeSearchDialog();
        const url = topicResultUrl(result);
        if (url) {
            window.location.href = url;
        }
    }

    if (searchTrigger) {
        searchTrigger.addEventListener('click', openSearchDialog);
    }

    searchInput?.addEventListener('input', (event) => {
        clearTimeout(searchTimer);
        const query = event.target.value.trim();
        searchTimer = window.setTimeout(() => executeSearch(query), 180);
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
            const active = searchResultItems[searchActiveIndex];
            if (active) {
                active.click();
            }
        } else if (event.key === 'Escape') {
            event.preventDefault();
            closeSearchDialog();
        }
    });

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            openSearchDialog();
        }
    });

    searchDialog?.addEventListener('close', () => {
        searchInput.value = '';
        renderSearchResults(null);
        searchResultItems = [];
        searchActiveIndex = -1;
    });

    // ── Filtro de tópicos del sidebar ────────────────────────
    filterInput?.addEventListener('input', (event) => {
        const query = event.target.value.trim().toLowerCase();

        topicItems.forEach((item) => {
            const haystack = (item.dataset.topicQuery ?? item.dataset.topicTitle ?? '').toLowerCase();
            item.classList.toggle('is-hidden', query !== '' && !haystack.includes(query));
        });
    });

    // ── Acciones de cabecera ─────────────────────────────────
    document.querySelector('[data-copy-link]')?.addEventListener('click', (event) => {
        const button = event.currentTarget;
        navigator.clipboard?.writeText(window.location.href).then(() => {
            button.classList.add('is-copied');
            const icon = button.querySelector('.material-symbols-outlined');
            const label = button.querySelector('.tv-toolbar-label');
            if (icon) icon.textContent = 'check';
            if (label) label.textContent = 'Copiado';
            window.setTimeout(() => {
                button.classList.remove('is-copied');
                if (icon) icon.textContent = 'link';
                if (label) label.textContent = 'Copiar';
            }, 1200);
        });
    });

    document.querySelector('[data-print]')?.addEventListener('click', () => window.print());

    const favButton = document.querySelector('[data-favorite]');
    favButton?.addEventListener('click', () => {
        favButton.classList.toggle('is-fav');
        const icon = favButton.querySelector('.material-symbols-outlined');
        if (icon) {
            icon.textContent = favButton.classList.contains('is-fav') ? 'star' : 'star_border';
        }
    });

    // ── Feedback de utilidad ─────────────────────────────────
    document.querySelectorAll('[data-feedback]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelector('[data-feedback-group]')?.toggleAttribute('hidden');
            document.querySelector('[data-feedback-result]')?.toggleAttribute('hidden');
        });
    });
})();