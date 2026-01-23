<?php

namespace App\Models;

use App\Models\File\File;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class File.
 *
 * @property int    $id
 * @property bool   $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $inactivated_at
 */
class FileAttachment extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'file_id',
        'active',
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id', 'id');
    }
}
