<?php

namespace App\Factories;

class DeviceAgentFactory
{
    public static function create(): ?\App\Models\Hr\DeviceAgent
    {
        $fingerprint = bin2hex(random_bytes(16));
        return \App\Models\Hr\DeviceAgent::create(['fingerprint' => $fingerprint]);
    }
}
