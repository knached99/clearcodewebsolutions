<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'customer_id',
        'ticket_id',
        'stripe_id',
        'checkout_token',
        'total_amount',
        'status',
        'payment_type',
        'subscription_months',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'payment_type' => PaymentType::class,
        'total_amount' => 'integer',
        'subscription_months' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Invoice $invoice) {
            if (empty($invoice->checkout_token)) {
                $invoice->checkout_token = Str::random(64);
            }

            // Subscriptions are enforced server-side too, not just in the UI.
            if (
                $invoice->payment_type === PaymentType::Subscription
                && (int) $invoice->subscription_months < 12
            ) {
                $invoice->subscription_months = 12;
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function isSubscription(): bool
    {
        return $this->payment_type === PaymentType::Subscription;
    }

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::Paid;
    }

    public function hasCompletedTwelveMonthMinimum(): bool
    {
        return $this->payment_type === PaymentType::Subscription
            && (int) ($this->subscription_months ?? 0) >= 12;
    }

    public function hasRecurringAddons(): bool
    {
        return $this->items()
            ->where('is_subscription', true)
            ->whereNotNull('stripe_price_id')
            ->exists();
    }

    /**
     * Get the total invoice amount in cents.
     *
     * For subscriptions, this represents the amount due today,
     * not the total value of the 12-month commitment.
     */
    public function totalAmountInCents(): int
    {
        return (int) $this->total_amount;
    }

    /**
     * Get the total invoice amount in dollars.
     *
     * For subscriptions, this represents the amount due today,
     * not the total value of the 12-month commitment.
     */
    public function totalAmountInDollars(): float
    {
        return $this->total_amount / 100;
    }

    /**
     * Get the monthly recurring amount in cents.
     *
     * Only invoice items marked as subscription items are included.
     */
    public function monthlyRecurringAmountInCents(): int
    {
        return (int) $this->items
            ->where('is_subscription', true)
            ->sum('amount');
    }

    /**
     * Get the monthly recurring amount in dollars.
     */
    public function monthlyRecurringAmountInDollars(): float
    {
        return $this->monthlyRecurringAmountInCents() / 100;
    }

    /**
     * Get the estimated total value over the 12-month minimum term.
     *
     * Example:
     * $10,000 one-time base charge
     * + $505/month recurring
     * = $16,060 estimated 12-month value
     */
    public function estimated12MonthValueInCents(): int
    {
        if (! $this->isSubscription()) {
            return $this->totalAmountInCents();
        }

        $oneTimeAmount = (int) $this->items
            ->where('is_subscription', false)
            ->sum('amount');

        return $oneTimeAmount
            + ($this->monthlyRecurringAmountInCents() * 12);
    }

    /**
     * Get the estimated total value over the 12-month minimum term in dollars.
     */
    public function estimated12MonthValueInDollars(): float
    {
        return $this->estimated12MonthValueInCents() / 100;
    }

    /**
     * Recalculate total_amount from the sum of its line items.
     *
     * This represents the amount due today.
     * Recurring items are NOT multiplied by 12.
     */
    public function recalculateTotal(): void
    {
        $this->update([
            'total_amount' => $this->items()->sum('amount'),
        ]);
    }

    /**
     * The secure, non-Stripe-hosted checkout page for this invoice.
     */
    public function checkoutUrl(): string
    {
        return route('checkout.show', $this->checkout_token);
    }

    /**
     * Generate the PDF binary content for this invoice.
     */
    public function generatePdf(): string
    {
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $this,
        ]);

        return $pdf->output();
    }
}
