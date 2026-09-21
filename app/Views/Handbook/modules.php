<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Directorio de Módulos Operativos - Manuales Municipales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBaseUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>/handbook-modules.css">
</head>
<body class="handbook-modules">

    <!-- Header -->
    <header class="hm-header">
        <div class="hm-header-inner">
            <div class="hm-brand">
                <div class="hm-brand-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                    </svg>
                </div>
                <div class="hm-brand-text">
                    <h1>Manuales Operativos Municipales</h1>
                    <p>Portal de Operaciones</p>
                </div>
            </div>
            
            <div class="hm-search-bar">
                <svg class="hm-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                </svg>
                <input class="hm-search-input" placeholder="Buscar en el catálogo de módulos..." type="search" />
                <kbd class="hm-search-kbd">Ctrl K</kbd>
            </div>
        </div>
    </header>

    <!-- Subheader -->
    <div class="hm-subheader">
        <div class="hm-subheader-inner">
            <nav class="hm-breadcrumbs">
                <a href="/handbook">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M4 6h16M4 10h16M4 14h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                    </svg>
                    Inicio
                </a>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                </svg>
                <span>Explorador de Módulos</span>
            </nav>
            <div style="display:flex; gap:.5rem; align-items:center;">
                <span style="background:var(--slate-100); padding:.125rem .5rem; border-radius:999px; border:1px solid var(--slate-200);">Versión Oficial 2026.4</span>
            </div>
        </div>
    </div>

    <!-- Main -->
    <main class="hm-main">
        
        <header class="hm-section-header">
            <span class="hm-section-eyebrow">Gestión y Procedimientos</span>
            <h2 class="hm-section-title">Módulos de Procedimientos Operativos</h2>
            <p class="hm-section-desc">Selecciona un módulo para acceder a sus tópicos, pautas de atención en ventanilla, normativas y protocolos paso a paso.</p>
            
            <!-- <div class="hm-filters">
                <div class="hm-tabs">
                    <button class="hm-tab active">Todos (<?= count($modules) ?>)</button>
                    <button class="hm-tab">Trámites</button>
                    <button class="hm-tab">Atención</button>
                </div>
            </div> -->
        </header>

        <section class="hm-grid">
            <?php foreach ($modules as $module): ?>
                <?php 
                    $topicsCount = isset($topicsByModule[$module->id]) ? count($topicsByModule[$module->id]) : 0;
                ?>
                <article class="hm-card">
                    <div>
                        <div class="hm-card-header">
                            <div class="hm-card-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"></path>
                                </svg>
                            </div>
                            <!-- <span class="hm-card-tag">Oficial</span> -->
                        </div>
                        <h3 class="hm-card-title"><?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h3>
                        <p class="hm-card-desc"><?= htmlspecialchars($module->description ?? 'Sin descripción.', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        
                        <div class="hm-card-meta">
                            <span class="hm-card-meta-main"><?= $topicsCount ?> Tópicos</span>
                            <span>•</span>
                            <span>Actualizado <?= $module->updatedAt ? $module->updatedAt->format('d/m/Y') : 'Recientemente' ?></span>
                        </div>
                    </div>
                    
                    <div class="hm-card-action">
                        <a href="/handbook/<?= $module->id ?>" class="hm-btn">
                            Ingresar al módulo
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                            </svg>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- Banner Inferior -->
        <div class="hm-banner">
            <div class="hm-banner-content">
                <div class="hm-banner-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                    </svg>
                </div>
                <p class="hm-banner-text"><strong>¿No encuentras un procedimiento específico?</strong> Consulta con el equipo de TI sobre el procedimiento faltante ingresando un requerimiento en .-..</p>
            </div>
            <!-- <a href="#" class="hm-banner-link">Solicitar manual →</a> -->
        </div>

    </main>

    <!-- Footer -->
    <footer class="hm-footer">
        <div class="hm-footer-inner">
            <div class="hm-footer-brand">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                </svg>
                <span>Sistema Oficial de Gestión y Protocolos Municipales © 2026</span>
            </div>
            <nav class="hm-footer-links">
                <a href="/handbook/modules">Módulos</a>
                <a href="#">Protocolos</a>
            </nav>
        </div>
    </footer>

</body>
</html>
