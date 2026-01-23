<?php

namespace App\Factories;

use App\Models\Hr\AuthCode;
use App\Models\Hr\BrowserAgent;
use App\Models\Hr\Session;
use App\Models\Hr\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SessionFactory
{
    public static function create(User $user, Request $request, BrowserAgent $browserAgent, ?AuthCode $authCode): Session
    {
        $attributes = [
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser_agent_id' => $browserAgent->id,
            'last_activity' => Carbon::now()->timestamp,
        ];
        if ($authCode) {
            $attributes['auth_code_id'] = $authCode->id;
            $attributes['auth_type'] = $authCode->auth_type;
        }

        return Session::create($attributes);
    }
}
