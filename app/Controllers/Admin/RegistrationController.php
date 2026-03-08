<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;
use App\Models\Event;
use App\Models\Registration;

final class RegistrationController
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

    /** GET /admin/inschrijvingen – list registrations for the current event */
    public function index(): void
    {
        $this->requireAuth();
        $pdo           = Database::getInstance($this->config['db']);
        $event         = Event::findCurrent($pdo);
        $registrations = $event !== null ? Registration::findByEvent($pdo, (int) $event['id']) : [];

        View::render('admin/registrations/index', [
            'event'         => $event,
            'registrations' => $registrations,
        ]);
    }

    /** GET /admin/inschrijvingen.csv – CSV download for the current event */
    public function exportCsv(): void
    {
        $this->requireAuth();
        $pdo   = Database::getInstance($this->config['db']);
        $event = Event::findCurrent($pdo);

        if ($event === null) {
            http_response_code(404);
            echo 'Geen huidig evenement gevonden.';
            return;
        }

        $registrations = Registration::findByEvent($pdo, (int) $event['id']);
        $filename      = 'inschrijvingen-' . preg_replace('/[^a-z0-9-]/i', '-', $event['slug']) . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        // BOM so Excel recognises UTF-8
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');
        if ($out === false) {
            http_response_code(500);
            echo 'Fout bij het genereren van het CSV-bestand.';
            return;
        }

        fputcsv($out, ['ID', 'Naam', 'E-mail', 'Telefoon', 'Opmerking', 'Aangemeld op'], ';');
        foreach ($registrations as $r) {
            fputcsv($out, [
                $r['id'],
                $r['naam'],
                $r['email'],
                $r['telefoon'] ?? '',
                $r['opmerking'] ?? '',
                $r['created_at'],
            ], ';');
        }
        fclose($out);
    }
}
