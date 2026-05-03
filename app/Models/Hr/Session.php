<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Session.
 *
 * Represents a user session with associated information about device, authentication status and activity.
 *
 * @property int                        $id
 * @property int                        $user_id
 * @property null|string                $ip_address
 * @property int                        $device_agent_id
 * @property null|int                   $auth_code_id
 * @property bool                       $authenticated
 * @property null|string                $payload
 * @property null|Carbon                $last_activity
 * @property Carbon                     $created_at
 * @property Carbon                     $updated_at
 * @property null|Carbon                $deleted_at
 * @property User                       $user
 * @property DeviceAgent                $deviceAgent
 * @property null|AuthCode              $authCode
 * @property Collection|RequestHistory[] $requestHistory
 */
class Session extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'hr.session';

    protected $fillable = [
        'user_id',
        'ip_address',
        'device_agent_id',
        'auth_code_id',
        'authenticated',
        'last_activity',
        'payload',
    ];

    protected $casts = [
        'authenticated' => 'boolean',
        'last_activity' => 'datetime',
    ];

    public function storageGet(string $key): mixed
    {
        $payload = $this->payload ? json_decode($this->payload, true) : [];

        return $payload[$key] ?? null;
    }

    public function storageSet(array|string $key, mixed $value = null): void
    {
        $payload = $this->payload ? json_decode($this->payload, true) : [];

        if (is_array($key)) {
            $payload = array_merge($payload, $key);
        } else {
            $payload[$key] = $value;
        }

        $this->payload = json_encode($payload);
        $this->save();
    }

    public function storageUnset(string $key): void
    {
        $payload = $this->payload ? json_decode($this->payload, true) : [];

        if (array_key_exists($key, $payload)) {
            unset($payload[$key]);
            $this->payload = json_encode($payload);
            $this->save();
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deviceAgent(): BelongsTo
    {
        return $this->belongsTo(DeviceAgent::class, 'device_agent_id');
    }

    public function authCode(): BelongsTo
    {
        return $this->belongsTo(AuthCode::class, 'auth_code_id');
    }

    public function requestHistory(): HasMany
    {
        return $this->hasMany(RequestHistory::class, 'session_id');
    }
}

