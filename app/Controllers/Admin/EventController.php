<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;
use App\Models\Event;

final class EventController
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
        $pdo    = Database::getInstance($this->config['db_path']);
        $events = Event::all($pdo);
        View::render('admin/events/index', ['events' => $events]);
    }

    public function create(): void
    {
        $this->requireAuth();
        View::render('admin/events/create', ['errors' => [], 'old' => []]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $basePath = $this->config['base_path'] ?? '';

        if (!Csrf::verify()) {
            View::render('admin/events/create', [
                'errors' => ['Ongeldig formulierverzoek. Probeer opnieuw.'],
                'old'    => [],
            ]);
            return;
        }

        $name       = trim((string) ($_POST['name']        ?? ''));
        $slug       = trim((string) ($_POST['slug']        ?? ''));
        $accessCode = trim((string) ($_POST['access_code'] ?? ''));
        $startsAt   = trim((string) ($_POST['starts_at']   ?? '')) ?: null;
        $endsAt     = trim((string) ($_POST['ends_at']     ?? '')) ?: null;

        // Auto-generate slug from name if not provided
        if ($slug === '') {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name) ?? $name);
            $slug = trim($slug, '-');
        }

        $errors = [];
        if ($name === '') {
            $errors[] = 'Naam is verplicht.';
        }
        if ($slug === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors[] = 'Slug mag alleen kleine letters, cijfers en koppeltekens bevatten en mag niet leeg zijn.';
        }
        if ($accessCode === '') {
            $errors[] = 'Toegangscode is verplicht.';
        }

        if ($errors !== []) {
            View::render('admin/events/create', [
                'errors' => $errors,
                'old'    => compact('name', 'slug', 'accessCode', 'startsAt', 'endsAt'),
            ]);
            return;
        }

        $pdo = Database::getInstance($this->config['db_path']);

        if (Event::slugExists($pdo, $slug)) {
            View::render('admin/events/create', [
                'errors' => ['Slug "' . htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') . '" bestaat al.'],
                'old'    => compact('name', 'slug', 'accessCode', 'startsAt', 'endsAt'),
            ]);
            return;
        }

        Event::create($pdo, [
            'name'        => $name,
            'slug'        => $slug,
            'access_code' => $accessCode,
            'starts_at'   => $startsAt,
            'ends_at'     => $endsAt,
        ]);

        header('Location: ' . $basePath . '/admin/events');
        exit;
    }

    public function setCurrent(): void
    {
        $this->requireAuth();
        $basePath = $this->config['base_path'] ?? '';

        if (!Csrf::verify()) {
            header('Location: ' . $basePath . '/admin/events');
            exit;
        }

        $id = (int) ($_POST['event_id'] ?? 0);
        if ($id > 0) {
            $pdo = Database::getInstance($this->config['db_path']);
            Event::setCurrent($pdo, $id);
        }

        header('Location: ' . $basePath . '/admin/events');
        exit;
    }
}
