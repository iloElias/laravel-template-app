<?php

namespace App\Services\Google;

use App\Exception\InvalidRequestException;
use App\Factories\SessionFactory;
use App\Factories\TokenFactory;
use App\Models\Hr\BrowserAgent;
use App\Models\Hr\Session;
use App\Models\Hr\User;
use App\Services\Chat\ChatService;
use Google\Auth\AccessToken;
use Google\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class GoogleAuthService
{
    protected $client;
    protected $chatService;
    protected $accessToken;

    public function __construct()
    {
        $this->client = new Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        $this->chatService = new ChatService();
        $this->accessToken = new AccessToken();
    }

    /**
     * Verify the Google ID token.
     *
     * @param string $idToken
     *
     * @return array{iss: string,azp: string,aud: string,sub: string,email: string,email_verified: bool,nbf: int,name: string,picture: string,given_name: string,familly_name: string,iat: int,exp: int,jti: string}
     *
     * @throws InvalidRequestException
     */
    public function verifyToken($idToken)
    {
        $payload = $this->client->verifyIdToken($idToken);

        if (!$payload) {
            throw new InvalidRequestException('Invalid Google token', [], Response::HTTP_UNAUTHORIZED);
        }

        return $payload;
    }

    public function verifyAccessToken($accessToken)
    {
        $response = Http::get('https://www.googleapis.com/oauth2/v2/userinfo', [
            'alt' => 'json',
            'access_token' => $accessToken,
        ]);

        if ($response->failed()) {
            throw new InvalidRequestException('Invalid Google token', [], Response::HTTP_UNAUTHORIZED);
        }

        return $response->json();
    }

    /**
     * Summary of createUserFromGoogle.
     *
     * @param mixed $payload
     *
     * @return array{session: Session, token: string, user: User}
     */
    public function createUserFromGoogle(Request $request, $payload)
    {
        $user = User::create([
            'uuid' => Str::uuid()->toString(),
            'email' => $payload['email'],
            'email_verified' => $payload['email_verified'] ?? $payload['verified_email'] ?? false,
            'password' => '',
            'name' => $payload['given_name'],
            'surname' => $payload['family_name'] ?? '',
            'profile_picture' => $payload['picture'] ?? null,
        ]);
        $browserAgent = BrowserAgent::where('fingerprint', $request->header('Browser-Agent'))->first();
        $session = SessionFactory::create($user, $request, $browserAgent, null);
        $jwt = TokenFactory::create($user, $session);

        $chat = $this->chatService->createChatWithSupport($user->id);
        $supportUser = User::where('email', 'contact.agrofast@gmail.com')->first();
        $this->chatService->sendMessage($chat->id, $supportUser->id, 'Olá, bem-vindo ao Terramov! Se precisar de ajuda, estamos à disposição.');

        return [
            'token' => $jwt,
            'session' => $session,
            'user' => $user,
        ];
    }

    /**
     * Summary of loginFromGoogle.
     *
     * @param mixed $payload
     *
     * @return array{session: Session, token: string, user: User}
     */
    public function loginFromGoogle(User $user, Request $request, $payload)
    {
        $browserAgent = BrowserAgent::where('fingerprint', $request->header('Browser-Agent'))->first();
        $session = SessionFactory::create($user, $request, $browserAgent, null);
        $jwt = TokenFactory::create($user, $session);

        $user->update([
            'profile_picture' => $user->profile_picture ?? $payload['picture'],
            'email_verified' => $payload['email_verified'] ?? $payload['verified_email'] ?? false,
        ]);

        return [
            'token' => $jwt,
            'session' => $session,
            'user' => $user,
        ];
    }
}
