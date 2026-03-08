<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\View;

final class AdminController
{
    public function __construct(private readonly array $config) {}

    public function requireAuth(): void
    {
        if (empty($_SESSION['admin_ok'])) {
            $basePath = $this->config['base_path'] ?? '';
            header('Location: ' . $basePath . '/admin/login');
            exit;
        }
    }

    public function login(): void
    {
        if (!empty($_SESSION['admin_ok'])) {
            $basePath = $this->config['base_path'] ?? '';
            header('Location: ' . $basePath . '/admin');
            exit;
        }
        View::render('admin/login', ['error' => null]);
    }

    public function loginSubmit(): void
    {
        $basePath = $this->config['base_path'] ?? '';

        if (!Csrf::verify()) {
            View::render('admin/login', ['error' => 'Ongeldig formulierverzoek. Probeer opnieuw.']);
            return;
        }

        $password      = (string) ($_POST['password'] ?? '');
        $passwordHash  = (string) ($this->config['admin']['password_hash'] ?? '');

        if ($passwordHash !== '' && password_verify($password, $passwordHash)) {
            $_SESSION['admin_ok'] = true;
            header('Location: ' . $basePath . '/admin');
            exit;
        }

        View::render('admin/login', ['error' => 'Ongeldig wachtwoord.']);
    }

    public function logout(): void
    {
        unset($_SESSION['admin_ok']);
        $basePath = $this->config['base_path'] ?? '';
        header('Location: ' . $basePath . '/admin/login');
        exit;
    }

    public function dashboard(): void
    {
        $this->requireAuth();
        View::render('admin/dashboard');
    }
}
