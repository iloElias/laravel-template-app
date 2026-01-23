<?php

namespace App\Http\Controllers;

use App\Http\Requests\Message\MessageStoreRequest;
use App\Models\Chat\Chat;
use App\Models\Hr\User;
use App\Services\Chat\ChatService;

class MessageController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {
    }

    public function store(MessageStoreRequest $request)
    {
        $user = User::auth();
        $validated = $request->validated();

        $chat = Chat::where('uuid', $validated['chat_uuid'])->first();

        $messages = [];

        if (!empty($validated['message'])) {
            $messages[] = $this->chatService->sendMessage($chat->id, $user->id, $validated['message'], $validated['answer_to'] ?? null);
        }
        foreach ($validated['messages'] as $messageData) {
            $messages[] = $this->chatService->sendMessage(
                $chat->id,
                $user->id,
                $messageData['message'],
                $messageData['answer_to'] ?? null
            );
        }

        return response()->json($messages, 201);
    }

    public function delete(string $uuid)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }
}
