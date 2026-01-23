<?php

use App\Http\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(
        function (Middleware $middleware) {
            $app = Application::getInstance();
            $router = $app->make('router');
            $kernel = new Kernel($app, $router);

            $kernelAliases = $kernel->getMiddlewareAliases();
            $middleware->alias(
                $kernelAliases
            );
        }
    )
    ->withExceptions(
    )->create()
;
