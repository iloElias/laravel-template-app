<?php

namespace App\Models\File;

use App\Models\Hr\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * Class File.
 *
 * @property int    $id
 * @property string $uuid
 * @property string $name
 * @property string $path
 * @property string $mime_type
 * @property string $size
 * @property int    $uploaded_by
 * @property bool   $attached
 */
class File extends Model
{
    use HasFactory;
    use Notifiable;

    protected $table = 'file.file';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'uuid',
        'name',
        'path',
        'mime_type',
        'size',
        'uploaded_by',
        'attached',
    ];

    protected $casts = [
        'active' => 'boolean',
        'attached' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    /**
     * Summary of markAsAttached.
     *
     * @param string[] $uuidList
     */
    public static function markAsAttached(array $uuidList)
    {
        return self::where('uploaded_by', User::auth()->id)
            ->whereIn('uuid', $uuidList)
            ->update(['attached' => true])
        ;
    }
}
