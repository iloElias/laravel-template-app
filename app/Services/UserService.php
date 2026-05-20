<?php

namespace App\Services;

use App\Factories\SessionFactory;
use App\Factories\TokenFactory;
use App\Http\Requests\User\UserStoreRequest;
use App\Models\Hr\AuthCode;
use App\Models\Hr\DeviceAgent;
use App\Models\Hr\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct()
    {
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
        $data['password'] = Hash::make($data['password']);
        $data['uuid'] = Str::uuid()->toString();
        $user = User::create($data);

        $authCode = AuthCode::createCode($user->id, AuthCode::EMAIL);
        $browserAgent = DeviceAgent::where('fingerprint', $request->header('Device-Agent'))->first();

        $session = SessionFactory::create($user, $request, $browserAgent, $authCode);

        $jwt = TokenFactory::create($user, $session);

        return [
            'user' => $user,
            'token' => $jwt,
            'session' => $session,
        ];
    }
}
