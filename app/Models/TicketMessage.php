<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketMessage extends Model 
{
    protected $fillable = [
        'ticket_id', 
        'author_type',
        'author_name', 
        'message',
    ];

    public function ticket(): BelongsTo 
    {
        return $this->belongsTo(Ticket::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketMessageAttachment::class, 'ticket_message_id');
    }
}