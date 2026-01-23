<?php

namespace App\Http\Middleware;

use App\Models\Hr\BrowserAgent;

class BrowserFingerprint
{
    public function handle($request, \Closure $next)
    {
        $browserAgent = $request->header('Browser-Agent');

        if (!$browserAgent) {
            return response()->json(['code' => 'browser_agent'], 401);
        }

        $storedBrowserAgent = BrowserAgent::where('fingerprint', $browserAgent)->first();

        if (!$storedBrowserAgent) {
            return response()->json(['code' => 'browser_agent'], 401);
        }

        return $next($request);
    }
}
