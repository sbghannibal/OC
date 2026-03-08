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
use App\Controllers\Admin\EventController;
use App\Controllers\Admin\UserController;
use App\Controllers\Public\AccessCodeController;
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

$app = new App($router);
$app->run();