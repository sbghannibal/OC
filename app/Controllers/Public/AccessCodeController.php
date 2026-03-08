<?php
namespace App\Controllers\Public;

use App\Core\View;

class AccessCodeController {
    public function index() {
        $view = new View();
        $view->render('public/access_code');
    }
}