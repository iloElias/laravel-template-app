<?php

namespace App\Services\Chat;

use App\Exception\InvalidFormException;
use App\Models\Chat\Chat;
use App\Models\Chat\ChatUser;
use App\Models\Chat\Message;
use App\Models\Hr\User;
use Illuminate\Support\Str;

class ChatService
{
    public function createPrivateChat(int $userA, int $userB, ?string $firstMessage = null): Chat
    {
        if ($userA === $userB) {
            throw new InvalidFormException('Não é possível criar um chat privado consigo mesmo.', [
                'user_uuid' => ['O usuário especificado é inválido.'],
            ]);
        }
        $existingChat = Chat::whereHas('users', function ($q) use ($userA, $userB) {
            $q->whereIn('user_id', [$userA, $userB]);
        }, '=', 2)
            ->whereDoesntHave('users', function ($q) use ($userA, $userB) {
                $q->whereNotIn('user_id', [$userA, $userB]);
            })
            ->first()
        ;

        if ($existingChat) {
            return $existingChat;
        }

        $chat = Chat::create([
            'uuid' => (string) Str::uuid(),
            'active' => true,
        ]);

        foreach ([$userA, $userB] as $userId) {
            ChatUser::create([
                'chat_id' => $chat->id,
                'user_id' => $userId,
            ]);
        }

        if ($firstMessage) {
            Message::create([
                'uuid' => (string) Str::uuid(),
                'chat_id' => $chat->id,
                'user_id' => $userA,
                'message' => $firstMessage,
            ]);
        }

        return $chat;
    }

    public function sendMessage(int $chatId, int $userId, string $content, null|int|string $answerTo = null): Message
    {
        $message = ((bool) $answerTo) ? ($answerTo === null ? null : Message::where('active', true)->where([
            (is_int($answerTo) || (is_string($answerTo) && ctype_digit($answerTo))) ? 'id' : 'uuid' => $answerTo,
        ])->first()) : null;

        $chat = Chat::where('id', $chatId)->where('active', true)->first();
        if (!$chat) {
            throw new InvalidFormException('Chat não encontrado ou inativo.', [
                'chat_uuid' => ['O chat especificado não existe ou está inativo.'],
            ]);
        }

        if (!$this->userBelongsToChat($userId, $chatId)) {
            throw new InvalidFormException('Usuário não pertence a este chat.', [
                'chat_uuid' => ['O usuário não pertence a este chat.'],
            ]);
        }

        $message = Message::create([
            'uuid' => (string) Str::uuid(),
            'chat_id' => $chat->id,
            'user_id' => $userId,
            'message' => $content,
            'answer_to' => $message ? $message->id : null,
            'active' => true,
        ]);

        $chat->touch();

        return $message->load(['user', 'answer_to']);
    }

    public function userBelongsToChat(string $userId, string $chatId): bool
    {
        $chat = Chat::find($chatId);
        if (!$chat) {
            return false;
        }

        return $chat->users()->where('user_id', $userId)->exists();
    }

    public function createChatWithSupport($forUserId)
    {
        $supportUser = User::where('email', 'contact.agrofast@gmail.com')->first();
        if (!$supportUser) {
            throw new InvalidFormException('Usuário de suporte não encontrado.', [
                'user_uuid' => ['O usuário de suporte não existe.'],
            ]);
        }

        return $this->createPrivateChat($supportUser->id, $forUserId);
    }
}
