<?php

use App\Http\Controllers\GuestTicketAttachmentController;
use App\Http\Controllers\Teams\TicketAttachmentController;
use App\Http\Controllers\Teams\TicketController as TeamTicketController;
use App\Http\Controllers\TicketController as GuestTicketController;
use App\Http\Controllers\WorkController;
use App\Livewire\Home\Tickets\TicketSearch;
use App\Livewire\Teams\Projects\Index as AdminWorksIndex;
use App\Livewire\Teams\Testimonials\Index as AdminTestimonialsIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')
    ->name('home');

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
| Public Projects
|--------------------------------------------------------------------------
*/

Route::get('/projects/{work}', [WorkController::class, 'show'])
    ->name('work.show');

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

        /*
        |--------------------------------------------------------------------------
        | Ticket Attachments
        |--------------------------------------------------------------------------
        */

        Route::get(
            'tickets/attachments/{attachment}/preview',
            [TicketAttachmentController::class, 'preview']
        )->name('ticket.attachments.preview');

        Route::get(
            'tickets/attachments/{attachment}/download',
            [TicketAttachmentController::class, 'download']
        )->name('ticket.attachments.download');

        /*
        |--------------------------------------------------------------------------
        | Team Tickets
        |--------------------------------------------------------------------------
        */

        Route::get(
            'tickets/{ticket}',
            [TeamTicketController::class, 'show']
        )->name('team.tickets.show');

        /*
        |--------------------------------------------------------------------------
        | Projects
        |--------------------------------------------------------------------------
        */

        Route::get(
            'projects',
            AdminWorksIndex::class
        )->name('teams.projects.index');

        /*
        |--------------------------------------------------------------------------
        | Testimonials
        |--------------------------------------------------------------------------
        */

        Route::get(
            'testimonials',
            AdminTestimonialsIndex::class
        )->name('testimonials.index');

        Route::get(
            'testimonials/{testimonial}',
            AdminTestimonialsIndex::class
        )->name('testimonials.show');
    });

require __DIR__ . '/settings.php';