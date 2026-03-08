<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Database;
use App\Core\View;
use App\Models\Event;

final class HomeController
{
    public function __construct(private readonly array $config) {}

    public function index(): void
    {
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db_path']);
        $event    = Event::findCurrent($pdo);

        if ($event === null) {
            // No event configured yet
            View::render('public/home', ['event' => null]);
            return;
        }

        // Check if user has been authorized for this specific event
        $sessionKey = 'access_ok_' . $event['slug'];
        if (empty($_SESSION[$sessionKey])) {
            header('Location: ' . $basePath . '/toegang');
            exit;
        }

        View::render('public/home', ['event' => $event]);
    }
}