<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TicketMessageAttachment;
use Illuminate\Support\Facades\Storage;

class TicketAttachmentController extends Controller
{
    public function preview(
        string $current_team,
        TicketMessageAttachment $attachment
    ) {
        $team = Team::query()
            ->where('slug', $current_team)
            ->firstOrFail();

        $attachment->load('message.ticket');

        abort_unless(
            $attachment->message?->ticket?->team_id === $team->id,
            404
        );

        $disk = Storage::disk('public');

        abort_unless(
            $attachment->path && $disk->exists($attachment->path),
            404
        );

        $stream = $disk->readStream($attachment->path);

        abort_unless($stream !== false, 404);

        return response()->stream(
            function () use ($stream) {
                fpassthru($stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }
            },
            200,
            [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="'.
                    addslashes($attachment->original_name).
                    '"',
                'Cache-Control' => 'private, max-age=3600',
            ]
        );
    }

    public function download(
        string $current_team,
        TicketMessageAttachment $attachment
    ) {
        $team = Team::query()
            ->where('slug', $current_team)
            ->firstOrFail();

        $attachment->load('message.ticket');

        abort_unless(
            $attachment->message?->ticket?->team_id === $team->id,
            404
        );

        $disk = Storage::disk('public');

        abort_unless(
            $attachment->path && $disk->exists($attachment->path),
            404
        );

        return $disk->download(
            $attachment->path,
            $attachment->original_name
        );
    }
}
