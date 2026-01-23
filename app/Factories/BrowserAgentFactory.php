<?php

namespace App\Factories;

use App\Models\Hr\BrowserAgent;
use App\Models\Tracker;
use Illuminate\Support\Str;

class BrowserAgentFactory
{
    public static function create(): BrowserAgent
    {
        return BrowserAgent::create([
            'user_agent' => request()->header('User-Agent'),
            'fingerprint' => Str::uuid(),
            'ip_address' => Tracker::ip(),
            'active' => true,
        ]);
    }
}
