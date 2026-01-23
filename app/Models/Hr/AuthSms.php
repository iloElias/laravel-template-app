<?php

namespace App\Models\Hr;

use App\Jobs\SendSms;
use App\Models\Tracker;
use Illuminate\Database\Eloquent\Model;

class AuthSms extends Model
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
        if (!self::validatePhoneNumber($user->number)) {
            throw new \Exception('Invalid phone number');
        }
        $code = AuthCode::generateCode();
        self::where('user_id', $userId)->update(['active' => false]);

        $authCodeParams = [
            'user_id' => $userId,
            'ip_address' => Tracker::ip(),
            'user_agent' => request()->userAgent(),
            'auth_type' => AuthCode::EMAIL,
            'code' => $code,
        ];
        $authCode = AuthCode::create($authCodeParams);

        $smsEnabled = env('SMS_SERVICE_ENABLED', false);
        // Added this verification to avoid sending SMS in local environment. It's really expensive XD.
        if ($smsEnabled === true || $smsEnabled === 'true') {
            SendSms::dispatch($user->number, __('sms.authentication.message', ['code' => $code]));
        }

        return $authCode;
    }

    /**
     * Validate a phone number (simple example).
     */
    private static function validatePhoneNumber(string $number): bool
    {
        return !empty($number);
    }
}
