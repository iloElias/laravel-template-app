<?php

namespace App\Models\Pdf;

use App\Models\Hr\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MarkdownExport.
 *
 * @property int         $id
 * @property string      $uuid
 * @property string|null $project_id
 * @property string      $content_hash
 * @property string      $filename
 * @property string      $storage_path
 * @property int         $size
 * @property int         $version
 * @property string      $status
 * @property string|null $error_message
 * @property int|null    $created_by
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 */
class MarkdownExport extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $table = 'pdf.markdown_export';

    protected $fillable = [
        'uuid',
        'project_id',
        'content_hash',
        'filename',
        'storage_path',
        'size',
        'version',
        'status',
        'error_message',
        'created_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'version' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Retorna o próximo número de versão para o project_id informado.
     */
    public static function nextVersion(?string $projectId): int
    {
        if (!$projectId) {
            return 1;
        }

        $max = self::where('project_id', $projectId)->max('version');

        return $max ? $max + 1 : 1;
    }
}
