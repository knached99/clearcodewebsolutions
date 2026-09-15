<?php

use App\Http\Controllers\GuestTicketAttachmentController;
use App\Http\Controllers\Teams\TicketAttachmentController;
use App\Http\Controllers\Teams\TicketController as TeamTicketController;
use App\Http\Controllers\TicketController as GuestTicketController;
use App\Http\Controllers\WorkController;
use App\Livewire\Home\Tickets\TicketSearch;
use App\Livewire\Teams\Projects\Index as AdminWorksIndex;
use App\Livewire\Teams\Testimonials\Index as AdminTestimonialsIndex;
use App\Livewire\Home\Projects\Index as ProjectsIndex; 
use App\Livewire\RequiredOnboarding;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::view('/', 'welcome')
    ->name('home');

/*
|--------------------------------------------------------------------------
| Onboarding
|--------------------------------------------------------------------------
|
| IMPORTANT:
| This route is intentionally NOT inside {current_team}.
|
*/

Route::get('/onboarding', RequiredOnboarding::class)
    ->middleware([
        'auth',
        'verified',
    ])
    ->name('onboarding');


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
*/

Route::get('/tickets/{ticketNumber}/{token?}', [
    GuestTicketController::class,
    'guestShow',
])
    ->where([
        'ticketNumber' => 'TCKT-[A-Z0-9]+',
        'token' => '[a-fA-F0-9]{64}',
    ])
    ->name('tickets.show');

/*
|--------------------------------------------------------------------------
| Guest Ticket Attachments
|--------------------------------------------------------------------------
*/

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

Route::get('/projects', ProjectsIndex::class)
    ->name('projects.index');

Route::get('/projects/{work}', [
    WorkController::class,
    'show',
])->name('work.show');

/*
|--------------------------------------------------------------------------
| Team / Admin
|--------------------------------------------------------------------------
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

require __DIR__.'/settings.php';
