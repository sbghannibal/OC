<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/../vendor/autoload.php';

$configFile = __DIR__ . '/../config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Configuratiebestand config/config.php ontbreekt. Kopieer config/config.example.php en pas aan.');
}
$config = require $configFile;

use App\Controllers\Public\AccessCodeController;
use App\Controllers\Public\HomeController;
use App\Core\App;
use App\Core\Router;

$router = new Router();

$router->get('/', function () use ($config): void {
    (new HomeController($config))->index();
});

$router->get('/toegang', function () use ($config): void {
    (new AccessCodeController($config))->form();
});

$router->post('/toegang', function () use ($config): void {
    (new AccessCodeController($config))->submit();
});

$app = new App($router);
$app->run();