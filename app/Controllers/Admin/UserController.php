<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\User;

final class UserController
{
    public function __construct(private readonly array $config) {}

    private function requireAuth(): void
    {
        if (empty($_SESSION['admin_ok'])) {
            $basePath = $this->config['base_path'] ?? '';
            header('Location: ' . $basePath . '/admin/login');
            exit;
        }
    }

    public function index(): void
    {
        $this->requireAuth();
        $pdo   = Database::getInstance($this->config['db']);
        $users = User::all($pdo);
        View::render('admin/users/index', ['users' => $users]);
    }

    public function create(): void
    {
        $this->requireAuth();
        View::render('admin/users/create', ['errors' => [], 'old' => []]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $basePath = $this->config['base_path'] ?? '';

        if (!Csrf::verify()) {
            View::render('admin/users/create', [
                'errors' => ['Ongeldig formulierverzoek. Probeer opnieuw.'],
                'old'    => [],
            ]);
            return;
        }

        $username        = trim((string) ($_POST['username']         ?? ''));
        $password        = (string) ($_POST['password']              ?? '');
        $passwordConfirm = (string) ($_POST['password_confirmation'] ?? '');

        $errors = [];
        if ($username === '') {
            $errors[] = 'Gebruikersnaam is verplicht.';
        } elseif (!preg_match('/^[a-zA-Z0-9_\-\.]{3,50}$/', $username)) {
            $errors[] = 'Gebruikersnaam mag alleen letters, cijfers, _, - en . bevatten (3–50 tekens).';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Wachtwoord moet minimaal 8 tekens lang zijn.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Wachtwoorden komen niet overeen.';
        }

        if ($errors !== []) {
            View::render('admin/users/create', [
                'errors' => $errors,
                'old'    => ['username' => $username],
            ]);
            return;
        }

        $pdo = Database::getInstance($this->config['db']);

        if (User::usernameExists($pdo, $username)) {
            View::render('admin/users/create', [
                'errors' => ['Gebruikersnaam "' . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . '" bestaat al.'],
                'old'    => ['username' => $username],
            ]);
            return;
        }

        $newId = User::create($pdo, $username, $password);

        AuditLog::record(
            $pdo,
            (int) ($_SESSION['admin_user_id'] ?? 0),
            (string) ($_SESSION['admin_username'] ?? ''),
            'user.create',
            "id={$newId} username={$username}",
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        header('Location: ' . $basePath . '/admin/users');
        exit;
    }

    public function destroy(): void
    {
        $this->requireAuth();
        $basePath = $this->config['base_path'] ?? '';

        if (!Csrf::verify()) {
            header('Location: ' . $basePath . '/admin/users');
            exit;
        }

        $id = (int) ($_POST['user_id'] ?? 0);

        // Prevent admins from deleting their own account
        if ($id > 0 && $id !== (int) ($_SESSION['admin_user_id'] ?? 0)) {
            $pdo = Database::getInstance($this->config['db']);
            User::delete($pdo, $id);

            AuditLog::record(
                $pdo,
                (int) ($_SESSION['admin_user_id'] ?? 0),
                (string) ($_SESSION['admin_username'] ?? ''),
                'user.delete',
                "id={$id}",
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        }

        header('Location: ' . $basePath . '/admin/users');
        exit;
    }
}
