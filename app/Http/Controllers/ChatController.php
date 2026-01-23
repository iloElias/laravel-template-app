<?php

namespace App\Http\Controllers;

use App\Models\Chat\Chat;
use App\Models\Hr\User;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {
    }

    public function index()
    {
        $user = User::auth();
        $chats = $user->chats()->get();

        return response()->json($chats);
    }

    public function show(string $uuid)
    {
        $user = User::auth();

        if ($uuid === "support") {
            $chat = $this->chatService->createChatWithSupport($user->id);
        } else {
            $chat = Chat::where('uuid', $uuid)->first();
        }

        if (!$this->chatService->userBelongsToChat($user->id, $chat->id)) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        $chat = Chat::where('uuid', $chat->uuid)
            ->with([
                'users',
                'messages' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(40);
                },
                'messages.answer_to',
            ])
            ->first()
        ;

        return response()->json($chat);
    }

    public function with(Request $request)
    {
        $user = User::auth();
        $otherUserUuid = $request->input('user_uuid');
        $otherUser = User::where('uuid', $otherUserUuid)->first();

        if (!$otherUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $chat = $this->chatService->createPrivateChat($user->id, $otherUser->id);

        return response()->json($chat);
    }
}
