<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    /**
     * Dynamic routes with named placeholders, e.g. /events/{slug}.
     *
     * @var array<string, list<array{pattern: string, params: list<string>, handler: callable}>>
     */
    private array $dynamicRoutes = [];

    private string $basePath;

    public function __construct(string $basePath = '')
    {
        if ($basePath === '') {
            // Auto-detect: strip script filename from SCRIPT_NAME
            $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
            $basePath = rtrim(dirname($script), '/');
        }
        // Normalise to empty string when we are at the root
        $this->basePath = ($basePath === '/' ? '' : $basePath);
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        if (strpos($path, '{') === false) {
            $this->routes[$method][$path] = $handler;
            return;
        }

        // Extract parameter names and build a regex pattern.
        // [^/]+ safely excludes '/' (no path traversal); '?' and '#' are already
        // stripped by parse_url() before dispatch() is called.
        $params  = [];
        $pattern = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', static function (array $m) use (&$params): string {
            $params[] = $m[1];
            return '(?P<' . $m[1] . '>[^/]+)';
        }, $path);

        $this->dynamicRoutes[$method][] = [
            'pattern' => '#^' . $pattern . '$#',
            'params'  => $params,
            'handler' => $handler,
        ];
    }

    /**
     * Try to match a dynamic route for the given method and URI.
     *
     * @return array{handler: callable, params: array<string,string>}|null
     */
    private function matchDynamic(string $method, string $uri): ?array
    {
        foreach ($this->dynamicRoutes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = [];
                foreach ($route['params'] as $name) {
                    $params[$name] = $matches[$name] ?? '';
                }
                return ['handler' => $route['handler'], 'params' => $params];
            }
        }
        return null;
    }

    /** Check whether any registered method recognises this URI (static or dynamic). */
    private function uriKnown(string $uri): bool
    {
        foreach ($this->routes as $routes) {
            if (isset($routes[$uri])) {
                return true;
            }
        }
        foreach ($this->dynamicRoutes as $methodRoutes) {
            foreach ($methodRoutes as $route) {
                if (preg_match($route['pattern'], $uri)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri    = (string) $rawUri;

        // Strip base path prefix
        if ($this->basePath !== '' && str_starts_with($uri, $this->basePath)) {
            $uri = substr($uri, strlen($this->basePath));
        }

        $uri = '/' . ltrim($uri, '/');
        // Preserve the root path as-is; strip trailing slashes from all other paths
        // so that e.g. /admin/events/ resolves the same as /admin/events.
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        // Try static match first
        if (isset($this->routes[$method][$uri])) {
            ($this->routes[$method][$uri])();
            return;
        }

        // Try dynamic match
        $match = $this->matchDynamic($method, $uri);
        if ($match !== null) {
            ($match['handler'])($match['params']);
            return;
        }

        // Distinguish 404 vs 405
        if ($this->uriKnown($uri)) {
            http_response_code(405);
            $errorFile = __DIR__ . '/../../app/Views/errors/405.php';
            if (is_file($errorFile)) {
                include $errorFile;
            } else {
                echo '<h1>405 – Methode niet toegestaan</h1>';
            }
            return;
        }

        http_response_code(404);
        $errorFile = __DIR__ . '/../../app/Views/errors/404.php';
        if (is_file($errorFile)) {
            include $errorFile;
        } else {
            echo '<h1>404 – Pagina niet gevonden</h1>';
        }
    }
}