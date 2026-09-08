<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAccessToken extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'token_hash',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'token' => 'encrypted',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }
}