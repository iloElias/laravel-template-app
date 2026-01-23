<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\BrowserAgentController;
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
    Route::get('/', [IndexController::class, 'index']);
    Route::fallback([IndexController::class, 'fallback']);

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

    Route::middleware([])->prefix('/webhook')->group(function () {
        Route::post('/mercado-pago', [MercadoPagoController::class, 'webhook']);
    });
    Route::middleware([])->prefix('/integrations')->group(function () {
        Route::prefix('/mercado-pago')->group(function () {
            Route::middleware(['auth'])->group(function () {
                Route::get('/connect', [MercadoPagoAuthController::class, 'connect']);
                Route::get('/callback', [MercadoPagoAuthController::class, 'callback']);
            });
        });
    });

    Route::middleware([])->prefix('/auth')->group(function () {
        Route::prefix('fingerprint')->group(function () {
            Route::get('/', [BrowserAgentController::class, 'makeFingerprint']);
            Route::middleware('fingerprint')->get('/validate', [BrowserAgentController::class, 'validate']);
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
                Route::put('/profile-type', [UserController::class, 'profileType']);
                Route::prefix('/document')->group(function () {
                    Route::get('/', [DocumentController::class, 'index']);
                    Route::get('/{uuid}', [DocumentController::class, 'show']);
                    Route::post('/', [DocumentController::class, 'store']);
                    Route::put('/{uuid}', [DocumentController::class, 'update']);
                    Route::delete('/{uuid}', [DocumentController::class, 'delete']);
                });

                Route::prefix('/picture')->group(function () {
                    Route::post('/upload', [UserController::class, 'postPicture']);
                });
            });
            Route::get('/exists', [UserController::class, 'exists']);
        });
        Route::middleware(['auth'])->group(function () {
            Route::prefix('/support')->group(function () {
                Route::post('/request', [SupportController::class, 'store']);
            });

            // Chat routes
            Route::prefix('/chat')->group(function () {
                Route::get('/', [ChatController::class, 'index']);
                Route::post('/with', [ChatController::class, 'with']);
                Route::get('/{uuid}', [ChatController::class, 'show']);
                Route::prefix('/message')->group(function () {
                    Route::post('/', [MessageController::class, 'store']);
                    Route::delete('/{uuid}', [MessageController::class, 'destroy']);
                });
            });

            // Machinery routes
            Route::prefix('/machinery')->group(function () {
                Route::get('/', [MachineryController::class, 'index']);
                Route::get('/{uuid}', [MachineryController::class, 'show']);
                Route::post('/', [MachineryController::class, 'store']);
                Route::put('/{uuid}', [MachineryController::class, 'update']);
                Route::delete('/{uuid}', [MachineryController::class, 'disable']);
            });

            // Transport vehicle routes
            Route::prefix('/carrier')->group(function () {
                Route::get('/', [CarrierController::class, 'index']);
                Route::get('/{uuid}', [CarrierController::class, 'show']);
                Route::post('/', [CarrierController::class, 'store']);
                Route::put('/{uuid}', [CarrierController::class, 'update']);
                Route::delete('/{uuid}', [CarrierController::class, 'disable']);
            });

            // Request routes
            Route::prefix('/request')->group(function () {
                Route::get('/', [RequestController::class, 'index']);
                Route::post('/', [RequestController::class, 'store']);
                Route::get('/available', [RequestController::class, 'listRequestsForOffer']);
                Route::put('/rate', [RequestController::class, 'rate']);
                Route::prefix('/{uuid}')->group(function () {
                    Route::get('/offers', [RequestController::class, 'offers']);
                    Route::get('/update', [RequestController::class, 'updatePaymentStatus']);
                    Route::put('/complete', [RequestController::class, 'complete']);
                    Route::get('/', [RequestController::class, 'show']);
                    Route::delete('/', [RequestController::class, 'destroy']);
                });
                // Route::put('/', [RequestController::class, 'update']);
            });

            // Offer routes
            Route::prefix('/offer')->group(function () {
                Route::get('/', [OfferController::class, 'index']);
                Route::post('/', [OfferController::class, 'store']);
                Route::put('/rate', [OfferController::class, 'rate']);
                Route::prefix('/{uuid}')->group(function () {
                    Route::put('/accept', [OfferController::class, 'accept']);
                    Route::put('/start', [OfferController::class, 'start']);
                    Route::put('/complete', [OfferController::class, 'complete']);
                    Route::get('/', [OfferController::class, 'show']);
                    Route::put('/', [OfferController::class, 'update']);
                    Route::delete('/', [OfferController::class, 'delete']);
                });
            });

            Route::prefix('/cash-out')->group(function () {
                Route::get('/', [CashOutController::class, 'index']);
                Route::get('/funds', [CashOutController::class, 'funds']);
                Route::post('/', [CashOutController::class, 'store']);
            });
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