<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\RateLimit;
use App\Core\View;
use App\Models\Event;

final class AccessCodeController
{
    private const RATE_LIMIT_MAX     = 5;
    private const RATE_LIMIT_WINDOW  = 300; // 5 minutes

    public function __construct(private readonly array $config) {}

    public function form(): void
    {
        View::render('public/access_code', ['error' => null, 'rateLimited' => false]);
    }

    public function submit(): void
    {
        $basePath = $this->config['base_path'] ?? '';

        if (!Csrf::verify()) {
            View::render('public/access_code', [
                'error'       => 'Ongeldig formulierverzoek. Probeer opnieuw.',
                'rateLimited' => false,
            ]);
            return;
        }

        $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'access_attempt_' . md5($ip);

        if (RateLimit::tooManyAttempts($key, self::RATE_LIMIT_MAX, self::RATE_LIMIT_WINDOW)) {
            View::render('public/access_code', [
                'error'       => null,
                'rateLimited' => true,
            ]);
            return;
        }

        $pdo   = Database::getInstance($this->config['db_path']);
        $event = Event::findCurrent($pdo);

        if ($event === null) {
            View::render('public/access_code', [
                'error'       => 'Er is momenteel geen actief evenement.',
                'rateLimited' => false,
            ]);
            return;
        }

        $code = trim((string) ($_POST['code'] ?? ''));

        if (hash_equals($event['access_code'], $code)) {
            RateLimit::reset($key);
            $_SESSION['access_ok_' . $event['slug']] = true;
            header('Location: ' . $basePath . '/');
            exit;
        }

        RateLimit::increment($key);
        View::render('public/access_code', [
            'error'       => 'Ongeldige toegangscode. Probeer opnieuw.',
            'rateLimited' => false,
        ]);
    }
}