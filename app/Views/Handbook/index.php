<!doctype html>
<html class="handbook-home" lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Manual oficial de procedimientos y protocolos para funcionarios de la Municipalidad de Viña del Mar.">
    <title>Manual Municipal — Municipio Viña del Mar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBaseUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>/handbook-home.css">
    <script type="module" src="<?= htmlspecialchars($assetBaseUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>/handbook-home.js"></script>
</head>
<body class="handbook-home">

    <main class="hh-main">

        <!-- ── Hero ──────────────────────────────────────────── -->
        <section class="hh-hero" aria-labelledby="hh-hero-title">
            <img
                class="hh-hero-img"
                src="<?= htmlspecialchars($assetBaseUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>/images/homepage.jpg"
                alt="Espacio de trabajo administrativo municipal"
            >
            <div class="hh-hero-overlay" aria-hidden="true"></div>
            <div class="hh-hero-content">
                <div class="hh-hero-copy">
                    <div class="hh-hero-badge" aria-hidden="true">
                        <span class="hh-badge-dot"></span>
                        Documentación oficial de funcionarios
                    </div>
                    <h1 id="hh-hero-title" class="hh-hero-title">
                        Bienvenido al manual de Municipio Viña del Mar
                    </h1>
                    <p class="hh-hero-subtitle">
                        Encuentra toda la información respecto a las funcionalidades que tiene la plataforma de gestión del municipio. 
                    </p>
                    <div class="hh-search-wrap">
                        <div class="hh-search-icon" aria-hidden="true">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                            </svg>
                        </div>
                        <input
                            id="hh-search-input"
                            class="hh-search-input"
                            type="text"
                            placeholder="Buscar procedimientos, trámites, módulos o palabras clave..."
                            autocomplete="off"
                            aria-label="Buscar en el manual"
                        >
                        <div class="hh-search-actions">
                            <kbd class="hh-search-kbd">Ctrl + K</kbd>
                            <button class="hh-search-btn" type="button">Buscar</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Módulos ────────────────────────────────────────── -->
        <div class="hh-modules-section">
            <header class="hh-section-header">
                <div class="hh-section-eyebrow" aria-hidden="true">Directorio del Sistema</div>
                <h2 class="hh-section-title">Módulos presentes en el manual</h2>
                <p class="hh-section-desc">
                    Si estás buscando un módulo en específico, en esta sección podés ver agrupados
                    los módulos presentes en la plataforma.
                </p>
                <div class="hh-section-actions">
                    <a class="hh-section-btn" href="/handbook/modules">
                        Ver todos los módulos
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                        </svg>
                    </a>
                </div>
            </header>

            <section aria-label="Módulos del Manual">
                <?php if ($modules === []): ?>
                    <div class="hh-empty">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                        </svg>
                        <p>Todavía no hay módulos publicados en el manual.<br>
                           <a href="/handbook/edit">Ir al editor</a> para comenzar a crearlos.</p>
                    </div>
                <?php else: ?>
                    <div class="hh-modules-grid">
                        <?php foreach ($modules as $module): ?>
                            <a class="hh-card" data-purpose="guide-card" href="/handbook/<?= $module->id ?>">
                                <div class="hh-card-thumb" aria-hidden="true">
                                    <div class="hh-card-icon-box">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                                        </svg>
                                        <div class="hh-card-icon-bar"></div>
                                    </div>
                                    <span class="hh-card-tag">Módulo</span>
                                </div>
                                <div class="hh-card-body">
                                    <div>
                                        <h3 class="hh-card-title">
                                            <?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                        </h3>
                                        <?php if ($module->description !== null && $module->description !== ''): ?>
                                            <p class="hh-card-desc">
                                                <?= htmlspecialchars($module->description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="hh-card-desc">Explorá los tópicos y procedimientos que componen este módulo del manual.</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="hh-card-footer" aria-hidden="true">
                                        <span>Explorar guía</span>
                                        <svg class="hh-card-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>

    </main>

    <footer class="hh-footer">
        <div class="hh-footer-inner">
            <div class="hh-footer-brand">
                <span>Ilustre Municipalidad de Viña del Mar</span>
                <span aria-hidden="true">•</span>
                <span>Dirección de Tránsito y Tecnologías</span>
            </div>
            <div class="hh-footer-links">
                <span>Versión del Sistema 2.4.0</span>
                <a href="#">Mesa de Ayuda</a>
            </div>
        </div>
    </footer>

    <dialog class="hh-search-dialog" data-search-dialog aria-labelledby="hh-search-dialog-title">
        <div class="hh-search-dialog-inner">
            <header class="hh-search-dialog-header">
                <span class="material-symbols-outlined hh-search-dialog-icon" aria-hidden="true">search</span>
                <h2 id="hh-search-dialog-title" class="hh-visually-hidden">Buscar en el manual</h2>
                <input
                    data-search-input
                    type="search"
                    placeholder="Buscar módulos, tópicos o secciones..."
                    autocomplete="off"
                    aria-label="Buscar en el manual"
                    maxlength="60"
                >
                <kbd class="hh-search-dialog-shortcut">Esc</kbd>
            </header>
            <div class="hh-search-results-container">
                <div class="hh-search-results" data-search-results>
                    <div class="hh-search-placeholder-container">
                        <div class="hh-search-placeholder" data-search-placeholder>
                            <span class="material-symbols-outlined hh-search-placeholder-icon" aria-hidden="true">manage_search</span>
                            <span>Escribí al menos dos caracteres para buscar.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </dialog>

</body>
</html>
