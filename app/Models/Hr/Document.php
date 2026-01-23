<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class User.
 *
 * Represents a system user with associated attributes and logic.
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $user_id
 * @property string      $document_type
 * @property Carbon      $emission_date
 * @property string      $number
 * @property bool        $active
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $inactivated_at
 */
class Document extends Model
{
    protected $table = 'hr.document';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'uuid',
        'user_id',
        'emission_date',
        'document_type',
        'number',
        'active',
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
     * Get the user that owns the document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the document type associated with the document.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type', 'id');
    }

    /**
     * Get the document type associated with the document.
     */
    public function documentTypeKey(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type', 'key');
    }
}
