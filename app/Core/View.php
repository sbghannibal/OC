<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    private static string $basePath = '';

    public static function setBasePath(string $basePath): void
    {
        self::$basePath = $basePath;
    }

    public static function basePath(): string
    {
        return self::$basePath;
    }

    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $basePath   = self::$basePath;
        $viewFile   = __DIR__ . '/../../app/Views/' . $view . '.php';
        $layoutFile = __DIR__ . '/../../app/Views/_layout.php';

        // Admin views use their own layout
        if (str_starts_with($view, 'admin/')) {
            $layoutFile = __DIR__ . '/../../app/Views/admin/_layout.php';
        }

        if (!is_file($viewFile)) {
            http_response_code(500);
            echo '<h1>500 – View niet gevonden: ' . htmlspecialchars($view, ENT_QUOTES, 'UTF-8') . '</h1>';
            return;
        }

        include $layoutFile;
    }
}