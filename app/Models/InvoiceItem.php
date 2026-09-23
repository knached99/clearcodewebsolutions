<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'invoice_id',
        'description',
        'amount',
        'stripe_price_id',
        'is_subscription',
    ];

    protected $casts = [
        'amount' => 'integer',
        'is_subscription' => 'boolean',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function amountInDollars(): float
    {
        return $this->amount / 100;
    }
}
