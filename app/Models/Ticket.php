<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'team_id',
        'customer_id',
        'ticket_number',
        'name',
        'email',
        'subject',
        'status',
        'priority',
        'guest_token_hash',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function accessTokens(): HasMany
    {
        return $this->hasMany(TicketAccessToken::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    /**
     * Get the current guest access token.
     */
    public function guestAccessToken(): ?TicketAccessToken
    {
        return $this->accessTokens()
            ->whereNotNull('token')
            ->latest('created_at')
            ->first();
    }

    public function getRouteKeyName(): string
    {
        return 'ticket_number';
    }

    /**
     * Find (or create) the Customer this ticket belongs to, keyed by the
     * submitter's email, and persist the link. Called when a ticket is
     * resolved and a charge is about to be created against it.
     */
    public function resolveCustomer(): Customer
    {
        if ($this->customer_id) {
            return $this->customer;
        }

        $customer = Customer::resolveFromEmail($this->email, $this->name);
        $this->update(['customer_id' => $customer->id]);

        return $customer;
    }
}
