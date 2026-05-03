<?php

namespace App\Models\Hr;

use App\Models\DynamicQuery;
use App\Models\LastError;
use App\Support\Traits\HasAuthUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * Class User.
 *
 * Represents a system user with associated attributes and logic.
 *
 * @property int         $id
 * @property string      $uuid
 * @property string      $name
 * @property null|string $surname
 * @property string      $email
 * @property null|string $number
 * @property string      $password
 * @property string      $language
 * @property null|string $profile_picture
 * @property null|string $remember_token
 * @property bool        $email_two_factor_auth
 * @property bool        $email_verified
 * @property null|Carbon $email_verified_at
 * @property bool        $number_two_factor_auth
 * @property bool        $number_verified
 * @property null|Carbon $number_verified_at
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $deleted_at
 */
class User extends DynamicQuery
{
    use HasAuthUser;
    use HasFactory;
    use LastError;
    use Notifiable;
    use SoftDeletes;

    protected $table = 'hr.user';

    protected $fillable = [
        'uuid',
        'name',
        'surname',
        'number',
        'email',
        'password',
        'language',
        'profile_picture',
        'remember_token',
        'email_two_factor_auth',
        'email_verified',
        'email_verified_at',
        'number_two_factor_auth',
        'number_verified',
        'number_verified_at',
    ];

    protected $casts = [
        'email_two_factor_auth' => 'boolean',
        'email_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'number_two_factor_auth' => 'boolean',
        'number_verified' => 'boolean',
        'number_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}