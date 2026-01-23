<?php

namespace App\Support\Traits;

use App\Enums\UserError;
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

            if (gettype($decoded) === 'enum') {
                self::setLastError($decoded);

                return false;
            }

            if (!isset($decoded->sub)) {
                self::setLastError(UserError::INVALID_TOKEN);

                return false;
            }
            $user = self::where('id', $decoded->sub)->first();
            if (!$user) {
                self::setLastError(UserError::USER_NOT_FOUND);

                return false;
            }
            self::$user = $user;

            return $user;
        } catch (\Throwable) {
            self::setLastError(UserError::INVALID_TOKEN);

            return false;
        }
    }
}
