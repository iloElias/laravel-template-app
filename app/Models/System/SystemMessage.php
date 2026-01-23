<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class SystemMessage.
 *
 * Represents a system message used for delivering various notifications or alerts within the system.
 *
 * @property int         $id             Unique identifier for the notification.
 * @property string      $uuid           Unique identifier for the system message.
 * @property string      $key            Key used to identify the message.
 * @property string      $type           Type of the message (e.g., error, warning, info).
 * @property bool        $active         Indicates whether the message is active.
 * @property null|string $inactivated_at Timestamp when the message was deactivated.
 * @property Carbon      $created_at     Timestamp when the message was created.
 * @property Carbon      $updated_at     Timestamp when the message was last updated.
 */
class SystemMessage extends Model
{
    protected $table = 'system.system_message';

    protected $fillable = [
        'id',
        'uuid',
        'key',
        'type',
        'active',
        'inactivated_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'inactivated_at' => 'datetime',
    ];
}
