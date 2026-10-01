<?php

namespace App\Models;

use App\Enums\PaymentMethodType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasUuids;

    protected $fillable = [
        'customer_id',
        'stripe_id',
        'type',
        'brand',
        'last4',
        'exp_month',
        'exp_year',
        'is_default',
    ];

    protected $casts = [
        'type' => PaymentMethodType::class,
        'is_default' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isCard(): bool
    {
        return $this->type === PaymentMethodType::Card;
    }

    public function isExpired(): bool
    {
        if (empty($this->exp_month) || empty($this->exp_year)) {
            return false;
        }

        $expiration = now()->setDate((int) $this->exp_year, (int) $this->exp_month, 1)->endOfMonth();

        return now()->startOfMonth()->greaterThan($expiration);
    }

    /**
     * Make this the customer's default payment method and demote the rest.
     */
    public function makeDefault(): void
    {
        if (! $this->customer) {
            return;
        }
        $this->customer->paymentMethods()->where('id', '!=', $this->id)->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }
}
