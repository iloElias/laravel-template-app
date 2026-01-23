<?php

namespace App\Services;

use App\Enums\UserAction;
use App\Factories\SessionFactory;
use App\Factories\TokenFactory;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Responses\User\UserDataResponse;
use App\Models\Error;
use App\Models\Hr\AuthCode;
use App\Models\Hr\BrowserAgent;
use App\Models\Hr\RememberBrowser;
use App\Models\Hr\User;
use App\Services\Chat\ChatService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserService
{
    private ChatService $chatService;
    public function __construct()
    {
        $this->chatService = new ChatService();
    }

    /**
     * Creates a new user and starts a session.
     *
     * @param array            $data    user data to be inserted
     * @param UserStoreRequest $request request instance
     *
     * @return array result with user and token or error
     *
     * @throws ValidationException
     */
    public function createUser(array $data, UserStoreRequest $request): array
    {
        if (!empty($validated)) {
            throw new ValidationException($validated);
        }

        $data['password'] = Hash::make($data['password']);
        $data['uuid'] = Str::uuid()->toString();
        $user = User::create($data);

        $authCode = AuthCode::createCode($user->id, AuthCode::EMAIL);
        $browserAgent = BrowserAgent::where('fingerprint', $request->header('Browser-Agent'))->first();

        $session = SessionFactory::create($user, $request, $browserAgent, $authCode);

        $chat = $this->chatService->createChatWithSupport($user->id);
        $supportUser = User::where('email', 'contact.agrofast@gmail.com')->first();
        $this->chatService->sendMessage($chat->id, $supportUser->id, 'Olá, bem-vindo ao Terramov! Se precisar de ajuda, estamos à disposição.');

        if (!empty($data['remember']) && $data['remember'] === 'true') {
            RememberBrowser::create([
                'user_id' => $user->id,
                'browser_agent_id' => $browserAgent->id,
            ]);
        }

        $jwt = TokenFactory::create($user, $session);

        return [
            ...UserDataResponse::withDocument($user),
            'token' => $jwt,
            'session' => $session,
            'auth' => UserAction::AUTHENTICATE->value,
        ];
    }
}
