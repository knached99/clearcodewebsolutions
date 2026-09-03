<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketMessageAttachment extends Model
{
    protected $table = 'ticket_message_attachments';

    protected $fillable = [
        'ticket_message_id',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function message()
    {
        return $this->belongsTo(
            TicketMessage::class,
            'ticket_message_id'
        );
    }
}
