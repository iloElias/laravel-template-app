<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DeviceAgent.
 *
 * Represents a device agent with associated attributes and relationships.
 *
 * @property int         $id
 * @property string      $fingerprint
 * @property string      $user_agent
 * @property string      $ip_address
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $deleted_at
 */
class DeviceAgent extends Model
{
    use SoftDeletes;

    protected $table = 'hr.device_agent';

    protected $fillable = [
        'fingerprint',
        'user_agent',
        'ip_address',
    ];

    public static function validateFingerprint(string $fingerprint): ?self
    {
        return self::where('fingerprint', $fingerprint)->first();
    }

    /**
     * Get the sessions associated with this device agent.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Get the remembered devices associated with this device agent.
     */
    public function rememberedDevices(): HasMany
    {
        return $this->hasMany(RememberDevice::class);
    }
}
