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

    private static function clientIp(): string
    {
        // Check trusted proxy headers; fall back to REMOTE_ADDR
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Take only the first (client) IP from the chain
            $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

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

        $ip  = self::clientIp();
        $key = 'access_attempt_' . md5($ip . '_' . session_id());

        if (RateLimit::tooManyAttempts($key, self::RATE_LIMIT_MAX, self::RATE_LIMIT_WINDOW)) {
            View::render('public/access_code', [
                'error'       => null,
                'rateLimited' => true,
            ]);
            return;
        }

        $pdo   = Database::getInstance($this->config['db']);
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