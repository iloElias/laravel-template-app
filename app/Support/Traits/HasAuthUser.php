<?php

namespace App\Support\Traits;

use App\Models\Hr\User;

trait HasAuthUser
{
    use HasSession;

    protected static User $user;

    /**
     * Authenticates the user based on the provided token.
     */
    public static function auth(): false|self
    {
        if (!empty(self::$user)) {
            return self::$user;
        }

        try {
            $decoded = self::getDecodedToken();

            if (!$decoded || !isset($decoded->sub)) {
                return false;
            }
            $user = self::whereId($decoded->sub)->first();
            if (!$user) {
                return false;
            }
            self::$user = $user;

            return $user;
        } catch (\Throwable) {
            return false;
        }
    }
}
