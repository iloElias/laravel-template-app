<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
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
    /**
     * Enable timestamps (created_at and updated_at) as defined in the migration.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Define the schema-qualified table name.
     *
     * @var string
     */
    protected $table = 'system.error_log';

    /**
     * Fields that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'url',
        'error_message',
        'stack_trace',
        'request_data',
    ];

    /**
     * Cast the request_data column to an array.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'request_data' => 'array',
    ];
}
