<?php

namespace App\Events\Chat;

use App\Models\Hr\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOnlineEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly User $user)
    {
    }

    /**
     * Canal privado do usuário: private-chat.user.{id}
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.user.' . $this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.online';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'name' => $this->user->name,
        ];
    }
}