<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/email')->group(function () {
    Route::get('/{screen?}/{locale?}', function ($screen = 'authentication', $locale = null) {
        // App::setLocale($locale ?? 'pt_BR');
        if ($screen) {
            if (view()->exists("emails.{$screen}")) {
                return view("emails.{$screen}", [
                    'user' => [
                        'name' => 'John Doe',
                    ],
                    'info' => [
                        'code' => '123456',
                        'expires' => '30',
                    ],
                ]);
            }
            abort(404);
        }
    });

    // Route::get('/', function () {
    //     return redirect('/pt_BR');
    // });
});
