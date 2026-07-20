<?php

declare(strict_types=1);

namespace App\Http;

class Router
{
    private array $routes = [];
    private array $globalMiddleware = [];
    private static ?Router $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addMiddleware(string $middlewareClass): void
    {
        $this->globalMiddleware[] = $middlewareClass;
    }

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, array $handler, array $middleware): void
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[a-zA-Z0-9_\-]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->globalMiddleware as $mwClass) {
            $mwClass::handle();
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                foreach ($route['middleware'] as $mwClass) {
                    $mwClass::handle();
                }

                $params = array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);

                [$class, $action] = $route['handler'];
                $controller = new $class();
                $controller->$action(...array_values($params));
                return;
            }
        }

        http_response_code(404);
        View::render('errors.404');
    }
}
