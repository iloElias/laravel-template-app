<?php

namespace App\Models;

class Tracker
{
    public static function ip()
    {
        $headers = [
            'X-Forwarded-For',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_X_REAL_IP',
        ];

        foreach ($headers as $header) {
            $ip = request()->header($header);
            if ($ip) {
                $ip = trim(explode(',', $ip)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return request()->ip();
    }
}
