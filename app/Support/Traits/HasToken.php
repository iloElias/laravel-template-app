<?php

namespace App\Support\Traits;

use App\Enums\UserError;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

trait HasToken
{
    protected static \stdClass $decodedToken;

    public static function getDecodedToken(): false|\stdClass
    {
        if (!empty(self::$decodedToken)) {
            return self::$decodedToken;
        }

        $token = request()->bearerToken();
        if (empty($token)) {
            self::setLastError(UserError::MISSING_TOKEN->value);

            return false;
        }

        $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));
        self::$decodedToken = $decoded;

        return $decoded;
    }
}
