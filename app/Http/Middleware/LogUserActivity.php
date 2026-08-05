<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log activity for the Guest role
        if ($request->user() && $request->user()->hasRole('Guest')) {
            $route = $request->route();
            if ($route) {
                $routeName = $route->getName();
                $method = $request->method();

                // Skip logging for certain routes
                if (!$this->shouldSkipLogging($routeName)) {
                    $description = $this->getDescriptionFromRoute($routeName, $method);
                    if ($description) {
                        ActivityLogger::log(
                            $description,
                            $this->getEventType($method),
                            [
                                'url' => $request->fullUrl(),
                                'method' => $method,
                                'route' => $routeName,
                                'params' => $request->except(['password', 'password_confirmation'])
                            ]
                        );
                    }
                }
            }
        }

        return $response;
    }

    private function shouldSkipLogging(?string $routeName): bool
    {
        $skipRoutes = [
            'activity-logs.*',
            'debugbar.*',
            'sanctum.*',
            'ignition.*',
            'livewire.*'
        ];

        if (!$routeName) {
            return true;
        }

        foreach ($skipRoutes as $pattern) {
            if (str_contains($routeName, trim($pattern, '*'))) {
                return true;
            }
        }

        return false;
    }

    private function getDescriptionFromRoute(?string $routeName, string $method): ?string
    {
        if (!$routeName) {
            return null;
        }

        $parts = explode('.', $routeName);
        $action = end($parts);
        $resource = $parts[0] ?? '';

        // Convert route names to readable descriptions
        switch ($action) {
            case 'index':
                return "Viewed {$resource} list";
            case 'show':
                return "Viewed {$resource} details";
            case 'create':
                return "Accessed {$resource} creation form";
            case 'store':
                return "Created new {$resource}";
            case 'edit':
                return "Accessed {$resource} edit form";
            case 'update':
                return "Updated {$resource}";
            case 'destroy':
                return "Deleted {$resource}";
            default:
                return "Accessed {$resource} {$action}";
        }
    }

    private function getEventType(string $method): string
    {
        switch ($method) {
            case 'POST':
                return 'create';
            case 'PUT':
            case 'PATCH':
                return 'update';
            case 'DELETE':
                return 'delete';
            default:
                return 'view';
        }
    }
}