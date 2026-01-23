<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DocumentType.
 *
 * @property int         $id
 * @property string      $uuid
 * @property string      $key
 * @property string      $label
 * @property null|string $mask
 * @property bool        $active
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $inactivated_at
 */
class DocumentType extends Model
{
    protected $table = 'hr.document_type';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'uuid',
        'key',
        'label',
        'mask',
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

    /**
     * Get the documents associated with this document type.
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'document_type', 'id');
    }
}
