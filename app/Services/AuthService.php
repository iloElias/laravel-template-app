<?php

namespace App\Services;

use App\Exception\InvalidFormException;
use App\Exception\InvalidRequestException;
use App\Factories\SessionFactory;
use App\Factories\TokenFactory;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Requests\User\UserResetPasswordRequest;
use App\Jobs\SendMail;
use App\Mail\ResetPasswordMail;
use App\Models\Hr\AuthCode;
use App\Models\Hr\DeviceAgent;
use App\Models\Hr\User;
use App\Services\Google\GoogleAuthService;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    private GoogleAuthService $googleAuthService;

    public function __construct()
    {
        $this->googleAuthService = new GoogleAuthService();
    }

    /**
     * Logs in the user.
     *
     * @param array   $credentials Authentication data (email, password, remember)
     * @param Request $request     Request instance
     *
     * @return array Result containing user, token, session, or error
     */
    public function login(array $credentials, UserLoginRequest $request): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!Hash::check($credentials['password'], $user->password)) {
            throw new InvalidFormException(__('validation.current_password'), ['password' => __('validation.current_password')]);
        }

        $browserFingerprint = $request->header('Device-Agent');
        $browserAgent = DeviceAgent::where('fingerprint', $browserFingerprint)->first();

        // Lógica 2FA: código obrigatório no primeiro login (!email_verified)
        // ou quando 2FA está ativo (email_two_factor_auth=true)
        $needsCode = !$user->email_verified || $user->email_two_factor_auth;
        $authCode = $needsCode ? AuthCode::createCode($user->id, AuthCode::EMAIL) : null;

        $session = SessionFactory::create($user, $request, $browserAgent, $authCode);

        // Se não precisa de código, já autentica direto
        if (!$needsCode) {
            $session->update(['authenticated' => true]);
        }

        $jwt = TokenFactory::create($user, $session);

        return [
            'user' => $user,
            'token' => $jwt,
        ];
    }

    /**
     * @return array the result of the resend operation
     *
     * @throws ValidationException if the user is not authenticated or the session is not found
     */
    public function resendCode()
    {
        $user = User::auth();

        $session = User::session();

        $authCode = AuthCode::where('id', $session->auth_code_id)
            ->where('auth_type', AuthCode::EMAIL)
            ->first()
        ;
        if (!$authCode) {
            throw new ValidationException('invalid_authentication_code');
        }

        $timeoutSeconds = 60;
        if (Carbon::now()->diffInSeconds($authCode->created_at) < $timeoutSeconds) {
            throw new ValidationException('resend_timeout');
        }

        $attempts = $authCode->attempts;

        $authCode->update(['active' => false]);

        $newAuthCode = AuthCode::createCode($user->id, AuthCode::EMAIL);
        $newAuthCode->update(['attempts' => $attempts]);

        $session->update(['auth_code_id' => $newAuthCode->id]);

        return [
            'user' => $user,
        ];
    }

    /**
     * Performs user authentication using the verification code.
     *
     * @param Request $request Request instance
     *
     * @return array Result containing user and new token, or error
     */
    public function authenticate(Request $request)
    {
        $user = User::auth();

        $session = User::session();

        $authCode = AuthCode::where('id', $session->auth_code_id)
            ->where('auth_type', AuthCode::EMAIL)
            ->first()
        ;

        if (!$authCode) {
            throw new ValidationException('invalid_authentication_code');
        }

        $codeInput = $request->input('code');
        if ($authCode->code !== $codeInput) {
            $updatedAttempts = $authCode->attempts + 1;
            if ($updatedAttempts >= AuthCode::MAX_ATTEMPTS) {
                $authCode->update(['attempts' => $updatedAttempts, 'active' => false]);
                $session->update(['authenticated' => false, 'active' => false]);

                throw new ValidationException('authentication_code_attempts_exceeded', 401);
            }
            $authCode->update(['attempts' => $updatedAttempts]);

            throw new InvalidRequestException('incorrect_authentication_code', [
                'code' => 'incorrect_authentication_code',
                'attempts' => AuthCode::MAX_ATTEMPTS - $updatedAttempts,
            ], 400);
        }

        $authCode->update(['active' => false]);
        $session->update(['authenticated' => true]);

        if ($authCode->auth_type === AuthCode::EMAIL) {
            $user->update(['email_verified' => true, 'email_verified_at' => Carbon::now()]);
        } elseif ($authCode->auth_type === AuthCode::SMS) {
            $user->update(['number_verified' => true, 'number_verified_at' => Carbon::now()]);
        }

        if ($session->storageGet('reset_password')) {
            $session->storageUnset('reset_password');
            $user->update([
                'password' => '',
            ]);
        }

        $jwt = TokenFactory::create($user, $session);

        return [
            'user' => $user,
            'token' => $jwt,
        ];
    }

    public function resetPassword(UserResetPasswordRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            throw new ValidationException('user_not_found');
        }

        $browserFingerprint = $request->header('Device-Agent');
        $browserAgent = DeviceAgent::where('fingerprint', $browserFingerprint)->first();

        // Reset password sempre requer código de verificação
        $authCode = AuthCode::createCode($user->id, AuthCode::EMAIL);

        $session = SessionFactory::create($user, $request, $browserAgent, $authCode);

        SendMail::dispatch($user->email, ResetPasswordMail::class, [
            'user_id' => $user->id,
            'code' => $authCode->code,
        ]);

        $session->storageSet([
            'reset_password' => true,
        ]);

        $jwt = TokenFactory::create($user, $session);

        return [
            'user' => $user,
            'token' => $jwt,
        ];
    }

    public function google(Request $request)
    {
        $payload = $this->googleAuthService->verifyToken($request->input('credential'));

        return $this->loginWithGooglePayload($request, $payload);
    }

    public function googleV2(Request $request)
    {
        $payload = $this->googleAuthService->verifyAccessToken($request->input('access_token'));

        return $this->loginWithGooglePayload($request, $payload);
    }

    protected function loginWithGooglePayload(Request $request, $payload)
    {
        if (!$payload) {
            return response()->json(['message' => 'Invalid Google token'], 401);
        }

        $user = User::where('email', $payload['email'])->first();

        if (!$user) {
            $data = $this->googleAuthService->createUserFromGoogle($request, $payload);
        } else {
            $data = $this->googleAuthService->loginFromGoogle($user, $request, $payload);
        }

        // Se a sessão não requer código (authCode=null), marca como autenticada
        if (!$data['session']->auth_code_id) {
            $data['session']->update(['authenticated' => true]);
        }

        return [
            'token' => $data['token'],
            'user' => $data['user'],
        ];
    }
}
