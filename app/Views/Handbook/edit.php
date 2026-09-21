<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:wght@400;600;700&family=JetBrains+Mono:wght@400;600&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBaseUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>/handbook-editor.css">
    <script type="module" src="<?= htmlspecialchars($assetBaseUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>/handbook-editor.js"></script>
</head>
<body class="handbook-editor" data-initial-selected-module-id="<?= htmlspecialchars($selectedModuleId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" data-initial-selected-topic-id="<?= htmlspecialchars($selectedTopicId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <?php require __DIR__ . '/Partials/header.php'; ?>
    <main class="editor-main">
        <div class="editor-layout">
            <?php require __DIR__ . '/Partials/sidebar.php'; ?>
            <?php require __DIR__ . '/Partials/empty-state.php'; ?>
            <?php require __DIR__ . '/Partials/topic-content.php'; ?>
        </div>
    </main>
    <?php require __DIR__ . '/Partials/module-dialog.php'; ?>
    <?php require __DIR__ . '/Partials/topic-dialog.php'; ?>
    <?php require __DIR__ . '/Partials/edit-module-dialog.php'; ?>
    <?php require __DIR__ . '/Partials/delete-module-dialog.php'; ?>
    <?php require __DIR__ . '/Partials/delete-section-dialog.php'; ?>
    <?php require __DIR__ . '/Partials/edit-topic-dialog.php'; ?>
    <?php require __DIR__ . '/Partials/delete-topic-dialog.php'; ?>
    <?php require __DIR__ . '/Partials/search-dialog.php'; ?>
</body>
</html>
