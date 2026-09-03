<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessageAttachment;
use App\Services\TicketService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestTicketAttachmentController extends Controller
{
    /**
     * Preview an attachment inline.
     */
    public function preview(
        TicketMessageAttachment $attachment,
        TicketService $ticketService
    ): StreamedResponse {
        $ticket = $this->authorizeAttachment(
            $attachment,
            $ticketService
        );

        $disk = Storage::disk('public');

        abort_unless(
            $attachment->path &&
            $disk->exists($attachment->path),
            404,
            'Attachment file not found.'
        );

        $mime = $attachment->mime_type
            ?: $disk->mimeType($attachment->path)
            ?: 'application/octet-stream';

        $filename = $this->safeFilename(
            $attachment->original_name
        );

        return response()->stream(
            function () use ($disk, $attachment): void {
                $stream = $disk->readStream($attachment->path);

                abort_unless(
                    $stream !== false,
                    404,
                    'Unable to read attachment.'
                );

                fpassthru($stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }
            },
            200,
            [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
                'Cache-Control' => 'private, max-age=3600',
            ]
        );
    }

    /**
     * Download an attachment.
     */
    public function download(
        TicketMessageAttachment $attachment,
        TicketService $ticketService
    ): StreamedResponse {
        $ticket = $this->authorizeAttachment(
            $attachment,
            $ticketService
        );

        $disk = Storage::disk('public');

        abort_unless(
            $attachment->path &&
            $disk->exists($attachment->path),
            404,
            'Attachment file not found.'
        );

        return $disk->download(
            $attachment->path,
            $this->safeFilename($attachment->original_name)
        );
    }

    /**
     * Make sure this attachment belongs to a real ticket
     * and that the guest is allowed to access that ticket.
     */
    protected function authorizeAttachment(
        TicketMessageAttachment $attachment,
        TicketService $ticketService
    ): Ticket {
        /*
         * We only need the message here.
         *
         * This deliberately does NOT depend on a nested
         * message.ticket relationship existing.
         */
        $attachment->loadMissing('message');

        abort_unless(
            $attachment->message !== null,
            404,
            'Attachment message not found.'
        );

        $ticketId = (int) $attachment->message->ticket_id;

        abort_unless(
            $ticketId > 0,
            404,
            'Attachment ticket not found.'
        );

        $ticket = Ticket::query()->find($ticketId);

        abort_unless(
            $ticket !== null,
            404,
            'Ticket not found.'
        );

        /*
         * This is the same guest authorization used by
         * GuestTicket::mount(), sendReply(), and render().
         */
        $ticketService->requireGuestTicketAccess($ticket);

        return $ticket;
    }

    /**
     * Prevent invalid characters from being placed into
     * the Content-Disposition header.
     */
    protected function safeFilename(?string $filename): string
    {
        $filename = trim((string) $filename);

        if ($filename === '') {
            return 'attachment';
        }

        $filename = basename($filename);

        return str_replace(
            ["\r", "\n", '"'],
            ['', '', "'"],
            $filename
        );
    }
}