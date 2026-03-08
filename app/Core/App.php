<?php
namespace App\Core;

class App {
    private $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    public function run() {
        // Your application logic here
    }
}