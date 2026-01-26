<?php

namespace App\Support\Traits;

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

        if (!$decoded || !isset($decoded->sid)) {
            return false;
        }

        $session = Session::where('id', $decoded->sid)->first();
        if (!$session) {
            return false;
        }
        self::$session = $session;

        return $session;
    }
}
