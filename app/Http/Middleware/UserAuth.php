<?php

namespace App\Http\Middleware;

use App\Enums\UserError;
use App\Models\Hr\Session;
use App\Models\Hr\User;

class UserAuth
{
    public function handle($request, \Closure $next)
    {
        $user = User::auth();

        if (!$user) {
            return response()->json(['code' => User::getLastError()], 401);
        }

        $decodedToken = User::getDecodedToken();

        $session = Session::where([
            'id' => $decodedToken->sid,
        ])->first();

        if ($session->authenticated === false) {
            return response()->json(['code' => UserError::INVALID_TOKEN], 401);
        }

        return $next($request);
    }
}
