<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model representing a user's Mercado Pago account details stored in the hr.user_mercado_pago table.
 *
 * #file:database/migrations/2025_10_29_083712_create_user_mercado_pago_table.php
 *
 * Columns:
 *
 * @property int         $id            Primary key.
 * @property int         $user_id       FK to hr.user (unique, cascade on delete).
 * @property null|string $mp_user_id    Mercado Pago user identifier (nullable).
 * @property null|string $public_key    Public key provided by Mercado Pago (nullable).
 * @property null|string $access_token  OAuth access token (nullable).
 * @property null|string $refresh_token OAuth refresh token (nullable).
 * @property null|string $token_type    Token type (nullable).
 * @property null|string $scope         Granted scopes (nullable).
 * @property bool        $live_mode     Whether account is in live mode (default: false).
 * @property null|int    $expires_in    Token lifetime in seconds (nullable).
 * @property null|Carbon $created_at    Record creation timestamp.
 * @property null|Carbon $updated_at    Record update timestamp.
 * @property null|Carbon $deleted_at    Soft delete timestamp.
 *
 * Relations:
 * @property User $user User owning this Mercado Pago account.
 *
 * Notes:
 * - The user_id column is unique to ensure one Mercado Pago record per user.
 * - Tokens and sensitive fields are stored as text/strings per migration and may be nullable.
 */
class UserMercadoPago extends Model
{
    use SoftDeletes;

    protected $table = 'hr.user_mercado_pago';

    protected $fillable = [
        'user_id',
        'mp_user_id',
        'public_key',
        'mp_access_token',
        'mp_refresh_token',
        'mp_token_expires_at',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConnected(): bool
    {
        return true;
        // return $this->status === 'connected' && !empty($this->mp_access_token);
    }
}
