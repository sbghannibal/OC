<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\View;
use App\Models\AuditLog;

final class AuditLogController
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
        $pdo     = Database::getInstance($this->config['db']);
        $entries = AuditLog::recent($pdo);
        View::render('admin/audit_log', ['entries' => $entries]);
    }
}
