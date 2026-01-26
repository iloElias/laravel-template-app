<?php

namespace App\Http\Controllers;

use App\Factories\DeviceAgentFactory;
use App\Models\Hr\DeviceAgent;
use Illuminate\Http\JsonResponse;

class DeviceAgentController extends Controller
{
    public function makeFingerprint(): JsonResponse
    {
        $fingerprint = request()->header('Device-Agent');
        if ($fingerprint) {
            $browserAgent = DeviceAgent::validateFingerprint($fingerprint);
            if ($browserAgent) {
                return response()->json('valid_fingerprint', 200);
            }
        }

        $browserAgent = DeviceAgentFactory::create();

        if ($browserAgent) {
            return response()->json($browserAgent->fingerprint, 201);
        }

        return response()->json('fingerprint_not_created', 500);
    }

    public function validate(): JsonResponse
    {
        // This is a middleware, so if it reaches this point, the fingerprint is valid
        return response()->json('valid_fingerprint', 200);
    }
}
