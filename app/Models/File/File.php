<?php

namespace App\Models\File;

use App\Models\Hr\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class File.
 *
 * @property int         $id
 * @property string      $uuid
 * @property string      $name
 * @property string      $path
 * @property string      $mime_type
 * @property int         $size
 * @property int         $uploaded_by
 * @property bool        $attached
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $deleted_at
 */
class File extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'file.file';

    protected $fillable = [
        'uuid',
        'name',
        'path',
        'mime_type',
        'size',
        'uploaded_by',
        'attached',
    ];

    protected $casts = [
        'attached' => 'boolean',
        'size' => 'integer',
    ];

    /**
     * @param string[] $uuidList
     */
    public static function markAsAttached(array $uuidList): int
    {
        return self::where('uploaded_by', User::auth()->id)
            ->whereIn('uuid', $uuidList)
            ->update(['attached' => true]);
    }
}

