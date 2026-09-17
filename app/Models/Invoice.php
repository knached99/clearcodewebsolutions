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
            if ($invoice->payment_type === PaymentType::Subscription
                && (int) $invoice->subscription_months < 12) {
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

    public function totalAmountInDollars(): float
    {
        return $this->total_amount / 100;
    }

    /**
     * Recalculate total_amount from the sum of its line items.
     */
    public function recalculateTotal(): void
    {
        $this->update(['total_amount' => $this->items()->sum('amount')]);
    }

    /**
     * The secure, non-Stripe-hosted checkout page for this invoice.
     * Route is wired up in the checkout-page phase.
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
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $this]);

        return $pdf->output();
    }
}
