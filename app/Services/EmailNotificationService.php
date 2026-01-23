<?php

namespace App\Services\System;

use App\Mail\SystemMessageNotification;
use App\Models\Hr\User;
use App\Models\System\SystemMessage;
use App\Models\System\UserNotification;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    public function send(User $user, SystemMessage $message)
    {
        Mail::to($user->email)->send(new SystemMessageNotification([
            'user' => $user,
            'message' => [
                'subject' => __("notifications.{$message->key}.subject"),
                'title' => __("notifications.{$message->key}.title"),
                'content' => __("notifications.{$message->key}.content"),
            ],
        ]));

        UserNotification::where('user_id', $user->id)
            ->where('system_message_id', $message->id)
            ->update(['sent_email' => true])
        ;
    }
}
