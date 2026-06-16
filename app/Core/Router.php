<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, array $action): void
    {
        $this->routes['GET'][$this->normalize($uri)] = $action;
    }

    public function post(string $uri, array $action): void
    {
        $this->routes['POST'][$this->normalize($uri)] = $action;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getCurrentUri();

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $route => $action) {
            $pattern = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([a-zA-Z0-9_-]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                [$controller, $methodName] = $action;

                if (!class_exists($controller)) {
                    throw new \Exception("Controller not found: {$controller}");
                }

                $controllerInstance = new $controller();

                if (!method_exists($controllerInstance, $methodName)) {
                    throw new \Exception("Method not found: {$methodName}");
                }

                call_user_func_array([$controllerInstance, $methodName], $matches);
                return;
            }
        }

        http_response_code(404);

        $view = __DIR__ . '/../Views/errors/404.php';

        if (file_exists($view)) {
            require $view;
            return;
        }

        echo '404 Not Found';
    }

    private function getCurrentUri(): string
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $basePath = parse_url(Env::get('APP_URL'), PHP_URL_PATH) ?? '';
        $basePath = rtrim($basePath, '/');

        if ($basePath !== '' && str_starts_with($requestUri, $basePath)) {
            $requestUri = substr($requestUri, strlen($basePath));
        }

        return $this->normalize($requestUri);
    }

    private function normalize(string $uri): string
    {
        $uri = '/' . trim($uri, '/');

        return $uri === '//' ? '/' : $uri;
    }
}