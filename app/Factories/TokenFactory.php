<?php

namespace App\Factories;

use App\Models\Hr\Session;
use App\Models\Hr\User;
use Firebase\JWT\JWT;

class TokenFactory
{
    public static function create(User $user, Session $session): string
    {
        return JWT::encode(
            [
                'iss' => env('APP_URL'),
                'sub' => $user->id,
                'sid' => $session->id,
                'aud' => env('APP_NAME') . '-services',
                'iat' => now()->timestamp,
                'jti' => uniqid(),
            ],
            env('APP_KEY'),
            'HS256'
        );
    }
}
