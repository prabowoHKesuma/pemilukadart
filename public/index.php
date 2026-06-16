<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

$router = new App\Core\Router();

require_once __DIR__ . '/../routes/web.php';

$router->dispatch();