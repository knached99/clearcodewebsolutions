<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'stripe_id',
        'name',
        'email',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function defaultPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethods()->where('is_default', true)->first();
    }

    /**
     * Find the Customer for an email, creating it if it doesn't exist yet.
     * This is the single entry point for turning a ticket submitter (who
     * may never have logged in) into a billable customer.
     */
    public static function resolveFromEmail(string $email, ?string $name = null): self
    {
        $customer = static::firstOrCreate(
            ['email' => $email],
            ['name' => $name ?: $email],
        );

        if (! $customer->user_id) {
            $customer->linkExistingUser();
        }

        return $customer;
    }

    /**
     * If a User already exists with this customer's email, link them and
     * pull in any tickets created before that user had an account.
     */
    public function linkExistingUser(): void
    {
        if ($this->user_id) {
            return;
        }

        $user = User::where('email', $this->email)->first();

        if (! $user) {
            return;
        }

        $this->update(['user_id' => $user->id]);
        $this->backfillTickets();
    }

    /**
     * Attach any tickets matching this customer's email that aren't
     * linked yet, so the customer dashboard shows their full history.
     */
    public function backfillTickets(): void
    {
        Ticket::where('email', $this->email)
            ->whereNull('customer_id')
            ->update(['customer_id' => $this->id]);
    }
}