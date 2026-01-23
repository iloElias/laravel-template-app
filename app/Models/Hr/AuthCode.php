<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * Class AuthCode.
 *
 * Represents an authentication code with associated attributes and logic.
 *
 * @property int         $id
 * @property string      $ip_address
 * @property string      $user_agent
 * @property string      $auth_type
 * @property bool        $authenticated
 * @property string      $code
 * @property int         $attempts
 * @property bool        $active
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $inactivated_at
 */
class AuthCode extends Model
{
    use HasFactory;
    use Notifiable;

    public const SMS = 'sms';

    public const EMAIL = 'email';

    public const MAX_ATTEMPTS = 3;

    public const LENGTH = 6;

    protected $table = 'hr.auth_code';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'auth_type',
        'authenticated',
        'code',
        'attempts',
        'active',
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    protected $attributes = [
        // 'attempts' => 0,
        'active' => true,
    ];

    /**
     * Generate a new authentication code for the user.
     *
     * @param self::EMAIL|self::SMS $userId
     *
     * @throws \Exception
     */
    public static function createCode(int $userId, string $authType): self
    {
        $authCode = null;

        if ($authType === self::SMS) {
            $authCode = AuthSms::createCode($userId);
        } elseif ($authType === self::EMAIL) {
            $authCode = AuthEmail::createCode($userId);
        }

        return $authCode;
    }

    /**
     * Craft a random code.
     */
    public static function generateCode(): string
    {
        return (env('APP_ENV') === 'local' || env('ENVIRONMENT') === 'development')
            ? '111111'
            : str_pad(rand(pow(10, self::LENGTH - 1), pow(10, self::LENGTH) - 1), self::LENGTH, '0', STR_PAD_LEFT);
    }
}
