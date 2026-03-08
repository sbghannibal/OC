<?php

declare(strict_types=1);

// Start session safely (before any output)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env at the repository root (outside public/)
\App\Core\Env::load(__DIR__ . '/../.env');

$configFile = __DIR__ . '/../config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Configuratiebestand config/config.php ontbreekt. Kopieer config/config.example.php en pas aan.');
}
$config = require $configFile;

use App\Controllers\Admin\AdminController;
use App\Controllers\Admin\AuditLogController;
use App\Controllers\Admin\ClassController;
use App\Controllers\Admin\EventController;
use App\Controllers\Admin\EventOptionsController;
use App\Controllers\Admin\RegistrationController;
use App\Controllers\Admin\UserController;
use App\Controllers\Public\AccessCodeController;
use App\Controllers\Public\EventController as PublicEventController;
use App\Controllers\Public\HomeController;
use App\Core\App;
use App\Core\Router;
use App\Core\View;

// Build router (auto-detects base path from SCRIPT_NAME)
$router = new Router($config['base_path'] ?? '');

// Make the base path available to views and store back in config
$config['base_path'] = $router->getBasePath();
View::setBasePath($config['base_path']);

// ── Public routes ──────────────────────────────────────────────────────────
$router->get('/', function () use ($config): void {
    (new HomeController($config))->index();
});

$router->get('/toegang', function () use ($config): void {
    (new AccessCodeController($config))->form();
});

$router->post('/toegang', function () use ($config): void {
    (new AccessCodeController($config))->submit();
});

// ── Public event routes ────────────────────────────────────────────────────
$router->get('/events', function () use ($config): void {
    (new PublicEventController($config))->index();
});

$router->get('/events/{slug}', function (array $params) use ($config): void {
    (new PublicEventController($config))->show($params['slug']);
});

$router->get('/events/{slug}/deelnemen', function (array $params) use ($config): void {
    (new PublicEventController($config))->registerForm($params['slug']);
});

$router->post('/events/{slug}/deelnemen', function (array $params) use ($config): void {
    (new PublicEventController($config))->registerSubmit($params['slug']);
});

$router->get('/events/{slug}/qr', function (array $params) use ($config): void {
    (new PublicEventController($config))->qrBypass($params['slug']);
});

// ── Admin routes ───────────────────────────────────────────────────────────
$router->get('/admin', function () use ($config): void {
    (new AdminController($config))->dashboard();
});

$router->get('/admin/login', function () use ($config): void {
    (new AdminController($config))->login();
});

$router->post('/admin/login', function () use ($config): void {
    (new AdminController($config))->loginSubmit();
});

$router->post('/admin/logout', function () use ($config): void {
    (new AdminController($config))->logout();
});

$router->get('/admin/events', function () use ($config): void {
    (new EventController($config))->index();
});

$router->get('/admin/events/new', function () use ($config): void {
    (new EventController($config))->create();
});

$router->post('/admin/events', function () use ($config): void {
    (new EventController($config))->store();
});

$router->post('/admin/events/current', function () use ($config): void {
    (new EventController($config))->setCurrent();
});

// ── User management routes ─────────────────────────────────────────────────
$router->get('/admin/users', function () use ($config): void {
    (new UserController($config))->index();
});

$router->get('/admin/users/new', function () use ($config): void {
    (new UserController($config))->create();
});

$router->post('/admin/users', function () use ($config): void {
    (new UserController($config))->store();
});

$router->post('/admin/users/delete', function () use ($config): void {
    (new UserController($config))->destroy();
});

// ── Audit log route ────────────────────────────────────────────────────────
$router->get('/admin/audit-log', function () use ($config): void {
    (new AuditLogController($config))->index();
});

// ── Classes (klassen) routes ───────────────────────────────────────────────
$router->get('/admin/klassen', function () use ($config): void {
    (new ClassController($config))->index();
});

$router->get('/admin/klassen/new', function () use ($config): void {
    (new ClassController($config))->create();
});

$router->post('/admin/klassen', function () use ($config): void {
    (new ClassController($config))->store();
});

$router->post('/admin/klassen/delete', function () use ($config): void {
    (new ClassController($config))->destroy();
});

// ── Event options (opties) routes ──────────────────────────────────────────
$router->get('/admin/events/{slug}/opties', function (array $params) use ($config): void {
    (new EventOptionsController($config))->index($params['slug']);
});

$router->post('/admin/events/{slug}/opties', function (array $params) use ($config): void {
    (new EventOptionsController($config))->storeGroup($params['slug']);
});

$router->post('/admin/events/{slug}/opties/{group_id}/update', function (array $params) use ($config): void {
    (new EventOptionsController($config))->updateGroup($params['slug'], (int) $params['group_id']);
});

$router->post('/admin/events/{slug}/opties/{group_id}/delete', function (array $params) use ($config): void {
    (new EventOptionsController($config))->deleteGroup($params['slug'], (int) $params['group_id']);
});

$router->post('/admin/events/{slug}/opties/{group_id}/items', function (array $params) use ($config): void {
    (new EventOptionsController($config))->storeItem($params['slug'], (int) $params['group_id']);
});

$router->post('/admin/events/{slug}/opties/{group_id}/items/{item_id}/delete', function (array $params) use ($config): void {
    (new EventOptionsController($config))->deleteItem($params['slug'], (int) $params['group_id'], (int) $params['item_id']);
});

// ── Registrations routes ───────────────────────────────────────────────────
$router->get('/admin/inschrijvingen', function () use ($config): void {
    (new RegistrationController($config))->index();
});

$router->post('/admin/inschrijvingen/{id}', function (array $params) use ($config): void {
    (new RegistrationController($config))->updatePayment((int) $params['id']);
});

$router->get('/admin/inschrijvingen.csv', function () use ($config): void {
    (new RegistrationController($config))->exportCsv();
});

$app = new App($router);
$app->run();