<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Enums\TransactionStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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
        'stripe_invoice_id',
        'stripe_subscription_id',
        'checkout_token',
        'signature_data',
        'signature_signed_at',
        'agreement_version',
        'total_amount',
        'total_paid',
        'status',
        'payment_type',
        'subscription_months',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'payment_type' => PaymentType::class,
        'total_amount' => 'integer',
        'total_paid' => 'integer',
        'subscription_months' => 'integer',
        'signature_signed_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->checkout_token)) {
                $invoice->checkout_token = Str::random(64);
            }

            /*
             * Subscriptions are enforced server-side too,
             * not just in the UI.
             */
            if (
                $invoice->payment_type === PaymentType::Subscription
                && (int) $invoice->subscription_months < 12
            ) {
                $invoice->subscription_months = 12;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    /**
     * Get all successful transactions for this invoice.
     *
     * These are the transactions that count toward:
     * - actual revenue collected
     * - total_paid
     * - actual Stripe fees
     * - actual net revenue
     */
    public function successfulTransactions(): HasMany
    {
        return $this->transactions()
            ->where('status', TransactionStatus::Succeeded);
    }

    /**
     * Get the most recent successful transaction.
     *
     * This is useful when you only need the latest successful payment.
     */
    public function successfulTransaction(): ?Transaction
    {
        return $this->successfulTransactions()
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Status / Totals
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate the total amount actually paid from successful transactions.
     *
     * This is the source of truth used to rebuild total_paid.
     */
    public function totalPaidFromTransactions(): int
    {
        return (int) $this->successfulTransactions()
            ->sum('amount');
    }

    /**
     * Synchronize the cached total_paid and invoice payment status.
     *
     * total_paid is deliberately a cached aggregate of successful
     * transactions rather than an independently entered amount.
     */
    public function syncPaymentTotals(): void
    {
        $totalPaid = $this->totalPaidFromTransactions();

        /*
         * A void invoice should remain void.
         * Otherwise, payment status is derived from successful payments.
         */
        $status = $this->status === InvoiceStatus::Void
            ? InvoiceStatus::Void
            : match (true) {
                $this->total_amount > 0
                    && $totalPaid >= (int) $this->total_amount
                    => InvoiceStatus::Paid,

                $totalPaid > 0
                    => InvoiceStatus::Partial,

                default
                    => InvoiceStatus::Pending,
            };

        $this->update([
            'total_paid' => $totalPaid,
            'status' => $status,
        ]);
    }

    /**
     * Amount still owed on the invoice, in cents.
     */
    public function amountDueInCents(): int
    {
        return max(
            (int) $this->total_amount - (int) $this->total_paid,
            0
        );
    }

    /**
     * Amount still owed on the invoice, in dollars.
     */
    public function amountDueInDollars(): float
    {
        return $this->amountDueInCents() / 100;
    }

    /**
     * Whether the invoice has received some payment,
     * but is not fully paid.
     */
    public function isPartiallyPaid(): bool
    {
        return (int) $this->total_paid > 0
            && (int) $this->total_paid < (int) $this->total_amount;
    }

    /**
     * Whether the invoice has been fully paid.
     */
    public function isFullyPaid(): bool
    {
        return (int) $this->total_paid >= (int) $this->total_amount;
    }

    /**
     * Whether the invoice is paid.
     *
     * Using the amount makes this resilient if the cached status
     * has not yet been refreshed.
     */
    public function isPaid(): bool
    {
        return $this->isFullyPaid();
    }

    /*
    |--------------------------------------------------------------------------
    | Actual Stripe Fees / Net Revenue
    |--------------------------------------------------------------------------
    */

    /**
     * Get the actual Stripe processing fees from successful transactions.
     *
     * Fees are stored as positive cents.
     *
     * Example:
     *   amount = 10000
     *   processing_fee = 320
     *   net_amount = 9680
     */
    public function stripeProcessingFeeInCents(): int
    {
        return (int) $this->successfulTransactions()
            ->sum('processing_fee');
    }

    /**
     * Get actual Stripe processing fees in dollars.
     */
    public function stripeProcessingFeeInDollars(): float
    {
        return $this->stripeProcessingFeeInCents() / 100;
    }

    /**
     * Get actual collected net revenue after Stripe fees.
     *
     * This is based on actual successful transactions,
     * not the invoice total.
     */
    public function netAmountInCents(): int
    {
        return max(
            (int) $this->total_paid
                - $this->stripeProcessingFeeInCents(),
            0
        );
    }

    /**
     * Get actual collected net revenue in dollars.
     */
    public function netAmountInDollars(): float
    {
        return $this->netAmountInCents() / 100;
    }

    /*
    |--------------------------------------------------------------------------
    | Commission / Revenue Sharing
    |--------------------------------------------------------------------------
    */

    public function pabloCommissionInCents(): int
    {
        $net = $this->netAmountInCents();

        $rate = $net < 200000
            ? 0.10
            : 0.20;

        return (int) round($net * $rate);
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

    /*
    |--------------------------------------------------------------------------
    | Subscription
    |--------------------------------------------------------------------------
    */

    public function isSubscription(): bool
    {
        return $this->payment_type === PaymentType::Subscription;
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

    public function recurringChargeStartedAt(): Carbon
    {
        return Carbon::parse(
            $this->created_at ?? now()
        );
    }

    public function recurringChargeNextDueAt(): Carbon
    {
        return $this->recurringChargeStartedAt()
            ->copy()
            ->addMonth();
    }

    public function recurringChargeExpiresAt(): Carbon
    {
        return $this->recurringChargeStartedAt()
            ->copy()
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
    | Addons are recurring and use a Stripe recurring Price ID.
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

    /*
    |--------------------------------------------------------------------------
    | Invoice Amounts
    |--------------------------------------------------------------------------
    */

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
     */
    public function totalAmountInDollars(): float
    {
        return $this->totalAmountInCents() / 100;
    }

    /**
     * Get the amount actually paid in cents.
     */
    public function totalPaidInCents(): int
    {
        return (int) $this->total_paid;
    }

    /**
     * Get the amount actually paid in dollars.
     */
    public function totalPaidInDollars(): float
    {
        return $this->totalPaidInCents() / 100;
    }

    /*
    |--------------------------------------------------------------------------
    | Monthly Recurring Revenue
    |--------------------------------------------------------------------------
    |
    | These calculations intentionally use invoice items rather than
    | transactions. MRR represents recurring billing value, not cash
    | actually collected.
    |
    */

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
     * Estimate monthly Stripe processing fees.
     *
     * This is an estimate for recurring-value calculations only.
     * Actual Stripe fees are taken from Transaction records.
     *
     * Standard example:
     *   2.9% + 30 cents
     *
     * Returned as a POSITIVE amount.
     */
    public function estimatedMonthlyStripeFeeInCents(): int
    {
        $amount = $this->monthlyRecurringAmountInCents();

        if ($amount <= 0) {
            return 0;
        }

        $fee = ($amount * 0.029) + 30;

        return (int) round($fee);
    }

    /**
     * Estimated monthly net recurring amount after Stripe fees.
     */
    public function monthlyNetAmountInCents(): int
    {
        return max(
            $this->monthlyRecurringAmountInCents()
                - $this->estimatedMonthlyStripeFeeInCents(),
            0
        );
    }

    public function monthlyPabloCommissionInCents(): int
    {
        $gross = $this->monthlyRecurringAmountInCents();

        $rate = $gross < 200000
            ? 0.10
            : 0.20;

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

    public function nextBillingDate(): Carbon
    {
        return Carbon::parse(
            $this->created_at ?? now()
        )->addMonth();
    }

    public function renewalDate(): Carbon
    {
        $months = max(
            1,
            (int) ($this->subscription_months ?? 12)
        );

        return Carbon::parse(
            $this->created_at ?? now()
        )->addMonths($months);
    }

    /*
    |--------------------------------------------------------------------------
    | Estimated 12-Month Value
    |--------------------------------------------------------------------------
    |
    | One-time items are counted once.
    | Recurring items are counted for 12 months.
    |
    */

    public function estimated12MonthValueInCents(): int
    {
        $oneTimeAmount = (int) $this->items
            ->where('is_subscription', false)
            ->sum('amount');

        return $oneTimeAmount
            + ($this->monthlyRecurringAmountInCents() * 12);
    }

    public function estimated12MonthValueInDollars(): float
    {
        return $this->estimated12MonthValueInCents() / 100;
    }

    /*
    |--------------------------------------------------------------------------
    | Invoice Totals
    |--------------------------------------------------------------------------
    */

    /**
     * Recalculate total_amount from invoice line items.
     *
     * Recurring items are NOT multiplied by 12.
     * This represents the amount due today.
     *
     * total_paid is intentionally NOT changed here.
     */
    public function recalculateTotal(): void
    {
        $this->update([
            'total_amount' => (int) $this->items()->sum('amount'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    */

    /**
     * Get the secure custom checkout URL.
     */
    public function checkoutUrl(): string
    {
        return route(
            'checkout.show',
            $this->checkout_token
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

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