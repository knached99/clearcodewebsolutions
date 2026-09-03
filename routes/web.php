<?php

use App\Http\Controllers\GuestTicketAttachmentController;
use App\Http\Controllers\Teams\TicketAttachmentController;
use App\Http\Controllers\Teams\TicketController as TeamTicketController;
use App\Http\Controllers\TicketController as GuestTicketController;
use App\Livewire\Home\Tickets\TicketSearch;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

/*
|--------------------------------------------------------------------------
| Guest Ticket Search
|--------------------------------------------------------------------------
*/

Route::get('/tickets', TicketSearch::class)
    ->name('home.tickets.ticket-search');

/*
|--------------------------------------------------------------------------
| Guest Ticket
|--------------------------------------------------------------------------
|
| /tickets/TCKT-123/TOKEN
|
*/

Route::get('/tickets/{ticketNumber}/{token}', [
    GuestTicketController::class,
    'guestShow',
])->name('tickets.show');

Route::get(
    '/tickets/attachments/{attachment}/preview',
    [GuestTicketAttachmentController::class, 'preview']
)->name('guest.ticket.attachments.preview');

Route::get(
    '/tickets/attachments/{attachment}/download',
    [GuestTicketAttachmentController::class, 'download']
)->name('guest.ticket.attachments.download');

/*
|--------------------------------------------------------------------------
| Team / Admin
|--------------------------------------------------------------------------
|
| /karam-toors-team/tickets/TCKT-123
|
*/

Route::prefix('{current_team}')
    ->middleware([
        'auth',
        'verified',
    ])
    ->group(function () {

        Route::view('dashboard', 'dashboard')
            ->name('dashboard');

        Route::get(
            'tickets/attachments/{attachment}/preview',
            [TicketAttachmentController::class, 'preview']
        )->name('ticket.attachments.preview');

        Route::get(
            'tickets/attachments/{attachment}/download',
            [TicketAttachmentController::class, 'download']
        )->name('ticket.attachments.download');

        Route::get(
            'tickets/{ticket}',
            [TeamTicketController::class, 'show']
        )->name('team.tickets.show');
    });

require __DIR__.'/settings.php';
