<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class ErrorLog.
 *
 * Represents an error log with associated attributes and logic.
 *
 * @property int         $id
 * @property null|string $url           The URL where the error occurred.
 * @property string      $error_message The error message.
 * @property null|string $stack_trace   The stack trace of the error.
 * @property null|array  $request_data  The JSON-decoded request data.
 * @property Carbon      $created_at    Timestamp when the log was created.
 * @property Carbon      $updated_at    Timestamp when the log was last updated.
 */
class ErrorLog extends Model
{
    use SoftDeletes;

    protected $table = 'system.error_log';

    protected $fillable = [
        'url',
        'error_message',
        'stack_trace',
        'request_data',
    ];

    protected $casts = [
        'request_data' => 'array',
    ];
}
