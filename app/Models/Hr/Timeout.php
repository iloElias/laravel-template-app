<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int         $id
 * @property int         $user_id
 * @property null|string $reason
 * @property Carbon      $timeout_start
 * @property null|Carbon $timeout_end
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $deleted_at
 */
class Timeout extends Model
{
    use SoftDeletes;

    protected $table = 'hr.timeout';

    protected $fillable = [
        'user_id',
        'reason',
        'timeout_start',
        'timeout_end',
    ];

    protected $casts = [
        'timeout_start' => 'datetime',
        'timeout_end' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
