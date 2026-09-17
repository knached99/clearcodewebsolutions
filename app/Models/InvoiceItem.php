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
        'is_subscription',
    ];

    protected $casts = [
        'amount' => 'integer',
        'is_subscription' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Keep the parent invoice's total_amount in sync automatically.
        static::saved(fn (InvoiceItem $item) => $item->invoice?->recalculateTotal());
        static::deleted(fn (InvoiceItem $item) => $item->invoice?->recalculateTotal());
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function amountInDollars(): float
    {
        return $this->amount / 100;
    }
}
