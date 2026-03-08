<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\View;

final class AccessCodeController
{
    public function __construct(private readonly array $config) {}

    public function form(): void
    {
        View::render('public/access_code', ['error' => null]);
    }

    public function submit(): void
    {
        $code         = trim((string) ($_POST['code'] ?? ''));
        $defaultCode  = (string) ($this->config['access']['default_code'] ?? 'TEST123');

        if ($code === $defaultCode) {
            $_SESSION['access_ok'] = true;
            header('Location: /');
            exit;
        }

        View::render('public/access_code', ['error' => 'Ongeldige toegangscode. Probeer opnieuw.']);
    }
}