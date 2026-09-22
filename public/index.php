<?php

declare(strict_types=1);

use ManualMuni\Controllers\HandbookController;
use ManualMuni\Controllers\HomeController;
use ManualMuni\Controllers\MediaController;
use ManualMuni\Controllers\MediaProxyController;
use ManualMuni\Controllers\ModuleController;
use ManualMuni\Controllers\SectionController;
use ManualMuni\Controllers\SearchController;
use ManualMuni\Controllers\TopicController;
use ManualMuni\Controllers\TopicViewController;
use ManualMuni\Infrastructure\Storage\ImageStorageFactory;
use ManualMuni\Infrastructure\Storage\MediaImageUrlResolver;
use ManualMuni\Models\Repositories\HandbookSearchRepository;
use ManualMuni\Models\Repositories\MediaRepository;
use ManualMuni\Models\Repositories\ModuleRepository;
use ManualMuni\Models\Repositories\SectionRepository;
use ManualMuni\Models\Repositories\TopicRepository;
use ManualMuni\Support\CsrfTokenManager;
use ManualMuni\Support\DatabaseConnectionFactory;
use ManualMuni\Support\DotenvLoader;
use ManualMuni\Support\TiptapRenderer;

require dirname(__DIR__) . '/vendor/autoload.php';

DotenvLoader::load(dirname(__DIR__));

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (($_SERVER['HTTPS'] ?? '') === 'on'),
]);
session_start();

$csrfTokenManager = new CsrfTokenManager();
$imageStorage = ImageStorageFactory::create(dirname(__DIR__));

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($path) ? $path : '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST' && preg_match('#^/sections/([1-9][0-9]*)/media$#', $path, $matches) === 1) {
    $connection = (new DatabaseConnectionFactory())->create();

    (new MediaController(
        $imageStorage,
        new MediaRepository($connection),
        new SectionRepository($connection),
        $csrfTokenManager,
    ))->store((int) $matches[1]);
}

if ($method === 'POST' && $path === '/handbook/modules') {
    $connection = (new DatabaseConnectionFactory())->create();
    (new ModuleController(
        new ModuleRepository($connection),
        new MediaRepository($connection),
        $imageStorage,
        $csrfTokenManager,
    ))->create();
}

if ($method === 'POST' && $path === '/handbook/modules/update') {
    $connection = (new DatabaseConnectionFactory())->create();
    (new ModuleController(new ModuleRepository($connection), new MediaRepository($connection), $imageStorage, $csrfTokenManager))->update();
}

if ($method === 'POST' && $path === '/handbook/modules/delete') {
    $connection = (new DatabaseConnectionFactory())->create();
    (new ModuleController(new ModuleRepository($connection), new MediaRepository($connection), $imageStorage, $csrfTokenManager))->delete();
}

if ($method === 'POST' && $path === '/handbook/topics') {
    $connection = (new DatabaseConnectionFactory())->create();
    (new TopicController(new TopicRepository($connection), new ModuleRepository($connection), new MediaRepository($connection), $imageStorage, $csrfTokenManager))->create();
}

if ($method === 'POST' && $path === '/handbook/topics/update') {
    $connection = (new DatabaseConnectionFactory())->create();
    (new TopicController(new TopicRepository($connection), new ModuleRepository($connection), new MediaRepository($connection), $imageStorage, $csrfTokenManager))->update();
}

if ($method === 'POST' && $path === '/handbook/topics/delete') {
    $connection = (new DatabaseConnectionFactory())->create();
    (new TopicController(new TopicRepository($connection), new ModuleRepository($connection), new MediaRepository($connection), $imageStorage, $csrfTokenManager))->delete();
}

if ($method === 'POST' && $path === '/handbook/sections') {
    $connection = (new DatabaseConnectionFactory())->create();
    (new SectionController(new SectionRepository($connection), new TopicRepository($connection), new MediaRepository($connection), $imageStorage, $csrfTokenManager))->create();
}

if ($method === 'POST' && $path === '/handbook/sections/update') {
    $connection = (new DatabaseConnectionFactory())->create();
    (new SectionController(new SectionRepository($connection), new TopicRepository($connection), new MediaRepository($connection), $imageStorage, $csrfTokenManager))->update();
}

if ($method === 'POST' && $path === '/handbook/sections/delete') {
    $connection = (new DatabaseConnectionFactory())->create();
    (new SectionController(new SectionRepository($connection), new TopicRepository($connection), new MediaRepository($connection), $imageStorage, $csrfTokenManager))->delete();
}

if ($method === 'GET' && preg_match('#^/media/([a-f0-9]{32}\.(?:webp|jpg))$#', $path, $matches) === 1) {
    (new MediaProxyController($imageStorage))->show('media/' . $matches[1]);
}

if ($method === 'GET' && $path === '/') {
    (new HomeController())->index();
}

if ($method === 'GET' && $path === '/handbook') {
    $connection = (new DatabaseConnectionFactory())->create();

    (new HandbookController(
        new ModuleRepository($connection),
        new TopicRepository($connection),
        new SectionRepository($connection),
        $csrfTokenManager,
    ))->index();
}

if ($method === 'GET' && $path === '/handbook/modules') {
    $connection = (new DatabaseConnectionFactory())->create();

    (new HandbookController(
        new ModuleRepository($connection),
        new TopicRepository($connection),
        new SectionRepository($connection),
        $csrfTokenManager,
    ))->modules();
}

if ($method === 'GET' && $path === '/handbook/edit') {
    $connection = (new DatabaseConnectionFactory())->create();

    (new HandbookController(
        new ModuleRepository($connection),
        new TopicRepository($connection),
        new SectionRepository($connection),
        $csrfTokenManager,
    ))->edit();
}

if ($method === 'GET' && $path === '/handbook/search' && str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
    $connection = (new DatabaseConnectionFactory())->create();

    (new SearchController(new HandbookSearchRepository($connection)))->search();
}

if ($method === 'GET' && preg_match('#^/handbook/([1-9][0-9]*)$#', $path, $matches) === 1) {
    $connection = (new DatabaseConnectionFactory())->create();
    $mediaRepository = new MediaRepository($connection);

    (new TopicViewController(
        new ModuleRepository($connection),
        new TopicRepository($connection),
        new SectionRepository($connection),
        new TiptapRenderer(new MediaImageUrlResolver($mediaRepository, $imageStorage)),
    ))->module((int) $matches[1]);
}

if ($method === 'GET' && preg_match('#^/handbook/([1-9][0-9]*)/topics/([1-9][0-9]*)$#', $path, $matches) === 1) {
    $connection = (new DatabaseConnectionFactory())->create();
    $mediaRepository = new MediaRepository($connection);

    (new TopicViewController(
        new ModuleRepository($connection),
        new TopicRepository($connection),
        new SectionRepository($connection),
        new TiptapRenderer(new MediaImageUrlResolver($mediaRepository, $imageStorage)),
    ))->topic((int) $matches[1], (int) $matches[2]);
}

http_response_code(404);
echo 'Página no encontrada.';
