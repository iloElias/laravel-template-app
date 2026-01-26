<?php

namespace App\Http\Middleware;

use App\Utils;
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
        if (Utils::isProduction()) {
            return response()->json(['message' => 'Not allowed environment'], 404);
        }

        return $next($request);
    }
}
