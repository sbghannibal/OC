<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;
use App\Models\Event;
use App\Models\Registration;

final class EventController
{
    public function __construct(private readonly array $config) {}

    /** GET /events – public list of all events */
    public function index(): void
    {
        $pdo    = Database::getInstance($this->config['db']);
        $events = Event::all($pdo);
        View::render('public/events/index', ['events' => $events]);
    }

    /** GET /events/{slug} – public detail page */
    public function show(string $slug): void
    {
        $pdo   = Database::getInstance($this->config['db']);
        $event = Event::findBySlug($pdo, $slug);

        if ($event === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        $success = false;
        if (!empty($_SESSION['registration_success_' . $slug])) {
            $success = true;
            unset($_SESSION['registration_success_' . $slug]);
        }

        View::render('public/events/show', ['event' => $event, 'success' => $success]);
    }

    /** GET /events/{slug}/deelnemen – registration form (requires access code) */
    public function registerForm(string $slug): void
    {
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);
        $event    = Event::findBySlug($pdo, $slug);

        if ($event === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        if (empty($_SESSION['access_ok_' . $slug])) {
            $return = $basePath . '/events/' . rawurlencode($slug) . '/deelnemen';
            header('Location: ' . $basePath . '/toegang?return=' . rawurlencode($return));
            exit;
        }

        View::render('public/events/register', ['event' => $event, 'errors' => [], 'old' => []]);
    }

    /** POST /events/{slug}/deelnemen – process registration */
    public function registerSubmit(string $slug): void
    {
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);
        $event    = Event::findBySlug($pdo, $slug);

        if ($event === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        if (empty($_SESSION['access_ok_' . $slug])) {
            $return = $basePath . '/events/' . rawurlencode($slug) . '/deelnemen';
            header('Location: ' . $basePath . '/toegang?return=' . rawurlencode($return));
            exit;
        }

        if (!Csrf::verify()) {
            View::render('public/events/register', [
                'event'  => $event,
                'errors' => ['Ongeldig formulierverzoek. Probeer opnieuw.'],
                'old'    => [],
            ]);
            return;
        }

        $naam     = trim((string) ($_POST['naam']     ?? ''));
        $email    = trim((string) ($_POST['email']    ?? ''));
        $telefoon = trim((string) ($_POST['telefoon'] ?? ''));
        $opmerking = trim((string) ($_POST['opmerking'] ?? ''));

        $errors = [];
        if ($naam === '') {
            $errors[] = 'Naam is verplicht.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Vul een geldig e-mailadres in.';
        }

        if ($errors !== []) {
            View::render('public/events/register', [
                'event'  => $event,
                'errors' => $errors,
                'old'    => compact('naam', 'email', 'telefoon', 'opmerking'),
            ]);
            return;
        }

        Registration::create($pdo, [
            'event_id' => (int) $event['id'],
            'naam'     => $naam,
            'email'    => $email,
            'telefoon' => $telefoon !== '' ? $telefoon : null,
            'opmerking'=> $opmerking !== '' ? $opmerking : null,
        ]);

        $_SESSION['registration_success_' . $slug] = true;
        header('Location: ' . $basePath . '/events/' . rawurlencode($slug));
        exit;
    }
}
