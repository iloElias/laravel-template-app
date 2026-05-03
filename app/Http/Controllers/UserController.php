<?php

namespace App\Http\Controllers;

use App\Exception\InvalidFormException;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Requests\User\UserPasswordCreateRequest;
use App\Http\Requests\User\UserPasswordUpdateRequest;
use App\Http\Requests\User\UserResetPasswordRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\Hr\User;
use App\Services\AuthService;
use App\Services\PictureService;
use App\Services\UserDocumentService;
use App\Services\UserQueryService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected AuthService $authService,
        protected UserQueryService $userQueryService,
        protected PictureService $pictureService,
        protected UserDocumentService $userDocumentService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = $request->only(['id', 'telephone', 'name']);
        $user = $this->userQueryService->getUser($query);

        if ($user) {
            return response()->json($user);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->input('q', '');
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);

        $cacheKey = 'user:search:' . md5("{$search}|{$limit}|{$offset}");

        $users = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search) {
            return User::when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('surname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
                ->withPagination(defaultLimit: 10)
                ->get([
                    'uuid as value',
                    'name as label',
                    'email as description',
                    'profile_picture as image',
                ]);
        });

        return response()->json($users);
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        $result = $this->userService->createUser($request->validated(), $request);

        return response()->json($result, 201);
    }

    public function update(UserUpdateRequest $request): JsonResponse
    {
        $user = User::auth();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update($request->validated());

        Cache::forget("user:info:{$user->uuid}");

        return response()->json($user);
    }

    public function password(UserPasswordUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::auth();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!Hash::check($data['current_password'], $user->password)) {
            throw new InvalidFormException(__('validation.current_password'), ['current_password' => __('validation.current_password')]);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return response()->json($user);
    }

    public function passwordCreate(UserPasswordCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::auth();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->password !== '') {
            throw new InvalidFormException(__('validation.password_already_set'), ['current_password' => __('validation.password_already_set')]);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return response()->json($user);
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated(), $request);

        return response()->json($result);
    }

    public function resetPassword(UserResetPasswordRequest $request): JsonResponse
    {
        $result = $this->authService->resetPassword($request);

        return response()->json($result);
    }

    public function googleAuth(Request $request): JsonResponse
    {
        $result = $this->authService->google($request);

        return response()->json($result);
    }

    public function googleAuthV2(Request $request): JsonResponse
    {
        $result = $this->authService->googleV2($request);

        return response()->json($result);
    }

    public function resendCode(): JsonResponse
    {
        $result = $this->authService->resendCode();

        return response()->json($result);
    }

    public function authenticate(Request $request): JsonResponse
    {
        $result = $this->authService->authenticate($request);

        return response()->json($result);
    }

    public function authenticationMethods(): JsonResponse
    {
        $user = User::auth();
        $methods = [];

        if ($user->number_verified === true) {
            $methods[] = 'sms';
        }

        if ($user->email_verified === true) {
            $methods[] = 'email';
        }

        return response()->json($methods);
    }

    public function self(): JsonResponse
    {
        $user = User::auth();
        $session = User::session();

        return response()->json([
            'user' => $user->load(['documents', 'user_mercado_pago']),
            'authenticated' => $session->authenticated,
            'has_password' => !empty($user->password),
        ]);
    }

    public function info(string $uuid): JsonResponse
    {
        $user = Cache::remember("user:info:{$uuid}", now()->addMinutes(15), function () use ($uuid) {
            return $this->userQueryService->getInfo($uuid);
        });

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function postPicture(Request $request): JsonResponse
    {
        $user = User::auth();
        $result = $this->pictureService->uploadPicture($request, $user);

        return response()->json($result, 201);
    }

    public function exists(): JsonResponse
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function codeLength(): JsonResponse
    {
        $length = Cache::remember('auth:code-length', now()->addDay(), fn() => \App\Models\Hr\AuthCode::LENGTH);

        return response()->json($length);
    }
}