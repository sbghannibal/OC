<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;
use App\Models\ParentUser;

final class ParentAuthController
{
    public function __construct(private readonly array $config) {}

    /** GET /ouder/registreren */
    public function registerForm(): void
    {
        if (!empty($_SESSION['parent_ok'])) {
            $basePath = $this->config['base_path'] ?? '';
            header('Location: ' . $basePath . '/events');
            exit;
        }
        View::render('public/parents/register', ['error' => null, 'old' => []]);
    }

    /** POST /ouder/registreren */
    public function registerSubmit(): void
    {
        $basePath = $this->config['base_path'] ?? '';

        if (!Csrf::verify()) {
            View::render('public/parents/register', [
                'error' => 'Ongeldig formulierverzoek. Probeer opnieuw.',
                'old'   => [],
            ]);
            return;
        }

        $email   = trim((string) ($_POST['email']            ?? ''));
        $pass    = (string) ($_POST['password']              ?? '');
        $confirm = (string) ($_POST['password_confirm']      ?? '');

        $errors = [];
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Vul een geldig e-mailadres in.';
        }
        if (strlen($pass) < 8) {
            $errors[] = 'Wachtwoord moet minimaal 8 tekens bevatten.';
        }
        if ($pass !== $confirm) {
            $errors[] = 'Wachtwoorden komen niet overeen.';
        }

        if ($errors !== []) {
            View::render('public/parents/register', [
                'error' => implode(' ', $errors),
                'old'   => ['email' => $email],
            ]);
            return;
        }

        $pdo = Database::getInstance($this->config['db']);

        if (ParentUser::emailExists($pdo, $email)) {
            View::render('public/parents/register', [
                'error' => 'Dit e-mailadres is al geregistreerd. Probeer in te loggen.',
                'old'   => ['email' => $email],
            ]);
            return;
        }

        $parentId = ParentUser::create($pdo, $email, $pass);

        $_SESSION['parent_ok']    = true;
        $_SESSION['parent_id']    = $parentId;
        $_SESSION['parent_email'] = $email;

        header('Location: ' . $basePath . '/events');
        exit;
    }

    /** GET /ouder/login */
    public function loginForm(): void
    {
        if (!empty($_SESSION['parent_ok'])) {
            $basePath = $this->config['base_path'] ?? '';
            header('Location: ' . $basePath . '/events');
            exit;
        }
        $return = trim((string) ($_GET['return'] ?? ''));
        View::render('public/parents/login', ['error' => null, 'return' => $return]);
    }

    /** POST /ouder/login */
    public function loginSubmit(): void
    {
        $basePath = $this->config['base_path'] ?? '';

        if (!Csrf::verify()) {
            View::render('public/parents/login', [
                'error'  => 'Ongeldig formulierverzoek. Probeer opnieuw.',
                'return' => '',
            ]);
            return;
        }

        $email  = trim((string) ($_POST['email']    ?? ''));
        $pass   = (string) ($_POST['password']      ?? '');
        $return = trim((string) ($_POST['return']   ?? ''));

        $pdo    = Database::getInstance($this->config['db']);
        $parent = ParentUser::findByEmail($pdo, $email);

        if ($parent !== null && ParentUser::verifyPassword($parent, $pass)) {
            $_SESSION['parent_ok']    = true;
            $_SESSION['parent_id']    = (int) $parent['id'];
            $_SESSION['parent_email'] = (string) $parent['email'];

            // Only redirect to safe relative URLs (no host component, not protocol-relative)
            if ($return !== '') {
                $parsed = parse_url($return);
                if (
                    $parsed !== false &&
                    !isset($parsed['scheme']) &&
                    !isset($parsed['host']) &&
                    isset($parsed['path']) &&
                    str_starts_with($parsed['path'], '/')
                ) {
                    header('Location: ' . $return);
                    exit;
                }
            }

            header('Location: ' . $basePath . '/events');
            exit;
        }

        View::render('public/parents/login', [
            'error'  => 'Ongeldig e-mailadres of wachtwoord.',
            'return' => $return,
        ]);
    }

    /** POST /ouder/logout */
    public function logout(): void
    {
        unset($_SESSION['parent_ok'], $_SESSION['parent_id'], $_SESSION['parent_email']);
        $basePath = $this->config['base_path'] ?? '';
        header('Location: ' . $basePath . '/events');
        exit;
    }
}
