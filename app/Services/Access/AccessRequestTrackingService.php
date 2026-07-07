<?php

namespace App\Services\Access;

use App\Models\System\AccessRequestLog;
use App\Models\Tracker;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AccessRequestTrackingService
{
    public function __construct(private readonly IpGeolocationService $ipGeolocationService)
    {
    }

    public function store(Request $request): AccessRequestLog
    {
        $clientIp = Tracker::ip();
        $proxyIp = Tracker::proxyIp();
        $cfRay = Tracker::cfRay();
        $geo = $this->ipGeolocationService->locate($clientIp);

        $sourceId = hash('sha256', implode('|', [
            (string) $clientIp,
            (string) $request->userAgent(),
            (string) $request->header('accept-language'),
            (string) $request->getHost(),
        ]));

        return AccessRequestLog::query()->create([
            'request_id' => (string) Str::uuid(),
            'source_id' => $sourceId,
            'ip' => $clientIp,
            'client_ip' => $clientIp,
            'proxy_ip' => $proxyIp,
            'cf_ray' => $cfRay,
            'forwarded_ip' => $request->header('x-forwarded-for'),
            'cf_connecting_ip' => $request->header('cf-connecting-ip'),
            'method' => $request->method(),
            'full_url' => $request->fullUrl(),
            'path' => $request->path(),
            'host' => $request->getHost(),
            'scheme' => $request->getScheme(),
            'port' => $request->getPort(),
            'referer' => $request->headers->get('referer'),
            'origin' => $request->headers->get('origin'),
            'user_agent' => $request->userAgent(),
            'accept_language' => $request->header('accept-language'),
            'route_name' => optional($request->route())->getName(),
            'route_action' => optional($request->route())->getActionName(),
            'query_params' => $request->query(),
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'cookies' => $request->cookies->all(),
            'geo' => $geo,
        ]);
    }
}
