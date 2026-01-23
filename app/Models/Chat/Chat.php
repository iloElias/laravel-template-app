<?php

namespace App\Models\Chat;

use App\Models\Hr\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Chat.
 *
 * @property int         $id
 * @property string      $uuid
 * @property null|string $name
 * @property null|string $picture
 * @property bool        $active
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $inactivated_at
 */
class Chat extends Model
{
    use HasFactory;

    public $incrementing = true;

    public $timestamps = true;

    protected $table = 'chat.chat';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    protected $fillable = [
        'uuid',
        'name',
        'picture',
        'active',
        'inactivated_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat.chat_user', 'chat_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'chat_id', 'id');
    }

    public function last_message()
    {
        return $this->hasOne(Message::class, 'chat_id', 'id')->latestOfMany();
    }
}
