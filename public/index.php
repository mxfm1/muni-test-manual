<?php

declare(strict_types=1);

use ManualMuni\Controllers\HandbookController;
use ManualMuni\Controllers\HomeController;
use ManualMuni\Controllers\MediaController;
use ManualMuni\Controllers\ModuleController;
use ManualMuni\Controllers\SectionController;
use ManualMuni\Controllers\SearchController;
use ManualMuni\Controllers\TopicController;
use ManualMuni\Controllers\TopicViewController;
use ManualMuni\Models\Repositories\HandbookSearchRepository;
use ManualMuni\Models\Repositories\MediaRepository;
use ManualMuni\Models\Repositories\ModuleRepository;
use ManualMuni\Models\Repositories\SectionRepository;
use ManualMuni\Models\Repositories\TopicRepository;
use ManualMuni\Services\LocalImageStorage;
use ManualMuni\Support\CsrfTokenManager;
use ManualMuni\Support\DatabaseConnectionFactory;
use ManualMuni\Support\DotenvLoader;
use ManualMuni\Support\PublicPath;

require dirname(__DIR__) . '/vendor/autoload.php';

DotenvLoader::load(dirname(__DIR__));

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (($_SERVER['HTTPS'] ?? '') === 'on'),
]);
session_start();

$csrfTokenManager = new CsrfTokenManager();

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($path) ? $path : '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST' && preg_match('#^/sections/([1-9][0-9]*)/media$#', $path, $matches) === 1) {
    $projectRoot = dirname(__DIR__);
    $storage = new LocalImageStorage(
        $projectRoot . '/public/uploads/media',
        PublicPath::for('uploads/media'),
    );
    $connection = (new DatabaseConnectionFactory())->create();

    (new MediaController(
        $storage,
        new MediaRepository($connection),
        new SectionRepository($connection),
        $csrfTokenManager,
    ))->store((int) $matches[1]);
}

if ($method === 'POST' && $path === '/handbook/modules') {
    $connection = (new DatabaseConnectionFactory())->create();
    $storage = new LocalImageStorage(
        dirname(__DIR__) . '/public/uploads/media',
        PublicPath::for('uploads/media'),
    );

    (new ModuleController(
        new ModuleRepository($connection),
        new MediaRepository($connection),
        $storage,
        $csrfTokenManager,
    ))->create();
}

if ($method === 'POST' && $path === '/handbook/modules/update') {
    $connection = (new DatabaseConnectionFactory())->create();
    $storage = new LocalImageStorage(dirname(__DIR__) . '/public/uploads/media', PublicPath::for('uploads/media'));

    (new ModuleController(new ModuleRepository($connection), new MediaRepository($connection), $storage, $csrfTokenManager))->update();
}

if ($method === 'POST' && $path === '/handbook/modules/delete') {
    $connection = (new DatabaseConnectionFactory())->create();
    $storage = new LocalImageStorage(dirname(__DIR__) . '/public/uploads/media', PublicPath::for('uploads/media'));

    (new ModuleController(new ModuleRepository($connection), new MediaRepository($connection), $storage, $csrfTokenManager))->delete();
}

if ($method === 'POST' && $path === '/handbook/topics') {
    $connection = (new DatabaseConnectionFactory())->create();
    $storage = new LocalImageStorage(dirname(__DIR__) . '/public/uploads/media', PublicPath::for('uploads/media'));

    (new TopicController(new TopicRepository($connection), new ModuleRepository($connection), new MediaRepository($connection), $storage, $csrfTokenManager))->create();
}

if ($method === 'POST' && $path === '/handbook/topics/update') {
    $connection = (new DatabaseConnectionFactory())->create();
    $storage = new LocalImageStorage(dirname(__DIR__) . '/public/uploads/media', PublicPath::for('uploads/media'));

    (new TopicController(new TopicRepository($connection), new ModuleRepository($connection), new MediaRepository($connection), $storage, $csrfTokenManager))->update();
}

if ($method === 'POST' && $path === '/handbook/topics/delete') {
    $connection = (new DatabaseConnectionFactory())->create();
    $storage = new LocalImageStorage(dirname(__DIR__) . '/public/uploads/media', PublicPath::for('uploads/media'));

    (new TopicController(new TopicRepository($connection), new ModuleRepository($connection), new MediaRepository($connection), $storage, $csrfTokenManager))->delete();
}

if ($method === 'POST' && $path === '/handbook/sections') {
    $connection = (new DatabaseConnectionFactory())->create();
    $storage = new LocalImageStorage(dirname(__DIR__) . '/public/uploads/media', PublicPath::for('uploads/media'));

    (new SectionController(new SectionRepository($connection), new TopicRepository($connection), new MediaRepository($connection), $storage, $csrfTokenManager))->create();
}

if ($method === 'POST' && $path === '/handbook/sections/update') {
    $connection = (new DatabaseConnectionFactory())->create();
    $storage = new LocalImageStorage(dirname(__DIR__) . '/public/uploads/media', PublicPath::for('uploads/media'));

    (new SectionController(new SectionRepository($connection), new TopicRepository($connection), new MediaRepository($connection), $storage, $csrfTokenManager))->update();
}

if ($method === 'POST' && $path === '/handbook/sections/delete') {
    $connection = (new DatabaseConnectionFactory())->create();
    $storage = new LocalImageStorage(dirname(__DIR__) . '/public/uploads/media', PublicPath::for('uploads/media'));

    (new SectionController(new SectionRepository($connection), new TopicRepository($connection), new MediaRepository($connection), $storage, $csrfTokenManager))->delete();
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

    (new TopicViewController(
        new ModuleRepository($connection),
        new TopicRepository($connection),
        new SectionRepository($connection),
    ))->module((int) $matches[1]);
}

if ($method === 'GET' && preg_match('#^/handbook/([1-9][0-9]*)/topics/([1-9][0-9]*)$#', $path, $matches) === 1) {
    $connection = (new DatabaseConnectionFactory())->create();

    (new TopicViewController(
        new ModuleRepository($connection),
        new TopicRepository($connection),
        new SectionRepository($connection),
    ))->topic((int) $matches[1], (int) $matches[2]);
}

http_response_code(404);
echo 'Página no encontrada.';
