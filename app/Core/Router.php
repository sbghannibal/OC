<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    /** @var list<array{method: string, pattern: string, names: list<string>, handler: callable}> */
    private array $paramRoutes = [];

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
        if (str_contains($path, '{')) {
            $this->addParamRoute('GET', $path, $handler);
        } else {
            $this->routes['GET'][$path] = $handler;
        }
    }

    public function post(string $path, callable $handler): void
    {
        if (str_contains($path, '{')) {
            $this->addParamRoute('POST', $path, $handler);
        } else {
            $this->routes['POST'][$path] = $handler;
        }
    }

    /** Convert a path pattern like /events/{slug}/deelnemen to a regex + param names. */
    private function addParamRoute(string $method, string $path, callable $handler): void
    {
        $names   = [];
        $pattern = preg_replace_callback(
            '/\{([^}]+)\}/',
            static function (array $m) use (&$names): string {
                $names[] = $m[1];
                return '([^/]+)';
            },
            $path
        ) ?? $path;

        $this->paramRoutes[] = [
            'method'  => $method,
            'pattern' => '#^' . $pattern . '$#u',
            'names'   => $names,
            'handler' => $handler,
        ];
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

        // Check if the URI exists for any method (to distinguish 404 vs 405)
        $uriKnown = false;
        foreach ($this->routes as $routes) {
            if (isset($routes[$uri])) {
                $uriKnown = true;
                break;
            }
        }

        if ($uriKnown && !isset($this->routes[$method][$uri])) {
            http_response_code(405);
            $errorFile = __DIR__ . '/../../app/Views/errors/405.php';
            if (is_file($errorFile)) {
                include $errorFile;
            } else {
                echo '<h1>405 – Methode niet toegestaan</h1>';
            }
            return;
        }

        if (isset($this->routes[$method][$uri])) {
            ($this->routes[$method][$uri])();
            return;
        }

        // Try parameterized routes
        $patternMethodMismatch = false;
        foreach ($this->paramRoutes as $route) {
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }
            // URI pattern matched – check HTTP method
            if ($route['method'] !== $method) {
                $patternMethodMismatch = true;
                continue;
            }
            array_shift($matches); // remove the full-match capture
            if (count($matches) !== count($route['names'])) {
                continue; // safety: param count mismatch (should never happen with a correct pattern)
            }
            $params = (array) array_combine($route['names'], $matches);
            ($route['handler'])($params);
            return;
        }

        if ($patternMethodMismatch) {
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