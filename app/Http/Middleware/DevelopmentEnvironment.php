<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class DevelopmentEnvironment
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if (env('APP_ENV') !== 'local' || env('ENVIRONMENT') !== 'development') {
            return response()->json(['message' => 'Not allowed environment'], 404);
        }

        return $next($request);
    }
}
