<?php

use App\Enums\TransactionStatus;
use App\Http\Controllers\GuestTicketAttachmentController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Teams\TicketAttachmentController;
use App\Http\Controllers\Teams\TicketController as TeamTicketController;
use App\Http\Controllers\TicketController as GuestTicketController;
use App\Http\Controllers\WorkController;
use App\Livewire\Checkout\StripeCheckout;
use App\Livewire\Home\Projects\Index as ProjectsIndex;
use App\Livewire\Home\Tickets\TicketSearch;
use App\Livewire\RequiredOnboarding;
use App\Livewire\Teams\Invoices\InvoiceShow;
use App\Livewire\Teams\Projects\Index as AdminWorksIndex;
use App\Livewire\Teams\Testimonials\Index as AdminTestimonialsIndex;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Team;
use App\Models\Transaction;
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
| Checkout
|--------------------------------------------------------------------------
*/

Route::get('/checkout/{token}', StripeCheckout::class)
    ->name('checkout.show');

Route::get('/checkout/{token}/complete', function (string $token) {
    $invoice = Invoice::query()
        ->where('checkout_token', $token)
        ->with(['items', 'customer'])
        ->firstOrFail();

    return view('checkout.payment-success', [
        'invoice' => $invoice,
    ]);
})->name('checkout.complete');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

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

        Route::get('dashboard', function (string $current_team) {
            $team = Team::query()->where('slug', $current_team)->firstOrFail();
            $invoiceIds = Invoice::query()
                ->whereIn('ticket_id', $team->tickets()->pluck('id'))
                ->pluck('id');

            $successfulTransactions = Transaction::query()
                ->whereIn('invoice_id', $invoiceIds)
                ->where('status', TransactionStatus::Succeeded)
                ->get();

            $failedTransactions = Transaction::query()
                ->whereIn('invoice_id', $invoiceIds)
                ->where('status', TransactionStatus::Failed)
                ->get();

            $grossProfit = $successfulTransactions->sum('amount');
            $losses = $failedTransactions->sum('amount');
            $netProfit = $grossProfit - $losses;

            $subscriptionInvoices = Invoice::query()
                ->whereIn('ticket_id', $team->tickets()->pluck('id'))
                ->where('payment_type', \App\Enums\PaymentType::Subscription)
                ->where('status', \App\Enums\InvoiceStatus::Paid)
                ->with('items')
                ->get();

            $mrr = $subscriptionInvoices->sum(fn ($invoice) => (int) $invoice->monthlyRecurringAmountInCents());
            $arr = $mrr * 12;
            $grossMarginPercentage = $grossProfit > 0
                ? round(($grossProfit / max($successfulTransactions->sum('amount'), 1)) * 100, 2)
                : 0;

            $customerRevenue = $successfulTransactions
                ->groupBy('customer_id')
                ->map(function ($transactions, $customerId) {
                    $customer = Customer::query()->find($customerId);

                    return [
                        'name' => $customer?->name ?: $customer?->email ?: 'Unknown',
                        'amount' => $transactions->sum('amount'),
                    ];
                })
                ->sortByDesc('amount')
                ->values();

            $highestProfitingCustomer = $customerRevenue->first();

            $monthlySeries = collect(range(0, 11))->map(function ($offset) {
                $month = now()->startOfMonth()->subMonths(11 - $offset);
                $key = $month->format('Y-m');

                return [
                    'label' => $month->format('M'),
                    'amount' => 0,
                    'month' => $key,
                ];
            });

            $successfulTransactions->groupBy(function ($transaction) {
                return $transaction->created_at?->format('Y-m') ?? now()->format('Y-m');
            })->each(function ($transactions, $key) use ($monthlySeries) {
                $entry = $monthlySeries->firstWhere('month', $key);

                if ($entry) {
                    $entry['amount'] = $transactions->sum('amount');
                }
            });

            $monthlyRevenue = $monthlySeries
                ->map(fn ($entry) => [
                    'month' => $entry['label'],
                    'value' => (int) $entry['amount'],
                ]);

            $subscriptionRevenue = $successfulTransactions
                ->filter(fn ($transaction) => $transaction->invoice?->payment_type === \App\Enums\PaymentType::Subscription)
                ->sum('amount');

            $oneTimeRevenue = $successfulTransactions
                ->filter(fn ($transaction) => $transaction->invoice?->payment_type !== \App\Enums\PaymentType::Subscription)
                ->sum('amount');

            $totalInvoices = Invoice::query()
                ->whereIn('ticket_id', $team->tickets()->pluck('id'))
                ->count();

            $paidInvoices = Invoice::query()
                ->whereIn('ticket_id', $team->tickets()->pluck('id'))
                ->where('status', \App\Enums\InvoiceStatus::Paid)
                ->count();

            $activeSubscriptions = Invoice::query()
                ->whereIn('ticket_id', $team->tickets()->pluck('id'))
                ->where('payment_type', \App\Enums\PaymentType::Subscription)
                ->where('status', \App\Enums\InvoiceStatus::Paid)
                ->count();

            return view('dashboard', [
                'team' => $team,
                'grossProfit' => $grossProfit,
                'netProfit' => $netProfit,
                'losses' => $losses,
                'mrr' => $mrr,
                'arr' => $arr,
                'grossMarginPercentage' => $grossMarginPercentage,
                'highestProfitingCustomer' => $highestProfitingCustomer,
                'monthlyRevenue' => $monthlyRevenue,
                'customerRevenue' => $customerRevenue,
                'subscriptionRevenue' => $subscriptionRevenue,
                'oneTimeRevenue' => $oneTimeRevenue,
                'totalInvoices' => $totalInvoices,
                'paidInvoices' => $paidInvoices,
                'activeSubscriptions' => $activeSubscriptions,
                'averageInvoiceValue' => $invoiceIds->isNotEmpty() ? round($successfulTransactions->sum('amount') / max($paidInvoices, 1), 2) : 0,
            ]);
        })->name('dashboard');

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

        Route::get('/invoices/{invoice}', InvoiceShow::class)->name('invoices.show');
    });

require __DIR__.'/settings.php';
