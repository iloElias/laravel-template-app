<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int         $id
 * @property int         $device_agent_id
 * @property string      $reason
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $deleted_at
 */
class BannedDeviceAgent extends Model
{
    use SoftDeletes;

    protected $table = 'hr.banned_device_agent';

    protected $fillable = [
        'device_agent_id',
        'reason',
    ];

    public function deviceAgent(): BelongsTo
    {
        return $this->belongsTo(DeviceAgent::class);
    }
}
