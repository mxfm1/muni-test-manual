<?php

declare(strict_types=1);

namespace ManualMuni\Controllers;

use ManualMuni\Support\PublicPath;

final class HomeController
{
    public function index(): never
    {
        $assetBaseUrl = PublicPath::for('assets');

        require __DIR__ . '/../Views/Home/index.php';
        exit;
    }
}
