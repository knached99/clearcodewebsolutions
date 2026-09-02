<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\TicketMessageAttachment;
use Illuminate\Support\Facades\Storage;

class TicketAttachmentController extends Controller
{
    public function preview(
        string $current_team,
        TicketMessageAttachment $attachment
    ) {
        $attachment->load('message.ticket');

        $ticket = $attachment->message?->ticket;

        abort_unless($ticket, 404);

        abort_unless(
            auth()->user()
                ->teams()
                ->whereKey($ticket->team_id)
                ->exists(),
            403
        );

        abort_unless(
            str_starts_with(
                strtolower($attachment->mime_type ?? ''),
                'image/'
            ),
            404
        );

        $disk = Storage::disk('local');

        abort_unless(
            $disk->exists($attachment->file_path),
            404
        );

        return response()->file(
            $disk->path($attachment->file_path),
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'private, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function download(
        string $current_team,
        TicketMessageAttachment $attachment
    ) {
        $attachment->load('message.ticket');

        $ticket = $attachment->message?->ticket;

        abort_unless($ticket, 404);

        abort_unless(
            auth()->user()
                ->teams()
                ->whereKey($ticket->team_id)
                ->exists(),
            403
        );

        $disk = Storage::disk('local');

        abort_unless(
            $disk->exists($attachment->file_path),
            404
        );

        return $disk->download(
            $attachment->file_path,
            $attachment->file_name
        );
    }
}