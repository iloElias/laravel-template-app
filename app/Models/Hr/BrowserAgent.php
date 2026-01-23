<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class BrowserAgent.
 *
 * Represents a browser agent with associated attributes and relationships.
 *
 * @property int         $id
 * @property string      $fingerprint
 * @property string      $user_agent
 * @property string      $ip_address
 * @property bool        $active
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $inactivated_at
 */
class BrowserAgent extends Model
{
    protected $table = 'hr.browser_agent';

    protected $fillable = [
        'user_agent',
        'fingerprint',
        'ip_address',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    public static function validateFingerprint(string $fingerprint): ?self
    {
        return self::where('fingerprint', $fingerprint)
            ->where('active', true)
            ->first()
        ;
    }

    /**
     * Get the sessions associated with this browser agent.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Get the remembered browsers associated with this browser agent.
     */
    public function rememberedBrowsers(): HasMany
    {
        return $this->hasMany(RememberBrowser::class);
    }

    /**
     * Deactivate the browser agent.
     */
    public function deactivate(): bool
    {
        $this->active = false;
        $this->inactivated_at = now();

        return $this->save();
    }
}
