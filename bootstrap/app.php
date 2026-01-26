<?php

use App\Http\Middleware\DatabaseTransaction;
use App\Http\Middleware\DeveloperAuth;
use App\Http\Middleware\DevelopmentEnvironment;
use App\Http\Middleware\DeviceFingerprint;
use App\Http\Middleware\LanguageMiddleware;
use App\Http\Middleware\ResponseErrorMiddleware;
use App\Http\Middleware\SessionAuth;
use App\Http\Middleware\UserAuth;
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
            $middleware->alias([
                'auth' => UserAuth::class,
                'auth.basic' => SessionAuth::class,
                'lang' => LanguageMiddleware::class,
                'fingerprint' => DeviceFingerprint::class,
                'dev.env' => DevelopmentEnvironment::class,
                'dev.auth' => DeveloperAuth::class,
                'db.safe' => DatabaseTransaction::class,
                'response.error' => ResponseErrorMiddleware::class,
            ]);
        }
    )
    ->withExceptions()
    ->create()
;
