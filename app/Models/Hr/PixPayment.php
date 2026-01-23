<?php

namespace App\Models\Hr;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PixPayment.
 *
 * Represents a record of a PIX payment with detailed information such as payment status, transaction amount,
 * relevant timestamps (creation, approval, last update, expiration), and QR code data.
 *
 * @property string      $id                 Unique identifier for the PIX payment record.
 * @property string      $uuid               Unique identifier (UUID) for the PIX payment.
 * @property string      $payment_id         Identifier for the payment provided by the payment gateway.
 * @property string      $status             Current status of the PIX payment.
 * @property string      $status_detail      Detailed description of the payment status.
 * @property float       $transaction_amount The amount involved in the transaction.
 * @property string      $external_reference External reference associated with the payment.
 * @property null|Carbon $date_created       Date and time when the payment was created.
 * @property null|Carbon $date_approved      Date and time when the payment was approved.
 * @property null|Carbon $date_last_updated  Date and time when the payment was last updated.
 * @property null|Carbon $date_of_expiration Date and time when the payment expires.
 * @property string      $qr_code            QR code string that can be used to process the payment.
 * @property string      $qr_code_base64     Base64 encoded image of the QR code.
 * @property string      $ticket_url         URL for the payment ticket.
 * @property bool        $active             Indicates if the PIX payment record is active.
 * @property null|Carbon $inactivated_at     Date and time when the payment record was deactivated, if applicable.
 * @property Carbon      $created_at         Timestamp when the payment record was created.
 * @property Carbon      $updated_at         Timestamp when the payment record was last updated.
 */
class PixPayment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hr.pix_payment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'payment_id',
        'status',
        'status_detail',
        'transaction_amount',
        'external_reference',
        'date_created',
        'date_approved',
        'date_last_updated',
        'date_of_expiration',
        'qr_code',
        'qr_code_base64',
        'ticket_url',
        'active',
        'inactivated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'transaction_amount' => 'float',
        'active' => 'boolean',
        'date_created' => 'datetime',
        'date_approved' => 'datetime',
        'date_last_updated' => 'datetime',
        'date_of_expiration' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'inactivated_at' => 'datetime',
    ];
}
