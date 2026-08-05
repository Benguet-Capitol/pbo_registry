<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * NOTE: This file is not loaded by Laravel 11+ when bootstrap/app.php
     * uses Application::configure(). All middleware registration should
     * live in bootstrap/app.php instead. Keeping this file around risks
     * someone assuming it's authoritative when it isn't.
     */
    protected $middleware = [
        // ...existing code...
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\LogUserActivity::class,
            // ...existing code...
        ],

        'api' => [
            // ...existing code...
        ],
    ];

    protected $routeMiddleware = [
        // ...existing code...
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        'check.office.access' => \App\Http\Middleware\CheckOfficeAccess::class,
    ];
}