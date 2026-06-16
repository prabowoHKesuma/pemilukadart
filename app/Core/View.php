<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        extract($data);

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        $layoutPath = __DIR__ . '/../Views/layouts/' . $layout . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$view}");
        }

        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout not found: {$layout}");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require $layoutPath;
    }
}