<?php

namespace App\Http\Middleware;

use App\Models\Hr\User;

class SessionAuth
{
    public function handle($request, \Closure $next)
    {
        $session = User::session();

        if (!$session) {
            return response()->json(['code' => User::getLastError()], 401);
        }

        return $next($request);
    }
}
