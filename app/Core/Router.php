<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri    = '/' . ltrim((string) $uri, '/');

        if (isset($this->routes[$method][$uri])) {
            ($this->routes[$method][$uri])();
            return;
        }

        http_response_code(404);
        echo '<h1>404 – Pagina niet gevonden</h1>';
    }
}