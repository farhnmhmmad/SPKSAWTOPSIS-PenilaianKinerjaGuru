<?php
namespace App\Core;

class Router
{
    private array $routes = ['GET'=>[], 'POST'=>[]];

    public function get(string $path, $handler): void { $this->routes['GET'][$path] = $handler; }
    public function post(string $path, $handler): void { $this->routes['POST'][$path] = $handler; }

    private function notFound(): void {
        http_response_code(404);
        echo '404 Not Found';
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        $handler = $this->routes[$method][$uri] ?? null;
        if (!$handler) { $this->notFound(); return; }

        if (is_callable($handler)) {
            call_user_func($handler);
            return;
        }
        if (is_array($handler) && count($handler) === 2) {
            [$class, $methodName] = $handler;
            $controller = new $class();
            $controller->$methodName();
            return;
        }
        $this->notFound();
    }
}
