<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'team_id',
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

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function accessTokens(): HasMany
    {
        return $this->hasMany(TicketAccessToken::class);
    }

    public function getRouteKeyName(): string
    {
        return 'ticket_number';
    }
}
