<?php

namespace App\Http\Middleware;

use App\Models\Hr\DeviceAgent;

class DeviceFingerprint
{
    public function handle($request, \Closure $next)
    {
        $browserAgent = $request->header('Device-Agent');

        if (!$browserAgent) {
            return response()->json(['code' => 'device_agent'], 401);
        }

        $storedBrowserAgent = DeviceAgent::where('fingerprint', $browserAgent)->first();

        if (!$storedBrowserAgent) {
            return response()->json(['code' => 'device_agent'], 401);
        }

        return $next($request);
    }
}
