<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class PaymentMethodType.
 *
 * Represents a payment method type for a user.
 *
 * @property int         $id             Unique identifier.
 * @property string      $uuid           Universally unique identifier.
 * @property string      $key            Unique key representing the payment method type.
 * @property string      $label          Human-readable label for the payment method type.
 * @property null|string $mask           Optional mask for payment method details.
 * @property bool        $active         Indicates whether the payment method type is active.
 * @property Carbon      $created_at     Record creation timestamp.
 * @property Carbon      $updated_at     Record last update timestamp.
 * @property null|Carbon $inactivated_at Record inactivation timestamp, if applicable.
 */
class PaymentMethodType extends Model
{
    use HasFactory;

    protected $table = 'hr.payment_method_type';

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'inactivated_at' => 'datetime',
    ];

    /**
     * Get the payment methods associated with this type.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class, 'payment_method_type', 'id');
    }
}
