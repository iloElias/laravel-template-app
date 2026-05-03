<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $session_id
 * @property string      $route
 * @property string      $method
 * @property null|string $payload
 * @property Carbon      $created_at
 */
class RequestHistory extends Model
{
    // Migration only has created_at, no updated_at or deleted_at
    public $timestamps = false;

    protected $table = 'hr.request_history';

    protected $fillable = [
        'session_id',
        'route',
        'method',
        'payload',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }
}
