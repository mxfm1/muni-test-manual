(() => {
    'use strict';

    const searchDialog = document.querySelector('[data-search-dialog]');
    const searchTrigger = document.querySelector('#hh-search-input');
    const searchButton = document.querySelector('.hh-search-btn');
    const searchInput = document.querySelector('[data-search-input]');
    const searchResultsContainer = document.querySelector('[data-search-results]');
    const searchPlaceholder = document.querySelector('[data-search-placeholder]');
    const searchIconMap = { module: 'folder', topic: 'description', section: 'article' };

    let searchTimer = null;
    let activeSearchRequest = null;
    let searchResultItems = [];
    let searchActiveIndex = -1;

    function escapeHtml(value) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' };
        return value.replace(/[&<>\"]/g, (character) => map[character]);
    }

    function openSearchDialog() {
        searchDialog?.showModal();
        searchInput?.focus();
    }

    function closeSearchDialog() {
        clearTimeout(searchTimer);
        if (activeSearchRequest) {
            activeSearchRequest.abort();
            activeSearchRequest = null;
        }
        searchDialog?.close();
        if (searchInput) searchInput.value = '';
        if (searchTrigger) searchTrigger.value = '';
        renderSearchResults(null);
        searchResultItems = [];
        searchActiveIndex = -1;
    }

    function resolveResultPath(result) {
        if (result.type === 'module') return ['Módulo'];
        if (result.type === 'topic') return ['Módulo', result.moduleTitle];
        if (result.type === 'section') return ['Módulo', result.moduleTitle, result.topicTitle];
        return [];
    }

    function renderSearchPlaceholder(mode, query) {
        if (mode === 'results') {
            searchPlaceholder.classList.remove('hh-search-placeholder-empty');
            searchPlaceholder.hidden = true;
            return;
        }

        const isEmpty = mode === 'empty';
        searchPlaceholder.classList.toggle('hh-search-placeholder-empty', isEmpty);
        searchPlaceholder.hidden = false;

        const icon = isEmpty ? 'search_off' : 'manage_search';
        const text = isEmpty
            ? '<strong>Sin resultados</strong><span>No se encontraron coincidencias para &quot;' + escapeHtml(query) + '&quot;.</span>'
            : mode === 'loading'
                ? '<span>Buscando…</span>'
                : '<span>Escribí al menos dos caracteres para buscar.</span>';

        searchPlaceholder.innerHTML =
            '<span class="material-symbols-outlined hh-search-placeholder-icon" aria-hidden="true">' + icon + '</span>' + text;
    }

    function renderSearchResults(state) {
        searchResultsContainer.querySelectorAll('.hh-search-result-item').forEach((item) => item.remove());
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
            item.className = 'hh-search-result-item';

            const pathParts = resolveResultPath(result).filter(Boolean);
            const pathHtml = pathParts.length > 0
                ? '<span class="hh-search-result-path">' + escapeHtml(pathParts.join(' › ')) + '</span>'
                : '';
            const excerptHtml = result.excerpt
                ? '<p class="hh-search-result-excerpt">' + escapeHtml(result.excerpt) + '</p>'
                : '';
            const icon = searchIconMap[result.type] ?? 'description';

            item.innerHTML =
                '<span class="material-symbols-outlined hh-search-result-icon" aria-hidden="true">' + icon + '</span>' +
                '<div class="hh-search-result-body">' + pathHtml +
                    '<span class="hh-search-result-title">' + escapeHtml(result.title) + '</span>' + excerptHtml +
                '</div>';
            item.addEventListener('click', () => navigateSearchResult(result));
            searchResultsContainer.appendChild(item);
            searchResultItems.push(item);
        }

        updateSearchActiveIndex(0);
    }

    function updateSearchActiveIndex(newIndex) {
        if (searchResultItems.length === 0) return;
        searchActiveIndex = ((newIndex % searchResultItems.length) + searchResultItems.length) % searchResultItems.length;
        searchResultItems.forEach((item, index) => item.classList.toggle('is-active', index === searchActiveIndex));
        searchResultItems[searchActiveIndex]?.scrollIntoView({ block: 'nearest' });
    }

    async function executeSearch(query) {
        if (activeSearchRequest) activeSearchRequest.abort();

        const trimmed = query.trim();
        if (trimmed.length < 2) {
            renderSearchResults(null);
            return;
        }

        renderSearchResults({ loading: true, results: [], query: trimmed });
        const controller = new AbortController();
        activeSearchRequest = controller;

        try {
            const response = await fetch('/handbook/search?q=' + encodeURIComponent(trimmed), {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            const payload = await response.json();
            renderSearchResults({ loading: false, results: payload.ok ? payload.results : [], query: trimmed });
        } catch (error) {
            if (error?.name !== 'AbortError') {
                renderSearchResults({ loading: false, results: [], query: trimmed });
            }
        } finally {
            if (activeSearchRequest === controller) activeSearchRequest = null;
        }
    }

    function navigateSearchResult(result) {
        const urls = {
            module: '/handbook/' + result.id,
            topic: '/handbook/' + result.moduleId + '/topics/' + result.id,
            section: '/handbook/' + result.moduleId + '/topics/' + result.parentId + '#section-' + result.id,
        };
        const url = urls[result.type];
        if (url) window.location.href = url;
    }

    function triggerSearch() {
        openSearchDialog();
        if (searchTrigger?.value) {
            searchInput.value = searchTrigger.value;
            searchTrigger.value = '';
            executeSearch(searchInput.value);
        }
    }

    searchTrigger?.addEventListener('click', (event) => {
        event.preventDefault();
        triggerSearch();
    });
    searchButton?.addEventListener('click', triggerSearch);

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
            searchResultItems[searchActiveIndex]?.click();
        } else if (event.key === 'Escape') {
            event.preventDefault();
            closeSearchDialog();
        }
    });

    window.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            searchDialog?.open ? closeSearchDialog() : openSearchDialog();
        }
    });

    searchDialog?.addEventListener('click', (event) => {
        if (event.target === searchDialog) closeSearchDialog();
    });

    searchDialog?.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeSearchDialog();
    });

    searchDialog?.addEventListener('close', () => {
        if (searchInput) searchInput.value = '';
        renderSearchResults(null);
    });
})();
