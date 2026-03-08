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

    /** POST /admin/inschrijvingen/{id} – update payment info for a registration */
    public function updatePayment(int $id): void
    {
        $this->requireAuth();

        if (!\App\Core\Csrf::verify()) {
            http_response_code(400);
            echo 'Ongeldig formulierverzoek.';
            return;
        }

        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);

        $registration = Registration::findById($pdo, $id);
        if ($registration === null) {
            http_response_code(404);
            echo 'Inschrijving niet gevonden.';
            return;
        }

        $allowed = ['unknown', 'paid', 'unpaid'];
        $status  = trim((string) ($_POST['payment_status'] ?? 'unknown'));
        if (!in_array($status, $allowed, true)) {
            $status = 'unknown';
        }

        $paidAt = trim((string) ($_POST['paid_at'] ?? ''));
        // datetime-local returns "YYYY-MM-DDTHH:MM"; convert to MySQL datetime format
        if ($paidAt !== '') {
            $paidAt = str_replace('T', ' ', $paidAt);
            if (strlen($paidAt) === 16) {
                $paidAt .= ':00';
            }
        } else {
            $paidAt = null;
        }

        $note = trim((string) ($_POST['payment_note'] ?? ''));
        $note = ($note !== '') ? $note : null;

        Registration::updatePayment($pdo, $id, [
            'payment_status' => $status,
            'paid_at'        => $paidAt,
            'payment_note'   => $note,
        ]);

        header('Location: ' . $basePath . '/admin/inschrijvingen');
        exit;
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

        fputcsv($out, ['ID', 'Naam', 'E-mail', 'Telefoon', 'Opmerking', 'Aangemeld op', 'Betaalstatus', 'Betaald op', 'Betaalopmerking'], ';');
        foreach ($registrations as $r) {
            fputcsv($out, [
                $r['id'],
                $r['naam'],
                $r['email'],
                $r['telefoon'] ?? '',
                $r['opmerking'] ?? '',
                $r['created_at'],
                $r['payment_status'] ?? 'unknown',
                $r['paid_at'] ?? '',
                $r['payment_note'] ?? '',
            ], ';');
        }
        fclose($out);
    }
}
