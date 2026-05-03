<?php

namespace App\Models\System;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property string      $token
 * @property null|string $permissions
 * @property null|Carbon $token_expires_at
 * @property null|Carbon $last_used_at
 * @property null|string $last_used_ip
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $deleted_at
 */
class DeveloperAuth extends Model
{
    use SoftDeletes;

    protected $table = 'system.developer_auth';

    protected $fillable = [
        'name',
        'email',
        'token',
        'permissions',
        'token_expires_at',
        'last_used_at',
        'last_used_ip',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
    ];
}
