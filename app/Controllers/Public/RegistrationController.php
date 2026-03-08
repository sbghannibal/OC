<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\RateLimit;
use App\Core\View;
use App\Models\Event;
use App\Models\Registration;

final class RegistrationController
{
    private const QR_EXPIRY_SECONDS = 7 * 24 * 3600; // 7 days

    public function __construct(private readonly array $config) {}

    private static function clientIp(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Return the event by slug or render a 404 and return null.
     * @return array<string,mixed>|null
     */
    private function resolveEvent(string $slug): ?array
    {
        $pdo   = Database::getInstance($this->config['db']);
        $event = Event::findBySlug($pdo, $slug);

        if ($event === null) {
            http_response_code(404);
            $errorFile = __DIR__ . '/../../../app/Views/errors/404.php';
            if (is_file($errorFile)) {
                include $errorFile;
            } else {
                echo '<h1>404 – Evenement niet gevonden</h1>';
            }
        }

        return $event;
    }

    /** Check whether the visitor is allowed to register for this event. */
    private function hasAccess(string $slug): bool
    {
        return !empty($_SESSION['access_ok_' . $slug]);
    }

    /** GET /events/{slug}/deelnemen */
    public function form(string $slug): void
    {
        $event = $this->resolveEvent($slug);
        if ($event === null) {
            return;
        }

        $basePath = $this->config['base_path'] ?? '';

        if (!$this->hasAccess($slug)) {
            // Redirect to access-code form, passing the intended destination as a query param
            $intended = urlencode($basePath . '/events/' . rawurlencode($slug) . '/deelnemen');
            header('Location: ' . $basePath . '/toegang?next=' . $intended);
            exit;
        }

        View::render('public/events/deelnemen', [
            'event'   => $event,
            'errors'  => [],
            'old'     => [],
            'success' => false,
        ]);
    }

    /** POST /events/{slug}/deelnemen */
    public function submit(string $slug): void
    {
        $event = $this->resolveEvent($slug);
        if ($event === null) {
            return;
        }

        $basePath = $this->config['base_path'] ?? '';

        if (!$this->hasAccess($slug)) {
            header('Location: ' . $basePath . '/toegang');
            exit;
        }

        if (!Csrf::verify()) {
            View::render('public/events/deelnemen', [
                'event'   => $event,
                'errors'  => ['Ongeldig formulierverzoek. Probeer opnieuw.'],
                'old'     => [],
                'success' => false,
            ]);
            return;
        }

        $naam      = trim((string) ($_POST['naam']      ?? ''));
        $email     = trim((string) ($_POST['email']     ?? ''));
        $telefoon  = trim((string) ($_POST['telefoon']  ?? ''));
        $opmerking = trim((string) ($_POST['opmerking'] ?? ''));

        $errors = [];
        if ($naam === '') {
            $errors[] = 'Naam is verplicht.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Vul een geldig e-mailadres in.';
        }

        if ($errors !== []) {
            View::render('public/events/deelnemen', [
                'event'   => $event,
                'errors'  => $errors,
                'old'     => compact('naam', 'email', 'telefoon', 'opmerking'),
                'success' => false,
            ]);
            return;
        }

        $pdo = Database::getInstance($this->config['db']);
        Registration::create($pdo, [
            'event_id'  => (int) $event['id'],
            'naam'      => $naam,
            'email'     => $email,
            'telefoon'  => $telefoon ?: null,
            'opmerking' => $opmerking ?: null,
        ]);

        View::render('public/events/deelnemen', [
            'event'   => $event,
            'errors'  => [],
            'old'     => [],
            'success' => true,
        ]);
    }

    /**
     * GET /events/{slug}/qr?ts={unix_timestamp}&sig={hmac_hex}
     *
     * Verifies the signed token, sets the session access flag, and redirects
     * to the registration form so the visitor can register in one go.
     */
    public function qr(string $slug): void
    {
        $basePath   = $this->config['base_path'] ?? '';
        $signingKey = (string) ($this->config['signing_key'] ?? '');

        if ($signingKey === '') {
            // Signing key not configured – fall back to normal access-code flow
            header('Location: ' . $basePath . '/toegang');
            exit;
        }

        $event = $this->resolveEvent($slug);
        if ($event === null) {
            return;
        }

        $ts  = (string) ($_GET['ts']  ?? '');
        $sig = (string) ($_GET['sig'] ?? '');

        // Check expiry: reject links older than 7 days
        if ($ts === '' || !ctype_digit($ts) || (time() - (int) $ts) > self::QR_EXPIRY_SECONDS) {
            http_response_code(403);
            View::render('public/events/qr_invalid', [
                'event'   => $event,
                'reason'  => 'verlopen',
            ]);
            return;
        }

        // Verify HMAC (constant-time compare)
        $expected = hash_hmac('sha256', $slug . '|' . $ts, $signingKey);
        if (!hash_equals($expected, $sig)) {
            http_response_code(403);
            View::render('public/events/qr_invalid', [
                'event'  => $event,
                'reason' => 'ongeldig',
            ]);
            return;
        }

        // Grant access for this event
        $_SESSION['access_ok_' . $slug] = true;

        header('Location: ' . $basePath . '/events/' . rawurlencode($slug) . '/deelnemen');
        exit;
    }
}
