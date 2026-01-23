<?php

namespace App\Services\System;

use App\Models\Hr\User;
use App\Models\System\SystemMessage;
use App\Models\System\UserNotification;
use Illuminate\Support\Str;

class NotificationRuleService
{
    public function checkAndNotify(User $user)
    {
        $rules = [
            'no_documents' => fn () => $user->documents()->count() === 0,
            'no_pix' => fn () => !$user->payment_methods()->where('payment_method_type', 'pix')->exists(),
        ];

        foreach ($rules as $slug => $ruleCheck) {
            if ($ruleCheck()) {
                $message = SystemMessage::where('key', $slug)->first();
                if (!$message) {
                    continue;
                }

                $alreadyNotified = UserNotification::where('user_id', $user->id)
                    ->where('system_message_id', $message->id)
                    ->exists()
                ;

                if (!$alreadyNotified) {
                    UserNotification::create([
                        'uuid' => Str::uuid(),
                        'user_id' => $user->id,
                        'system_message_id' => $message->id,
                    ]);

                    (new EmailNotificationService())->send($user, $message);
                }
            }
        }
    }
}
