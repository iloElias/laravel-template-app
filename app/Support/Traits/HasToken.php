<?php

namespace App\Support\Traits;

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
            return false;
        }

        try {
            $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));
            self::$decodedToken = $decoded;

            return $decoded;
        } catch (\Throwable) {
        }
        return false;
    }
}
