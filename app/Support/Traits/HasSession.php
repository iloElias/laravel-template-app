<?php

namespace App\Support\Traits;

use App\Enums\UserError;
use App\Models\Hr\Session;

trait HasSession
{
    use HasToken;

    protected static Session $session;

    public static function session(): false|Session
    {
        if (!empty(self::$session)) {
            return self::$session;
        }
        $decoded = self::getDecodedToken();

        if (gettype($decoded) === 'enum') {
            return $decoded;
        }

        $session = Session::where('id', $decoded->sid)->first();
        if (!$session) {
            self::setLastError(UserError::SESSION_NOT_FOUND);

            return false;
        }
        self::$session = $session;

        return $session;
    }
}
