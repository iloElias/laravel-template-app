<?php

namespace App\Http\Controllers;

use App\Exception\InvalidFormException;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Requests\User\UserPasswordCreateRequest;
use App\Http\Requests\User\UserPasswordUpdateRequest;
use App\Http\Requests\User\UserProfileTypeRequest;
use App\Http\Requests\User\UserResetPasswordRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Responses\User\UserDataResponse;
use App\Models\Hr\AuthCode;
use App\Models\Hr\User;
use App\Services\AuthService;
use App\Services\PictureService;
use App\Services\UserDocumentService;
use App\Services\UserQueryService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $userService;

    protected $authService;

    protected $userQueryService;

    protected $pictureService;
    protected $userDocumentService;

    public function __construct(
        UserService $userService,
        AuthService $authService,
        UserQueryService $userQueryService,
        PictureService $pictureService,
        UserDocumentService $userDocumentService
    ) {
        $this->userService = $userService;
        $this->authService = $authService;
        $this->userQueryService = $userQueryService;
        $this->pictureService = $pictureService;
        $this->userDocumentService = $userDocumentService;
    }

    public function index(Request $request)
    {
        $query = $request->only(['id', 'telephone', 'name']);
        $user = $this->userQueryService->getUser($query);

        if ($user) {
            return response()->json($user, 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    public function search(Request $request)
    {
        $query = $request->only(['q', 'limit', 'offset']);

        return User::where('name', 'like', '%' . ($query['q'] ?? 'CHARACTER COMBINATION THAT SHOULD NEVER BE USED') . '%')
            ->orWhere('surname', 'like', '%' . ($query['q'] ?? 'CHARACTER COMBINATION THAT SHOULD NEVER BE USED') . '%')
            ->orWhere('email', 'like', '%' . ($query['q'] ?? 'CHARACTER COMBINATION THAT SHOULD NEVER BE USED') . '%')
            ->limit($query['limit'] ?? 10)->offset($query['offset'] ?? 0)
            ->get([
                'uuid as value',
                'name as label',
                'email as description',
                'profile_picture as image',
            ])
        ;
    }

    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();
        $result = $this->userService->createUser($data, $request);

        return response()->json($result, 201);
    }

    public function update(UserUpdateRequest $request)
    {
        $data = $request->validated();
        $user = User::auth();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update($data);

        return response()->json(UserDataResponse::withDocument($user), 200);
    }

    public function password(UserPasswordUpdateRequest $request)
    {
        $data = $request->validated();
        $user = User::auth();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!Hash::check($data['current_password'], $user->password)) {
            throw new InvalidFormException(__('validation.current_password'), ['current_password' => __('validation.current_password')]);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return response()->json(UserDataResponse::withDocument($user), 200);
    }

    public function passwordCreate(UserPasswordCreateRequest $request)
    {
        $data = $request->validated();
        $user = User::auth();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->password !== '') {
            throw new InvalidFormException("Senha já está definida", ['current_password' => "Senha já está definida"]);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return response()->json(UserDataResponse::withDocument($user), 200);
    }

    public function login(UserLoginRequest $request)
    {
        $credentials = $request->validated();

        $result = $this->authService->login($credentials, $request);

        return response()->json($result, 200);
    }

    public function resetPassword(UserResetPasswordRequest $request)
    {
        $result = $this->authService->resetPassword($request);

        return response()->json($result, 200);
    }

    public function googleAuth(Request $request)
    {
        $result = $this->authService->google($request);

        return response()->json($result, 200);
    }

    public function googleAuthV2(Request $request)
    {
        $result = $this->authService->googleV2($request);

        return response()->json($result, 200);
    }

    public function resendCode()
    {
        $result = $this->authService->resendCode();

        return response()->json($result, 200);
    }

    public function authenticate(Request $request)
    {
        $result = $this->authService->authenticate($request);

        return response()->json($result, 200);
    }

    public function setProfileType(UserProfileTypeRequest $request)
    {
        $user = User::auth();

        $user->update(['profile_type' => $request->input('profile_type')]);

        return response()->json($user->load(['documents']), 200);
    }

    public function authenticationMethods()
    {
        $user = User::auth();
        $methods = [];

        if ($user->number_verified === true) {
            $methods[] = 'sms';
        }

        if ($user->email_verified === true) {
            $methods[] = 'email';
        }

        return response()->json($methods, 200);
    }

    public function self()
    {
        $user = User::auth();

        $session = User::session();

        return response()->json([
            'user' => $user->load(['documents', 'user_mercado_pago']),
            'authenticated' => $session->authenticated,
            'has_password' => !empty($user->password),
        ]);
    }

    public function info($uuid)
    {
        $user = $this->userQueryService->getInfo($uuid);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user, 200);
    }

    public function picture($userUuid, $pictureUuid = null)
    {
        $result = $this->pictureService->getPicture($userUuid, $pictureUuid);

        return response($result['file'], 200)->header('Content-Type', $result['mime']);
    }

    public function postPicture(Request $request)
    {
        $user = User::auth();

        $result = $this->pictureService->uploadPicture($request, $user);

        return response()->json($result, 201);
    }

    public function exists()
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function codeLength()
    {
        return response()->json(AuthCode::LENGTH, 200);
    }

    public function profileType()
    {
        $user = User::auth();
        $user->update(['profile_type' => request('profile_type')]);

        return response()->json(UserDataResponse::withDocument($user), 200);
    }
}
