<?php

namespace App\Http\Middleware;

use App\Jobs\LogRequestHistory;
use App\Models\Hr\User;
use Illuminate\Http\Request;

class LogRequestMiddleware
{
    private const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'token',
        'secret',
        'access_token',
        'refresh_token',
    ];

    public function handle(Request $request, \Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, mixed $response): void
    {
        $session = User::session();

        $payload = $request->except(self::SENSITIVE_FIELDS);

        LogRequestHistory::dispatch([
            'session_id' => $session ? $session->id : 0,
            'route' => $request->route()?->getName() ?? $request->path(),
            'method' => $request->method(),
            'payload' => !empty($payload) ? json_encode($payload) : null,
            'created_at' => now()->toDateTimeString(),
        ]);
    }
}
