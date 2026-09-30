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

        /*
        |--------------------------------------------------------------------------
        | Stripe identifiers
        |--------------------------------------------------------------------------
        */
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'stripe_balance_transaction_id',
        'stripe_payment_method_id',

        /*
        |--------------------------------------------------------------------------
        | Amounts
        |--------------------------------------------------------------------------
        */
        'amount',
        'processing_fee',
        'net_amount',

        /*
        |--------------------------------------------------------------------------
        | Transaction state
        |--------------------------------------------------------------------------
        */
        'status',
        'paid_at',

        /*
        |--------------------------------------------------------------------------
        | Payment method information
        |--------------------------------------------------------------------------
        */
        'payment_method_type',
        'brand',
        'last4',

        /*
        |--------------------------------------------------------------------------
        | Failure information
        |--------------------------------------------------------------------------
        */
        'error_message',
    ];

    protected $casts = [
        'status' => TransactionStatus::class,
        'amount' => 'integer',
        'processing_fee' => 'integer',
        'net_amount' => 'integer',
        'paid_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public function succeeded(): bool
    {
        return $this->status === TransactionStatus::Succeeded;
    }

    /*
    |--------------------------------------------------------------------------
    | Amount Helpers
    |--------------------------------------------------------------------------
    */

    public function amountInDollars(): float
    {
        return $this->amount / 100;
    }

    public function processingFeeInDollars(): float
    {
        return $this->processing_fee / 100;
    }

    public function netAmountInDollars(): float
    {
        return $this->net_amount / 100;
    }
}