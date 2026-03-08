<?php
require '../vendor/autoload.php';

use App\Core\App;
use App\Core\Router;

$router = new Router();
$app = new App($router);
$app->run();