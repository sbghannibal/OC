<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\User;

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

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $pdo  = Database::getInstance($this->config['db']);
        $user = User::findByUsername($pdo, $username);

        if ($user !== null && User::verifyPassword($user, $password)) {
            $_SESSION['admin_ok']       = true;
            $_SESSION['admin_user_id']  = (int) $user['id'];
            $_SESSION['admin_username'] = (string) $user['username'];

            AuditLog::record(
                $pdo,
                (int) $user['id'],
                (string) $user['username'],
                'auth.login',
                null,
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            header('Location: ' . $basePath . '/admin');
            exit;
        }

        View::render('admin/login', ['error' => 'Ongeldige gebruikersnaam of wachtwoord.']);
    }

    public function logout(): void
    {
        if (!empty($_SESSION['admin_ok'])) {
            $pdo = Database::getInstance($this->config['db']);
            AuditLog::record(
                $pdo,
                (int) ($_SESSION['admin_user_id'] ?? 0),
                (string) ($_SESSION['admin_username'] ?? ''),
                'auth.logout',
                null,
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        }

        unset($_SESSION['admin_ok'], $_SESSION['admin_user_id'], $_SESSION['admin_username']);
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
