<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;
use App\Models\OcClass;

final class ClassController
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

    /** GET /admin/klassen */
    public function index(): void
    {
        $this->requireAuth();
        $pdo     = Database::getInstance($this->config['db']);
        $classes = OcClass::all($pdo);
        View::render('admin/classes/index', ['classes' => $classes, 'error' => null, 'success' => null]);
    }

    /** GET /admin/klassen/new */
    public function create(): void
    {
        $this->requireAuth();
        View::render('admin/classes/create', ['errors' => [], 'old' => []]);
    }

    /** POST /admin/klassen */
    public function store(): void
    {
        $this->requireAuth();
        $basePath = $this->config['base_path'] ?? '';

        if (!Csrf::verify()) {
            View::render('admin/classes/create', [
                'errors' => ['Ongeldig formulierverzoek. Probeer opnieuw.'],
                'old'    => [],
            ]);
            return;
        }

        $name = strtoupper(trim((string) ($_POST['name'] ?? '')));

        $errors = [];
        if ($name === '') {
            $errors[] = 'Naam is verplicht.';
        } elseif (!preg_match(OcClass::NAME_PATTERN, $name)) {
            $errors[] = 'Naam moet het formaat hebben van een jaarcijfer (1-6) gevolgd door een letter (bijv. 3A).';
        }

        if ($errors !== []) {
            View::render('admin/classes/create', ['errors' => $errors, 'old' => compact('name')]);
            return;
        }

        $pdo = Database::getInstance($this->config['db']);
        if (OcClass::nameExists($pdo, $name)) {
            View::render('admin/classes/create', [
                'errors' => ['Klas "' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" bestaat al.'],
                'old'    => compact('name'),
            ]);
            return;
        }

        OcClass::create($pdo, $name);
        header('Location: ' . $basePath . '/admin/klassen');
        exit;
    }

    /** POST /admin/klassen/delete */
    public function destroy(): void
    {
        $this->requireAuth();
        $basePath = $this->config['base_path'] ?? '';

        if (!Csrf::verify()) {
            header('Location: ' . $basePath . '/admin/klassen');
            exit;
        }

        $id  = (int) ($_POST['id'] ?? 0);
        $pdo = Database::getInstance($this->config['db']);

        if ($id > 0 && !OcClass::delete($pdo, $id)) {
            // Class is in use – show error
            $classes = OcClass::all($pdo);
            View::render('admin/classes/index', [
                'classes' => $classes,
                'error'   => 'Deze klas kan niet worden verwijderd omdat er al inschrijvingen aan gekoppeld zijn.',
                'success' => null,
            ]);
            return;
        }

        header('Location: ' . $basePath . '/admin/klassen');
        exit;
    }
}
