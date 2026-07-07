<?php

namespace App\Http\Controllers;

use App\Services\Access\AccessAlertService;
use App\Services\Access\AccessRequestTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    public function __construct(
        private readonly AccessRequestTrackingService $trackingService,
        private readonly AccessAlertService $alertService,
    ) {
    }

    public function handleThatAccess(Request $request): JsonResponse
    {
        $accessLog = $this->trackingService->store($request);

        $this->alertService->maybeSend($accessLog);

        return response()->json("Ok");
    }
}
