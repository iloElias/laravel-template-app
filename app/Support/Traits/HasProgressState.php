<?php

namespace App\Support\Traits;

trait HasProgressState
{
    public const STATE_PENDING = 'pending';
    public const STATE_WAITING_FOR_OFFER = 'waiting_for_offer';
    public const STATE_PAYMENT_PENDING = 'payment_pending';
    public const STATE_APPROVED = 'approved';
    public const STATE_REJECTED = 'rejected';
    public const STATE_IN_PROGRESS = 'in_progress';
    public const STATE_CANCELED = 'canceled';
    public const STATE_COMPLETED = 'completed';
}
