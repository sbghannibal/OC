<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Registration;

final class InschrijvingenController
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

    /** Build the signed QR URL for an event (requires APP_SIGNING_KEY). */
    private function buildQrUrl(string $slug): ?string
    {
        $signingKey = (string) ($this->config['signing_key'] ?? '');
        if ($signingKey === '') {
            return null;
        }
        $basePath = $this->config['base_path'] ?? '';
        $ts  = (string) time();
        $sig = hash_hmac('sha256', $slug . '|' . $ts, $signingKey);
        return $basePath . '/events/' . rawurlencode($slug) . '/qr?ts=' . $ts . '&sig=' . $sig;
    }

    /** GET /admin/inschrijvingen */
    public function index(): void
    {
        $this->requireAuth();

        $pdo           = Database::getInstance($this->config['db']);
        $event         = Event::findCurrent($pdo);
        $registrations = $event !== null ? Registration::allForEvent($pdo, (int) $event['id']) : [];
        $qrUrl         = $event !== null ? $this->buildQrUrl($event['slug']) : null;

        View::render('admin/inschrijvingen/index', [
            'event'         => $event,
            'registrations' => $registrations,
            'qrUrl'         => $qrUrl,
            'statuses'      => Registration::PAYMENT_STATUSES,
        ]);
    }

    /** GET /admin/inschrijvingen.csv */
    public function export(): void
    {
        $this->requireAuth();

        $pdo           = Database::getInstance($this->config['db']);
        $event         = Event::findCurrent($pdo);
        $registrations = $event !== null ? Registration::allForEvent($pdo, (int) $event['id']) : [];

        $filename = 'inschrijvingen'
            . ($event !== null ? '-' . preg_replace('/[^a-z0-9-]/', '-', $event['slug']) : '')
            . '-' . date('Ymd')
            . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store');

        // BOM so Excel opens UTF-8 correctly
        echo "\xEF\xBB\xBF";

        $columns = ['id', 'naam', 'email', 'telefoon', 'opmerking', 'payment_status', 'payment_reference', 'aangemeld_op'];
        echo implode(',', array_map(static fn(string $c): string => '"' . $c . '"', $columns)) . "\r\n";

        foreach ($registrations as $row) {
            $line = [
                $row['id'],
                $row['naam'],
                $row['email'],
                $row['telefoon'] ?? '',
                $row['opmerking'] ?? '',
                $row['payment_status'],
                $row['payment_reference'] ?? '',
                $row['created_at'],
            ];
            echo implode(',', array_map(
                static fn($v): string => '"' . str_replace('"', '""', (string) $v) . '"',
                $line
            )) . "\r\n";
        }
        exit;
    }

    /** POST /admin/inschrijvingen/betaalstatus */
    public function updatePaymentStatus(): void
    {
        $this->requireAuth();

        $basePath = $this->config['base_path'] ?? '';

        if (!Csrf::verify()) {
            header('Location: ' . $basePath . '/admin/inschrijvingen');
            exit;
        }

        $id     = (int) ($_POST['registration_id'] ?? 0);
        $status = trim((string) ($_POST['payment_status'] ?? ''));

        if ($id > 0 && in_array($status, Registration::PAYMENT_STATUSES, true)) {
            $pdo = Database::getInstance($this->config['db']);
            Registration::updatePaymentStatus($pdo, $id, $status);

            AuditLog::record(
                $pdo,
                (int) ($_SESSION['admin_user_id'] ?? 0),
                (string) ($_SESSION['admin_username'] ?? ''),
                'registration.payment_status',
                "id={$id} status={$status}",
                $_SERVER['REMOTE_ADDR'] ?? null
            );
        }

        header('Location: ' . $basePath . '/admin/inschrijvingen');
        exit;
    }
}
