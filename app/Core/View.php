<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = __DIR__ . '/../../app/Views/' . $view . '.php';
        $layoutFile = __DIR__ . '/../../app/Views/_layout.php';
        include $layoutFile;
    }
}