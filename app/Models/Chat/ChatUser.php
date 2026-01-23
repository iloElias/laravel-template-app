<?php

namespace App\Models\Chat;

use App\Models\HR\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ChatUser.
 *
 * @property int         $id
 * @property string      $chat_id
 * @property int         $user_id
 * @property Carbon      $joined_in
 * @property null|Carbon $left_in
 * @property bool        $active
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $inactivated_at
 */
class ChatUser extends Model
{
    use HasFactory;

    public $incrementing = true;

    public $timestamps = true;

    protected $table = 'chat.chat_user';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'chat_id',
        'user_id',
        'joined_in',
        'left_in',
        'active',
        'inactivated_at',
    ];

    protected $dates = [
        'joined_in',
        'left_in',
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }
}
