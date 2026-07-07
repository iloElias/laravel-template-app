<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class AccessRequestLog extends Model
{
    protected $table = 'system.access_request_log';

    protected $fillable = [
        'request_id',
        'source_id',
        'ip',
        'forwarded_ip',
        'cf_connecting_ip',
        'method',
        'full_url',
        'path',
        'host',
        'scheme',
        'port',
        'referer',
        'origin',
        'user_agent',
        'accept_language',
        'route_name',
        'route_action',
        'query_params',
        'payload',
        'headers',
        'cookies',
        'geo',
    ];

    protected $casts = [
        'query_params' => 'array',
        'payload' => 'array',
        'headers' => 'array',
        'cookies' => 'array',
        'geo' => 'array',
    ];
}
