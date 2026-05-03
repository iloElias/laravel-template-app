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
 * @property Carbon      $banned_at
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $deleted_at
 */
class Banned extends Model
{
    use SoftDeletes;

    protected $table = 'hr.banned';

    protected $fillable = [
        'user_id',
        'reason',
        'banned_at',
    ];

    protected $casts = [
        'banned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
