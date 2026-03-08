<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Database;
use App\Core\View;
use App\Models\Event;

final class EventListController
{
    public function __construct(private readonly array $config) {}

    /** GET /events – overview of all events, current one shown prominently. */
    public function index(): void
    {
        $pdo    = Database::getInstance($this->config['db']);
        $events = Event::all($pdo);
        View::render('public/events/index', ['events' => $events]);
    }

    /** GET /events/{slug} – public detail page for a single event. */
    public function show(string $slug): void
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
            return;
        }

        $hasAccess  = !empty($_SESSION['access_ok_' . $event['slug']]);

        View::render('public/events/show', [
            'event'     => $event,
            'hasAccess' => $hasAccess,
        ]);
    }
}
