<?php

namespace App\Models\Hr;

use App\Models\Chat\Chat;
use App\Models\Chat\ChatUser;
use App\Models\DynamicQuery;
use App\Models\LastError;
use App\Support\Traits\HasAuthUser;
use Carbon\Carbon;
use Firebase\JWT\Key;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

/**
 * Class User.
 *
 * Represents a system user with associated attributes and logic.
 *
 * @property int         $id
 * @property string      $uuid
 * @property string      $name
 * @property string      $surname
 * @property string      $email
 * @property string      $number
 * @property string      $password
 * @property string      $language
 * @property null|string $profile_type
 * @property bool        $email_two_factor_auth
 * @property bool        $email_verified
 * @property null|Carbon $email_verified_at
 * @property bool        $number_two_factor_auth
 * @property bool        $number_verified
 * @property null|Carbon $number_verified_at
 * @property null|string $pix_key
 * @property bool        $active
 * @property null|string $profile_picture
 * @property null|string $remember_token
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $inactivated_at
 */
class User extends DynamicQuery
{
    use HasFactory;
    use Notifiable;
    use LastError;
    use HasAuthUser;

    protected $table = 'hr.user';

    protected $primaryKey = 'id';

    protected $fillable = [
        'uuid',
        'name',
        'surname',
        'number',
        'email',
        'password',
        'profile_type',
        'language',
        'email_two_factor_auth',
        'email_verified',
        'email_verified_at',
        'number_two_factor_auth',
        'number_verified',
        'number_verified_at',
        'updated_at',
        'inactivated_at',
        'pix_key',
        'active',
        'profile_picture',
        'remember_token',
    ];

    protected $casts = [
        'email_two_factor_auth' => 'boolean',
        'email_authenticated' => 'boolean',
        'number_two_factor_auth' => 'boolean',
        'number_authenticated' => 'boolean',
        'active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'inactivated_at',
        'email_verified_at',
        'number_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'user_id', 'id');
    }

    public function payment_methods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'user_id', 'id');
    }

    public function chats(): BelongsToMany
    {
        $chatTable = (new Chat())->getTable();
        $chatUserTable = (new ChatUser())->getTable();

        return $this->belongsToMany(Chat::class, ChatUser::class, 'user_id', 'chat_id')
            ->with(['users', 'last_message'])
            ->where($chatTable . '.active', 1)
            ->groupBy([
                "{$chatTable}.id",
                "{$chatUserTable}.user_id",
                "{$chatUserTable}.chat_id",
            ])
            ->orderByDesc(
                Chat::selectRaw('MAX(created_at)')
                    ->from('chat.message')
                    ->whereColumn('chat.message.chat_id', "{$chatTable}.id")
            )
        ;
    }

    public function cashOuts(): HasMany
    {
        return $this->hasMany(CashOut::class, 'user_id', 'id');
    }

    public function user_mercado_pago()
    {
        return $this->hasOne(UserMercadoPago::class, 'user_id', 'id');
    }
}
