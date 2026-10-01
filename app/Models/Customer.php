<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable; // Added Cashier Trait

class Customer extends Model
{
    use Billable, HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'stripe_id',
        'pm_type',        // Added by Cashier
        'pm_last_four',   // Added by Cashier
        'trial_ends_at',  // Added by Cashier
        'name',
        'email',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class); // [cite: 10]
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class); // [cite: 10]
    }

    // REMOVED: custom paymentMethods(), invoices(), transactions(), defaultPaymentMethod()[cite: 10]

    public static function resolveFromEmail(string $email, ?string $name = null): self
    {
        $customer = static::firstOrCreate(
            ['email' => $email],
            ['name' => $name ?: $email],
        );

        if (! $customer->user_id) {
            $customer->linkExistingUser(); // [cite: 10]
        }

        // Ensure the Stripe Customer exists immediately via Cashier
        if (! $customer->hasStripeId()) {
            $customer->createAsStripeCustomer();
        }

        return $customer;
    }

    public function linkExistingUser(): void
    {
        if ($this->user_id) {
            return;
        }

        $user = User::where('email', $this->email)->first(); // [cite: 10]

        if (! $user) {
            return;
        }

        $this->update(['user_id' => $user->id]); // [cite: 10]
        $this->backfillTickets(); // [cite: 10]
    }

    public function backfillTickets(): void
    {
        Ticket::where('email', $this->email) // [cite: 10]
            ->whereNull('customer_id') // [cite: 10]
            ->update(['customer_id' => $this->id]); // [cite: 10]
    }
}
