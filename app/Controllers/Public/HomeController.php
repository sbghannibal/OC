<?php
namespace App\Controllers\Public;

use App\Core\View;

class HomeController {
    public function index() {
        $view = new View();
        $view->render('public/home');
    }
}