<?php

namespace App\Models\Hr;

use App\Jobs\SendMail;
use App\Mail\AuthenticationMail;
use App\Mail\FirstLoginMail;
use App\Models\Tracker;

class AuthEmail
{
    /**
     * Generate a new authentication code for the user.
     *
     * @throws \Exception
     */
    public static function createCode(int $userId): AuthCode
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception('User not found');
        }
        if (!self::validateEmail($user->email)) {
            throw new \Exception('Invalid Email');
        }
        $code = AuthCode::generateCode();
        AuthCode::where('user_id', $userId)->update(['active' => false]);

        $authCodeParams = [
            'user_id' => $userId,
            'ip_address' => Tracker::ip(),
            'user_agent' => request()->userAgent(),
            'auth_type' => AuthCode::EMAIL,
            'code' => $code,
        ];
        $authCode = AuthCode::create($authCodeParams);

        $mailData = [
            'user' => $user,
            'info' => [
                'code' => $code,
                'expires' => now()->addMinutes(10),
            ],
        ];

        if (!$user->email_verified) {
            SendMail::dispatch($user->email, FirstLoginMail::class, $mailData);
        } else {
            SendMail::dispatch($user->email, AuthenticationMail::class, $mailData);
        }

        return $authCode;
    }

    /**
     * Validate a phone Email (simple example).
     */
    private static function validateEmail(string $email): bool
    {
        return !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
