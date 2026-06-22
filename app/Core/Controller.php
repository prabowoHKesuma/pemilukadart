<?php

namespace App\Core;

class Controller
{
    protected function view(string $path, array $data = [], string $layout = 'app'): void
    {
        $viewFile = __DIR__ . '/../Views/' . $path . '.php';
        $layoutFile = __DIR__ . '/../Views/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            die('View tidak ditemukan: ' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8'));
        }

        if (!file_exists($layoutFile)) {
            http_response_code(500);
            die('Layout tidak ditemukan: ' . htmlspecialchars($layout, ENT_QUOTES, 'UTF-8'));
        }

        $layoutData = LayoutData::make();

        extract($layoutData, EXTR_SKIP);
        extract($data, EXTR_OVERWRITE);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }

    protected function redirect(string $path): void
    {
        Redirect::to($path);
    }
}