<?php

namespace App\Models\System;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserNotification.
 *
 * Represents a notification sent to a user regarding a system message, including details about its read and email status.
 *
 * @property int         $id                Unique identifier for the notification.
 * @property string      $uuid              Universally Unique Identifier for the notification.
 * @property int         $user_id           Identifier of the user receiving the notification.
 * @property int         $system_message_id Identifier of the associated system message.
 * @property bool        $read              Indicates whether the notification has been read.
 * @property bool        $sent_email        Indicates whether an email notification has been sent.
 * @property null|Carbon $notified_at       The datetime when the notification was recorded.
 */
class UserNotification extends Model
{
    protected $table = 'system.user_notifications';

    protected $fillable = [
        'id',
        'uuid',
        'user_id',
        'system_message_id',
        'read',
        'sent_email',
        'notified_at',
    ];

    protected $casts = [
        'read' => 'boolean',
        'sent_email' => 'boolean',
        'notified_at' => 'datetime',
    ];
}
