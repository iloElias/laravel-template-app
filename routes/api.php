<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\DeviceAgentController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['response.error', 'lang'])->group(function () {
    // Health check routes
    Route::prefix('/health')->name('health.')->group(function () {
        Route::get('/api', [HealthCheckController::class, 'api'])->name('api');
        Route::get('/database', [HealthCheckController::class, 'database'])->name('database');
        Route::get('/cache', [HealthCheckController::class, 'cache'])->name('cache');
        Route::get('/queue', [HealthCheckController::class, 'queue'])->name('queue');
    });

    // Debug routes (development environment only)
    Route::middleware(['dev.env'])->prefix('/debug')->name('debug.')->group(function () {
        Route::get('/', [DebugController::class, 'showEnvironment'])->name('show');
        Route::prefix('/env')->name('env.')->group(function () {
            Route::get('/', [DebugController::class, 'getEnvironmentInstructions'])->name('index');
            Route::get('/{variable}', [DebugController::class, 'getEnvironmentVariable'])->name('show');
        });
        Route::get('/lasterror', [DebugController::class, 'getLastError'])->name('lasterror');
        Route::prefix('/test')->name('test.')->group(function () {
            Route::get('/email', [DebugController::class, 'sendEmail'])->name('email');
            Route::prefix('/job')->name('job.')->group(function () {
                Route::get('/email', [DebugController::class, 'sendEmailJob'])->name('email');
            });
        });
        include __DIR__ . '/testing/email.php';
    });

    // Authentication routes
    Route::prefix('/auth')->name('auth.')->group(function () {
        Route::prefix('/fingerprint')->name('fingerprint.')->group(function () {
            Route::get('/', [DeviceAgentController::class, 'makeFingerprint'])->name('create');
            Route::get('/validate', [DeviceAgentController::class, 'validate'])->name('validate')->middleware('fingerprint');
        });
        Route::middleware(['db.safe', 'fingerprint'])->group(function () {
            Route::get('/code-length', [UserController::class, 'codeLength'])->name('code-length');
            Route::post('/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
            Route::post('/sign-in', [UserController::class, 'login'])->name('login');
            Route::post('/sign-up', [UserController::class, 'store'])->name('register');
            Route::post('/google-auth', [UserController::class, 'googleAuth'])->name('google-auth');
            Route::post('/google-auth/v2', [UserController::class, 'googleAuthV2'])->name('google-auth.v2');
            Route::middleware(['auth.basic'])->group(function () {
                Route::get('/', [UserController::class, 'authenticate'])->name('authenticate');
                Route::get('/methods', [UserController::class, 'authenticationMethods'])->name('methods');
                Route::get('/resend-code', [UserController::class, 'resendCode'])->name('resend-code');
            });
        });
    });

    // User routes
    Route::middleware(['db.safe', 'fingerprint'])->prefix('/user')->name('user.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/exists', [UserController::class, 'exists'])->name('exists');
        Route::get('/search', [UserController::class, 'search'])->name('search');
        Route::prefix('/info')->middleware(['auth.basic'])->name('info.')->group(function () {
            Route::get('/me', [UserController::class, 'self'])->name('me');
            Route::get('/{uuid}', [UserController::class, 'info'])->name('show');
        });
        Route::middleware(['auth'])->group(function () {
            Route::put('/', [UserController::class, 'update'])->name('update');
            Route::post('/password', [UserController::class, 'passwordCreate'])->name('password.create');
            Route::put('/password', [UserController::class, 'password'])->name('password.update');
            Route::post('/picture', [UserController::class, 'postPicture'])->name('picture.upload');
        });
    });

    // Upload/Attachment routes
    Route::middleware(['db.safe', 'fingerprint'])->prefix('/uploads')->name('uploads.')->group(function () {
        Route::prefix('/attachments')->name('attachments.')->group(function () {
            Route::get('/{uuid}', [AssetController::class, 'show'])->name('show');
            Route::get('/{uuid}/miniature', [AssetController::class, 'miniature'])->name('miniature');
            Route::middleware(['auth'])->group(function () {
                Route::get('/', [AssetController::class, 'recent'])->name('index');
                Route::post('/', [AssetController::class, 'store'])->name('store');
            });
        });
    });
});
