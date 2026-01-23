<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IndexController extends Controller
{
    /**
     * Handle the API index request.
     */
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'Bem vindo aos serviços de ' . env('APP_COMERCIAL_NAME') . ''], 200);
    }

    public function fallback()
    {
        return response()->json(['message' => 'Endpoint not found'], 404);
    }

    /**
     * Serve the favicon.ico file.
     *
     * @return BinaryFileResponse
     */
    public function favicon()
    {
        $path = public_path('img/favicon.ico');

        if (!file_exists($path)) {
            abort(404, 'Favicon not found.');
        }

        return Response::file($path, [
            'Content-Type' => 'image/x-icon',
        ]);
    }
}
