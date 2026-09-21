<?php

use ManualMuni\Support\TiptapDocument;
use ManualMuni\Support\TiptapRenderer;

/** @var \ManualMuni\Models\HandbookModule $module */
/** @var \ManualMuni\Models\Topic|null $topic */
/** @var list<\ManualMuni\Models\Topic> $topics */
/** @var list<\ManualMuni\Models\Section> $sections */
/** @var \ManualMuni\Models\Topic|null $nextTopic */
/** @var string $assetBaseUrl */

$moduleUrl = '/handbook/' . $module->id;
$activeTopicId = $topic?->id;

$readingMinutes = 1;
$readWords = 0;
$totalSections = 0;
foreach ($sections as $section) {
    $totalSections++;
    if ($section->content !== null && $section->content !== '') {
        $readWords += str_word_count(TiptapDocument::toPlainText($section->content, 1_000_000));
    }
}
if ($readWords > 0) {
    $readingMinutes = max(1, (int) ceil($readWords / 200));
}

function tvSnippet(?string $text, int $max = 90): string
{
    $text = trim((string) ($text ?? ''));
    if ($text === '') {
        return 'Tópico del manual operativo.';
    }

    return mb_strimwidth($text, 0, $max, '…');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manual operativo municipal: <?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> — <?= htmlspecialchars($topic?->title ?? 'módulo', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>.">
    <title><?= htmlspecialchars(($topic?->title ?? $module->title) . ' — Manuales Operativos Municipales', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBaseUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>/handbook-topic.css">
</head>
<body class="tv">

    <!-- Header principal -->
    <header class="tv-header">
        <div class="tv-header-inner">
            <div class="tv-brand">
                <a href="/" class="tv-brand-icon" aria-label="Ir al inicio">
                    <span class="material-symbols-outlined">account_balance</span>
                </a>
                <div class="tv-brand-text">
                    <span class="tv-brand-title">Manual Operativo</span>
                    <span class="tv-brand-subtitle">Plataforma de Gestión</span>
                </div>
                <div class="tv-sede">
                    <span class="material-symbols-outlined" aria-hidden="true">location_on</span>
                    <span>Municipalidad de Viña del Mar</span>
                </div>
            </div>

            <div class="tv-search-wrap">
                <button class="tv-search-field" type="button" data-search-trigger aria-label="Buscar en el manual">
                    <span class="material-symbols-outlined tv-search-icon" aria-hidden="true">search</span>
                    <span class="tv-search-placeholder">Buscar en el manual de procedimientos, trámites o citas...</span>
                    <span class="tv-search-kbd" aria-hidden="true">Ctrl K</span>
                </button>
            </div>

            <div class="tv-avatar" title="Perfil del Funcionario Municipal" aria-hidden="true">
                <span class="material-symbols-outlined">person</span>
            </div>
        </div>
    </header>

    <div class="tv-main">

        <!-- Breadcrumb + estado -->
        <section class="tv-crumbstrip">
            <div class="tv-crumbstrip-inner">
                <nav class="tv-breadcrumbs" aria-label="Ruta de navegación">
                    <a class="tv-crumb" href="/handbook/modules">
                        <span class="material-symbols-outlined" aria-hidden="true">folder_open</span>
                        Manuales
                    </a>
                    <span class="material-symbols-outlined tv-crumb-sep" aria-hidden="true">chevron_right</span>
                    <a class="tv-crumb" href="<?= htmlspecialchars($moduleUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        <?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </a>
                    <?php if ($topic !== null): ?>
                        <span class="material-symbols-outlined tv-crumb-sep" aria-hidden="true">chevron_right</span>
                        <span class="tv-crumb tv-crumb-current">
                            <span class="tv-crumb-dot" aria-hidden="true"></span>
                            <?= htmlspecialchars($topic->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                </nav>
                <div class="tv-status">
                    <span class="tv-badge">
                        <span class="material-symbols-outlined" aria-hidden="true">verified</span>
                        Versión 1.01
                    </span>
                    <!-- <span class="tv-badge tv-badge-live">
                        <span class="tv-pulse" aria-hidden="true"></span>
                        Módulo Activo en Ventanilla
                    </span> -->
                </div>
            </div>
        </section>

        <!-- Layout sidebar + contenido -->
        <div class="tv-layout">

            <!-- Barra lateral de navegación -->
            <aside class="tv-sidebar">

                <!-- Identidad del módulo -->
                <div class="tv-card tv-module-card">
                    <div class="tv-module-head">
                        <span class="tv-module-icon" aria-hidden="true">
                            <span class="material-symbols-outlined">menu_book</span>
                        </span>
                        <div class="tv-module-heading">
                            <span class="tv-eyebrow">Manual Operativo</span>
                            <h1 class="tv-module-title"><?= htmlspecialchars($module->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
                        </div>
                        <span class="tv-module-count"><?= count($topics) ?> Tópicos</span>
                    </div>
                    <?php if ($module->description !== null && $module->description !== ''): ?>
                        <p class="tv-module-desc"><?= htmlspecialchars($module->description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <div class="tv-filter">
                        <span class="material-symbols-outlined tv-filter-icon" aria-hidden="true">filter_list</span>
                        <input type="text" placeholder="Filtrar tópicos del manual..." aria-label="Filtrar tópicos del manual" data-topic-filter>
                    </div>

                    <nav class="tv-topic-list" aria-label="Tópicos del módulo">
                        <?php if ($topics === []): ?>
                            <p class="tv-topic-empty">Este módulo aún no tiene tópicos publicados.</p>
                        <?php else: ?>
                            <?php foreach ($topics as $index => $moduleTopic): ?>
                                <?php $isActive = $activeTopicId === $moduleTopic->id; ?>
                                <a
                                    class="tv-topic-item <?= $isActive ? 'is-active' : '' ?>"
                                    href="/handbook/<?= $module->id ?>/topics/<?= $moduleTopic->id ?>"
                                    data-topic-item
                                    data-topic-query="<?= htmlspecialchars(mb_strtolower($moduleTopic->title), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                    <?= $isActive ? 'aria-current="page"' : '' ?>
                                >
                                    <span class="tv-topic-icon <?= $isActive ? 'is-active' : '' ?>" aria-hidden="true">
                                        <span class="material-symbols-outlined">description</span>
                                    </span>
                                    <span class="tv-topic-body">
                                        <span class="tv-topic-title-row">
                                            <span class="tv-topic-title">
                                                <?= $index + 1 ?>. <?= htmlspecialchars($moduleTopic->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                            </span>
                                            <?php if ($isActive): ?>
                                                <span class="tv-reading-badge">En lectura</span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="tv-topic-snippet"><?= htmlspecialchars(tvSnippet($moduleTopic->description), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                        <?php if ($isActive): ?>
                                            <span class="tv-topic-meta">
                                                <span class="material-symbols-outlined" aria-hidden="true">schedule</span>
                                                <?= $readingMinutes ?> min de lectura
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </nav>
                </div>

                <!-- Asistencia -->
                <div class="tv-card tv-help-card">
                    <div class="tv-help-head">
                        <span class="material-symbols-outlined tv-help-icon" aria-hidden="true">support_agent</span>
                        <div>
                            <span class="tv-help-title">¿Problemas con la plataforma?</span>
                            <span class="tv-help-subtitle">Contacto TI</span>
                        </div>
                    </div>
                    <p class="tv-help-text">En caso de problemas técnicos con la plataforma, no dudes en contactarnos:</p>
                    <div class="tv-help-rows">
                        <div class="tv-help-row">
                            <span>Correo área informática:</span>
                            <strong>correoprueba@correo.com</strong>
                        </div>
                        <div class="tv-help-row">
                            <span>Numero de contacto departamento</span>
                            <strong>5432323232</strong>
                        </div>
                    </div>
                </div>

            </aside>

            <!-- Contenido del tópico -->
            <article class="tv-article">

                <?php if ($topic === null): ?>
                    <div class="tv-card tv-empty-article">
                        <span class="material-symbols-outlined tv-empty-icon" aria-hidden="true">inbox</span>
                        <h2>Este módulo aún no tiene tópicos</h2>
                        <p>Los procedimientos operativos de este módulo están en preparación. Consultá el resto del manual.</p>
                        <a class="tv-btn" href="/handbook/modules">Ver todos los módulos</a>
                    </div>
                <?php else: ?>

                    <!-- Cabecera del documento -->
                    <header class="tv-card tv-doc-header">
                        <div>
                            <div class="tv-doc-flags">
                                <span class="tv-doc-flag">Tópico Seleccionado</span>
                                <span class="tv-doc-flag-sep">•</span>
                                <!-- <span class="tv-doc-flag-meta">Módulo de Trámites y Servicios</span> -->
                            </div>
                            <h2 class="tv-doc-title"><?= htmlspecialchars($topic->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
                            <?php if ($topic->description !== null && $topic->description !== ''): ?>
                                <p class="tv-doc-desc"><?= htmlspecialchars($topic->description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="tv-doc-meta">
                            <div class="tv-doc-meta-items">
                                <span class="tv-doc-meta-item">
                                    <span class="material-symbols-outlined" aria-hidden="true">update</span>
                                    Actualizado: <strong><?= $topic->updatedAt ? $topic->updatedAt->format('d/m/Y') : 'Recientemente' ?></strong>
                                </span>
                                <span class="tv-doc-meta-item">
                                    <span class="material-symbols-outlined" aria-hidden="true">domain</span>
                                    Sede: <strong>Viña del Mar</strong>
                                </span>
                                <span class="tv-doc-meta-item">
                                    <span class="material-symbols-outlined" aria-hidden="true">timer</span>
                                    Lectura: <strong><?= $readingMinutes ?> min</strong>
                                </span>
                            </div>
                        </div>
                    </header>

                    <?php if ($sections === []): ?>
                        <div class="tv-card tv-empty-sections">
                            <span class="material-symbols-outlined tv-empty-icon" aria-hidden="true">article</span>
                            <h3>Este tópico aún no tiene secciones</h3>
                            <p>El contenido paso a paso de este procedimiento todavía no fue cargado.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($sections as $sectionIndex => $section): ?>
                            <?php $sectionHtml = $section->content !== null && $section->content !== '' ? TiptapRenderer::toHtml($section->content) : ''; ?>
                            <section class="tv-card tv-section" id="section-<?= $section->id ?>" data-section-card data-section-id="<?= $section->id ?>">
                                <div class="tv-section-head">
                                    <span class="tv-section-number"><?= sprintf('%02d', $sectionIndex + 1) ?></span>
                                    <h3 class="tv-section-title">
                                        <?= htmlspecialchars($section->title ?? 'Sección ' . ($sectionIndex + 1), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </h3>
                                </div>
                                <?php if ($sectionHtml === ''): ?>
                                    <p class="tv-section-empty">Esta sección no tiene contenido cargado.</p>
                                <?php else: ?>
                                    <div class="tv-content"><?= $sectionHtml ?></div>
                                <?php endif; ?>

                                <?php if (isset($sections[$sectionIndex + 1])): ?>
                                    <?php $nextSection = $sections[$sectionIndex + 1]; ?>
                                    <div class="tv-section-next">
                                        <a class="tv-btn tv-btn-primary" href="#section-<?= $nextSection->id ?>">
                                            Siguiente paso
                                            <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </section>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Pie del artículo -->
                    <footer class="tv-card tv-article-footer">
                        <div class="tv-feedback">
                            <div>
                                <span class="tv-feedback-title">¿Te fue útil esta guía de procedimiento?</span>
                                <p class="tv-feedback-text">Tu retroalimentación optimiza los manuales de calidad de la Dirección de Tránsito.</p>
                            </div>
                            <div class="tv-feedback-actions" data-feedback-group>
                                <button class="tv-feedback-btn" type="button" data-feedback="ok">
                                    <span class="material-symbols-outlined" aria-hidden="true">thumb_up</span>
                                    Sí, aclaró el flujo
                                </button>
                                <button class="tv-feedback-btn tv-feedback-btn-no" type="button" data-feedback="ko">
                                    <span class="material-symbols-outlined" aria-hidden="true">thumb_down</span>
                                    No, faltan datos
                                </button>
                            </div>
                            <p class="tv-feedback-result" data-feedback-result hidden>¡Gracias! Tu respuesta ha sido enviada al Departamento de Procesos.</p>
                        </div>

                        <div class="tv-next-topic">
                            <span class="tv-next-topic-label">
                                <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
                                Manual General de Operaciones Municipales
                            </span>
                            <?php if ($nextTopic !== null): ?>
                                <a class="tv-btn tv-btn-next" href="/handbook/<?= $module->id ?>/topics/<?= $nextTopic->id ?>">
                                    <span class="tv-btn-next-copy">
                                        <span class="tv-btn-next-eyebrow">Siguiente Tópico</span>
                                        <?php
                                        $nextIndex = null;
                                        foreach ($topics as $index => $candidate) {
                                            if ($candidate->id === $nextTopic->id) {
                                                $nextIndex = $index + 1;
                                                break;
                                            }
                                        }
                                        ?>
                                        <span class="tv-btn-next-title"><?= $nextIndex ?>. <?= htmlspecialchars($nextTopic->title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                    </span>
                                    <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                                </a>
                            <?php else: ?>
                                <span class="tv-btn tv-btn-disabled" aria-disabled="true">
                                    <span class="tv-btn-next-copy">
                                        <span class="tv-btn-next-eyebrow">Fin del Manual</span>
                                        <span class="tv-btn-next-title">Último tópico del módulo</span>
                                    </span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </footer>

                <?php endif; ?>
            </article>

        </div>
    </div>

    <!-- Footer -->
    <footer class="tv-footer">
        <div class="tv-footer-inner">
            <span class="tv-footer-brand">
                <span class="material-symbols-outlined" aria-hidden="true">verified_user</span>
                Sistema Oficial de Gestión y Protocolos Municipales © 2026
            </span>
            <nav class="tv-footer-links" aria-label="Navegación institucional">
                <a href="/handbook">Manual General</a>
                <a href="/handbook/modules">Protocolos de Atención</a>
                <a href="#">Directorio de Trámites</a>
                <a href="#">Normativa</a>
            </nav>
        </div>
    </footer>

    <?php require __DIR__ . '/Partials/search-dialog.php'; ?>

    <script src="<?= htmlspecialchars($assetBaseUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>/handbook-topic.js" defer></script>
</body>
</html>