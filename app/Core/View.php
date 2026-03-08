<?php
namespace App\Core;

class View {
    public function render($view, $data = []) {
        extract($data);
        include "../app/Views/$view.php";
    }
}