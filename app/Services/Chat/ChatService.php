<?php

namespace App\Services\Chat;

use App\Events\Chat\UserOnlineEvent;
use App\Models\Hr\User;

class ChatService
{
    /**
     * Notifica os demais clientes conectados que o usuário está online.
     * Emite o evento no canal privado `private-chat.user.{id}`.
     */
    public function notifyUserOnline(User $user): void
    {
        broadcast(new UserOnlineEvent($user))->toOthers();
    }
}
