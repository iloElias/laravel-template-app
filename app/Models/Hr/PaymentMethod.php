<?php

namespace App\Models\Hr;

use App\Models\DynamicQuery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Class PaymentMethod.
 *
 * Represents a payment method with associated attributes and logic.
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $user_id
 * @property int         $payment_method_type
 * @property null|string $provider
 * @property string      $number
 * @property null|string $holder_name
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property null|Carbon $inactivated_at
 */
class PaymentMethod extends DynamicQuery
{
    use HasFactory;

    protected $table = 'hr.payment_method';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'uuid',
        'user_id',
        'payment_method_type',
        'provider',
        'number',
        'holder_name',
        'inactivated_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    /**
     * Get the user that owns the payment method.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the payment method type associated with the payment method.
     */
    public function paymentMethodType(): HasOne
    {
        return $this->hasOne(PaymentMethodType::class, 'payment_method_type', 'id');
    }
}
