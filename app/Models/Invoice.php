<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Enums\TransactionStatus;
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
        'signature_data',
        'signature_signed_at',
        'agreement_version',
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
        'signature_signed_at' => 'datetime',
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

    public function successfulTransaction(): ?\App\Models\Transaction
    {
        return $this->transactions()
            ->where(
                'status',
                \App\Enums\TransactionStatus::Succeeded
            )
            ->orderByDesc('created_at')
            ->first();
    }

    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::Paid;
    }

    public function stripeProcessingFeeInCents(): int
    {
        $fee = (int) (
            $this->successfulTransaction()?->processing_fee
            ?? 0
        );

        if ($fee === 0 && $this->total_amount > 0) {
            // Estimate standard Stripe fee: 2.9% + 30 cents, represented as negative.
            $fee = -(int) round(
                ($this->total_amount * 0.029) + 30
            );
        }

        return $fee;
    }

    public function netAmountInCents(): int
    {
        return max(
            (int) $this->total_amount
            + (int) $this->stripeProcessingFeeInCents(),
            0
        );
    }

    public function pabloCommissionInCents(): int
    {
        $net = $this->netAmountInCents();
        $rate = $net < 200000 ? 0.10 : 0.20;

        return (int) round(
            $net * $rate
        );
    }

    public function teamShareAfterPabloInCents(): int
    {
        return max(
            $this->netAmountInCents()
            - $this->pabloCommissionInCents(),
            0
        );
    }

    public function ownerShareInCents(): int
    {
        return (int) round(
            $this->teamShareAfterPabloInCents() / 2
        );
    }

    public function partnerShareInCents(): int
    {
        return max(
            $this->teamShareAfterPabloInCents()
            - $this->ownerShareInCents(),
            0
        );
    }

    public function recurringDurationMonths(): int
    {
        return max(
            1,
            (int) (
                $this->subscription_months
                ?? (
                    $this->hasRecurringBilling()
                        ? 12
                        : 1
                )
            )
        );
    }

    public function recurringChargeStartedAt(): \Illuminate\Support\Carbon
    {
        return \Illuminate\Support\Carbon::parse(
            $this->created_at ?? now()
        );
    }

    public function recurringChargeNextDueAt(): \Illuminate\Support\Carbon
    {
        return $this->recurringChargeStartedAt()
            ->addMonth();
    }

    public function recurringChargeExpiresAt(): \Illuminate\Support\Carbon
    {
        return $this->recurringChargeStartedAt()
            ->addMonths(
                $this->recurringDurationMonths()
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Recurring Base
    |--------------------------------------------------------------------------
    |
    | ONLY the base item's is_subscription flag determines whether
    | the base amount itself is recurring.
    |
    | The base item does not need a stripe_price_id because the base
    | amount is a custom amount.
    |
    */

    public function hasRecurringBaseAmount(): bool
    {
        return $this->items()
            ->where('is_subscription', true)
            ->where('is_addon', false)
            ->exists();
    }

    public function hasCompletedTwelveMonthMinimum(): bool
    {
        return $this->payment_type === PaymentType::Subscription
            && (int) ($this->subscription_months ?? 0) >= 12;
    }

    /*
    |--------------------------------------------------------------------------
    | Recurring Addons
    |--------------------------------------------------------------------------
    |
    | Addons are ALWAYS recurring and use a Stripe recurring Price ID.
    |
    */

    public function hasRecurringAddons(): bool
    {
        return $this->items()
            ->where('is_subscription', true)
            ->where('is_addon', true)
            ->whereNotNull('stripe_price_id')
            ->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Recurring Billing
    |--------------------------------------------------------------------------
    |
    | This means the invoice has something that recurs.
    | It does NOT mean the invoice itself is subscription revenue.
    |
    */

    public function hasRecurringBilling(): bool
    {
        return $this->hasRecurringBaseAmount()
            || $this->hasRecurringAddons();
    }

    public function requiresSignature(): bool
    {
        return $this->payment_type === PaymentType::Subscription
            || $this->hasRecurringAddons();
    }

    public function hasSignedAgreement(): bool
    {
        return !empty($this->signature_data)
            && !empty($this->signature_signed_at);
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

    public function estimatedMonthlyStripeFeeInCents(): int
    {
        $amount = $this->monthlyRecurringAmountInCents();

        if ($amount === 0) {
            return 0;
        }

        // Standard Stripe fee: 2.9% + 30 cents. Represented as negative.
        $fee = ($amount * 0.029) + 30;

        return -(int) round($fee);
    }

    public function monthlyNetAmountInCents(): int
    {
        return max(
            $this->monthlyRecurringAmountInCents()
            + $this->estimatedMonthlyStripeFeeInCents(),
            0
        );
    }

    public function monthlyPabloCommissionInCents(): int
    {
        $gross = $this->monthlyRecurringAmountInCents();
        $rate = $gross < 200000 ? 0.10 : 0.20;

        return (int) round(
            $gross * $rate
        );
    }

    public function monthlyTeamShareAfterPabloInCents(): int
    {
        return max(
            $this->monthlyNetAmountInCents()
            - $this->monthlyPabloCommissionInCents(),
            0
        );
    }

    public function monthlyOwnerShareInCents(): int
    {
        return (int) round(
            $this->monthlyTeamShareAfterPabloInCents() / 2
        );
    }

    public function monthlyPartnerShareInCents(): int
    {
        return max(
            $this->monthlyTeamShareAfterPabloInCents()
            - $this->monthlyOwnerShareInCents(),
            0
        );
    }

    public function nextBillingDate(): \Illuminate\Support\Carbon
    {
        return \Illuminate\Support\Carbon::parse(
            $this->created_at ?? now()
        )->addMonth();
    }

    public function renewalDate(): \Illuminate\Support\Carbon
    {
        $months = max(
            1,
            (int) ($this->subscription_months ?? 12)
        );

        return \Illuminate\Support\Carbon::parse(
            $this->created_at ?? now()
        )->addMonths($months);
    }

    /**
     * Get the estimated total value over the 12-month minimum term.
     *
     * One-time items are counted once.
     * Recurring items are counted for 12 months.
     */
    public function estimated12MonthValueInCents(): int
    {
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
        return route(
            'checkout.show',
            $this->checkout_token
        );
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