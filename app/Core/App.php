<?php

declare(strict_types=1);

namespace App\Core;

final class App
{
    public function __construct(private readonly Router $router) {}

    public function run(): void
    {
        $this->router->dispatch();
    }
}