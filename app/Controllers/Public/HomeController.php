<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\View;

final class HomeController
{
    public function __construct(private readonly array $config) {}

    public function index(): void
    {
        if (empty($_SESSION['access_ok'])) {
            header('Location: /toegang');
            exit;
        }

        $groupLabel = (string) ($this->config['access']['default_group_label'] ?? '');
        View::render('public/home', ['groupLabel' => $groupLabel]);
    }
}