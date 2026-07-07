<?php

namespace App\Services\Access;

use App\Mail\AccessRouteAccessedMail;
use App\Models\System\AccessRequestLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class AccessAlertService
{
    private const CACHE_KEY = 'access_route:last_mail_sent_at';

    public function maybeSend(AccessRequestLog $accessLog): bool
    {
        $canSend = Cache::add(self::CACHE_KEY, now()->toIso8601String(), now()->addHour());

        if (!$canSend) {
            return false;
        }

        Mail::to('iloelias.dev@gmail.com')->send(new AccessRouteAccessedMail([
            'access' => $accessLog->toArray(),
            'geo' => $accessLog->geo ?? [],
        ]));

        return true;
    }
}
