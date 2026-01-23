<?php

namespace App\Models\Chat;

use App\Models\HR\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Message.
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $user_id
 * @property string      $chat_id
 * @property string      $message
 * @property null|int    $answer_to
 * @property bool        $active
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $inactivated_at
 */
class Message extends Model
{
    use HasFactory;

    public $incrementing = true;

    public $timestamps = true;

    protected $table = 'chat.message';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'uuid',
        'user_id',
        'chat_id',
        'message',
        'answer_to',
        'active',
        'inactivated_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id', 'id');
    }

    public function answer_to()
    {
        return $this->belongsTo(self::class, 'answer_to', 'id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'answer_to', 'id');
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
