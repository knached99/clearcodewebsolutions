<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

class Customer extends Model
{
    use Billable, HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
        'name',
        'email',
    ];

    /**
     * Customer's linked Laravel user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tickets belonging to this customer.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Payment-location records captured for this customer.
     *
     * A customer can have multiple payment locations over time.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(CustomerPaymentLocation::class);
    }

    /**
     * Resolve or create a customer by email.
     *
     * Also:
     * - links an existing Laravel user when available
     * - ensures the Stripe customer exists
     */
    public static function resolveFromEmail(
        string $email,
        ?string $name = null
    ): self {
        $customer = static::firstOrCreate(
            [
                'email' => $email,
            ],
            [
                'name' => $name ?: $email,
            ],
        );

        if (! $customer->user_id) {
            $customer->linkExistingUser();
        }

        /*
         * Ensure the Stripe Customer exists immediately via Cashier.
         */
        if (! $customer->hasStripeId()) {
            $customer->createAsStripeCustomer();
        }

        return $customer;
    }

    /**
     * Link this customer to an existing Laravel user
     * with the same email address.
     */
    public function linkExistingUser(): void
    {
        if ($this->user_id) {
            return;
        }

        $user = User::query()
            ->where('email', $this->email)
            ->first();

        if (! $user) {
            return;
        }

        $this->update([
            'user_id' => $user->id,
        ]);

        $this->backfillTickets();
    }

    /**
     * Backfill existing tickets that have the same email
     * but are not yet associated with this customer.
     */
    public function backfillTickets(): void
    {
        Ticket::query()
            ->where('email', $this->email)
            ->whereNull('customer_id')
            ->update([
                'customer_id' => $this->id,
            ]);
    }
}