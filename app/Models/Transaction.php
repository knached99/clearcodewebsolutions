<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'invoice_id',
        'customer_id',
        'stripe_payment_intent_id',
        'amount',
        'status',
        'payment_method_type',
        'error_message',
    ];

    protected $casts = [
        'status' => TransactionStatus::class,
        'amount' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function succeeded(): bool
    {
        return $this->status === TransactionStatus::Succeeded;
    }

    public function amountInDollars(): float
    {
        return $this->amount / 100;
    }
}
