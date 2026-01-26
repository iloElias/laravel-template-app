<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\DeviceAgentController;
use App\Http\Controllers\CashOutController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\Integrations\MercadoPagoAuthController;
use App\Http\Controllers\Integrations\MercadoPagoController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\Transport\CarrierController;
use App\Http\Controllers\Transport\MachineryController;
use App\Http\Controllers\Transport\OfferController;
use App\Http\Controllers\Transport\RequestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['response.error', 'lang'])->group(function () {
    // Debug routes
    Route::middleware(['dev.env'])->prefix('/debug')->group(function () {
        Route::get('/', [DebugController::class, 'showEnvironment']);
        Route::prefix('/env')->group(function () {
            Route::get('/', [DebugController::class, 'getEnvironmentInstructions']);
            Route::get('/{variable}', [DebugController::class, 'getEnvironmentVariable']);
        });
        Route::get('/lasterror', [DebugController::class, 'getLastError']);
        Route::get('/dir', [DebugController::class, 'mapProjectFiles']);
        Route::get('/file', [DebugController::class, 'getFileContent']);
        Route::post('/body', [DebugController::class, 'showBody']);

        Route::prefix('/test')->group(function () {
            Route::prefix('/job')->group(function () {
                Route::get('/email', [DebugController::class, 'sendEmailJob']);
                // Route::get('/sms', [DebugController::class, 'sendSmsJob']);
            });
            Route::get('/email', [DebugController::class, 'sendEmail']);
            // Route::get('/sms', [DebugController::class, 'sendSms']);
        });
        Route::middleware(['dev.env'])->group(function () {
            include __DIR__ . '/testing/email.php';
        });
    });

    Route::middleware([])->prefix('/auth')->group(function () {
        Route::prefix('fingerprint')->group(function () {
            Route::get('/', [DeviceAgentController::class, 'makeFingerprint']);
            Route::middleware('fingerprint')->get('/validate', [DeviceAgentController::class, 'validate']);
        });
        // Route::post('/logout', [UserController::class, 'logout']);
        Route::middleware(['db.safe', 'fingerprint'])->group(function () {
            Route::get('/code-length', [UserController::class, 'codeLength']);
            Route::post('/reset-password', [UserController::class, 'resetPassword']);
            Route::post('/sign-in', [UserController::class, 'login']);
            Route::post('/sign-up', [UserController::class, 'store']);
            Route::post('/google-auth', [UserController::class, 'googleAuth']);
            Route::post('/google-auth/v2', [UserController::class, 'googleAuthV2']);
            Route::middleware(['auth.basic'])->group(function () {
                Route::get('/', [UserController::class, 'authenticate']);
                Route::get('/methods', [UserController::class, 'authenticationMethods']);
            });
            Route::middleware(['auth.basic'])->get('/resend-code', [UserController::class, 'resendCode']);
        });
    });
    Route::middleware(['db.safe', 'fingerprint'])->group(function () {
        Route::prefix('/user')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/search', [UserController::class, 'search']);
            Route::prefix('/info')->middleware(['auth.basic'])->group(function () {
                Route::get('/me', [UserController::class, 'self']);
                Route::get('/{uuid}', [UserController::class, 'info']);
            });
            Route::middleware(['auth'])->group(function () {
                Route::put('/', [UserController::class, 'update']);
                Route::put('/password', [UserController::class, 'password']);
                Route::post('/password', [UserController::class, 'passwordCreate']);
                Route::prefix('/picture')->group(function () {
                    Route::post('/upload', [UserController::class, 'postPicture']);
                });
            });
            Route::get('/exists', [UserController::class, 'exists']);
        });
        Route::middleware(['auth'])->group(function () {

        });
    });
    Route::middleware([])->prefix('/uploads')->group(function () {
        Route::prefix('/pictures/{userUuid}')->group(function () {
            Route::get('/{pictureUuid?}', [UserController::class, 'picture']);
        });

        Route::prefix('/attachments')->group(function () {
            Route::middleware(['db.safe', 'fingerprint', 'auth'])->group(function () {
                Route::get('/', [AssetController::class, 'recent']);
                Route::post('/', [AssetController::class, 'store']);
            });
            Route::get('/{uuid}', [AssetController::class, 'show']);
            Route::get('/{uuid}/miniature', [AssetController::class, 'miniature']);
        });
    });
});
