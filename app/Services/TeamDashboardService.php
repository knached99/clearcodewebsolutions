<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentType;
use App\Enums\TransactionStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Team;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Throwable;

class TeamDashboardService
{
    protected object $stripe;

    public function __construct(?object $stripe = null)
    {
        $this->stripe = $stripe ?? new StripeClient(
            config('services.stripe.secret')
                ?: 'sk_test_placeholder'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */

    protected function logStep(
        string $step,
        float $startedAt,
        array $context = []
    ): void {
        Log::debug(
            'TeamDashboardService: ' . $step,
            [
                ...$context,
                'duration_ms' => round(
                    (microtime(true) - $startedAt) * 1000,
                    2
                ),
            ]
        );
    }

    protected function logStart(
        string $step,
        array $context = []
    ): void {
        Log::info(
            'TeamDashboardService: ' . $step . ' START',
            $context
        );
    }

    protected function logComplete(
        string $step,
        float $startedAt,
        array $context = []
    ): void {
        Log::info(
            'TeamDashboardService: ' . $step . ' COMPLETE',
            [
                ...$context,
                'duration_ms' => round(
                    (microtime(true) - $startedAt) * 1000,
                    2
                ),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Date Filtering
    |--------------------------------------------------------------------------
    */

    protected function normalizeFilter(string $filter): string
    {
        return in_array(
            $filter,
            ['max', 'mtd', 'ytd'],
            true
        )
            ? $filter
            : 'max';
    }

    protected function filterDateRange(string $filter): ?array
    {
        $filter = $this->normalizeFilter($filter);

        $now = now();

        return match ($filter) {
            'mtd' => [
                $now->copy()->startOfMonth(),
                $now->copy(),
            ],

            'ytd' => [
                $now->copy()->startOfYear(),
                $now->copy(),
            ],

            default => null,
        };
    }

    protected function applyDateFilter(
        Collection|EloquentCollection $collection,
        string $filter,
        string $dateColumn = 'created_at'
    ): Collection|EloquentCollection {
        $startedAt = microtime(true);

        $filter = $this->normalizeFilter($filter);

        Log::debug(
            'TeamDashboardService: applyDateFilter START',
            [
                'filter' => $filter,
                'date_column' => $dateColumn,
                'collection_count' => $collection->count(),
            ]
        );

        if ($filter === 'max') {
            $result = $collection->values();

            $this->logComplete(
                'applyDateFilter MAX',
                $startedAt,
                [
                    'result_count' => $result->count(),
                ]
            );

            return $result;
        }

        $dateRange = $this->filterDateRange($filter);

        if (! $dateRange) {
            $result = $collection->values();

            $this->logComplete(
                'applyDateFilter no range',
                $startedAt,
                [
                    'result_count' => $result->count(),
                ]
            );

            return $result;
        }

        [$start, $end] = $dateRange;

        Log::debug(
            'TeamDashboardService: applyDateFilter range',
            [
                'filter' => $filter,
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
                'collection_count' => $collection->count(),
            ]
        );

        $result = $collection
            ->filter(
                function ($item) use (
                    $dateColumn,
                    $start,
                    $end
                ) {
                    $date = $item->{$dateColumn} ?? null;

                    if (! $date) {
                        return false;
                    }

                    if (! $date instanceof Carbon) {
                        $date = Carbon::parse($date);
                    }

                    return $date->betweenIncluded(
                        $start,
                        $end
                    );
                }
            )
            ->values();

        $this->logComplete(
            'applyDateFilter',
            $startedAt,
            [
                'filter' => $filter,
                'input_count' => $collection->count(),
                'result_count' => $result->count(),
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
            ]
        );

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboardData(
        Team $team,
        string $filter = 'max'
    ): array {
        $startedAt = microtime(true);

        $this->logStart(
            'dashboardData',
            [
                'team_id' => $team->id,
                'team_slug' => $team->slug,
                'filter_received' => $filter,
            ]
        );

        try {
            /*
            |--------------------------------------------------------------------------
            | Normalize Filter
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $filter = $this->normalizeFilter($filter);

            $this->logComplete(
                'normalizeFilter',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'filter' => $filter,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Date Range
            |--------------------------------------------------------------------------
            */

            $dateRange = $this->filterDateRange($filter);

            if ($dateRange) {
                [$rangeStart, $rangeEnd] = $dateRange;

                Log::info(
                    'TeamDashboardService: reporting range',
                    [
                        'team_id' => $team->id,
                        'filter' => $filter,
                        'start' => $rangeStart->toDateTimeString(),
                        'end' => $rangeEnd->toDateTimeString(),
                    ]
                );
            } else {
                Log::info(
                    'TeamDashboardService: reporting range',
                    [
                        'team_id' => $team->id,
                        'filter' => $filter,
                        'start' => null,
                        'end' => null,
                        'description' => 'all time',
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Team Invoice IDs
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $teamInvoiceIds = $this->teamInvoiceIds($team);

            $this->logComplete(
                'teamInvoiceIds',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'count' => $teamInvoiceIds->count(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Failed Transactions
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $allFailedTransactions = $this->failedTransactions(
                $teamInvoiceIds
            );

            $this->logComplete(
                'failedTransactions',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'count' => $allFailedTransactions->count(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Failed Transaction Date Filter
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $failedTransactions = $this->applyDateFilter(
                $allFailedTransactions,
                $filter
            );

            $this->logComplete(
                'failedTransactions date filter',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'filter' => $filter,
                    'count' => $failedTransactions->count(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Paid Invoices
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $allPaidInvoices = $this->paidInvoices($team);

            $this->logComplete(
                'paidInvoices',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'count' => $allPaidInvoices->count(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Paid Invoice Date Filter
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $paidInvoices = $this->applyDateFilter(
                $allPaidInvoices,
                $filter
            );

            $this->logComplete(
                'paidInvoices date filter',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'filter' => $filter,
                    'count' => $paidInvoices->count(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Stripe Revenue
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            Log::info(
                'TeamDashboardService: stripeRevenueForTeam about to run',
                [
                    'team_id' => $team->id,
                    'filter' => $filter,
                ]
            );

            $stripeData = $this->stripeRevenueForTeam($team);

            $this->logComplete(
                'stripeRevenueForTeam',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'filter' => $filter,
                    'transaction_count' =>
                        $stripeData['transactions']->count(),
                    'revenue' =>
                        $stripeData['revenue'],
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Successful Stripe Transactions
            |--------------------------------------------------------------------------
            */

            $allSuccessfulTransactions =
                $stripeData['transactions'];

            Log::debug(
                'TeamDashboardService: successful transactions loaded',
                [
                    'team_id' => $team->id,
                    'count' =>
                        $allSuccessfulTransactions->count(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Successful Transaction Date Filter
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $successfulTransactions = $this->applyDateFilter(
                $allSuccessfulTransactions,
                $filter
            );

            $this->logComplete(
                'successfulTransactions date filter',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'filter' => $filter,
                    'before_count' =>
                        $allSuccessfulTransactions->count(),
                    'after_count' =>
                        $successfulTransactions->count(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Gross Revenue
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $grossProfit = (int) $successfulTransactions
                ->sum('amount');

            $this->logComplete(
                'grossProfit calculation',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'filter' => $filter,
                    'gross_profit' => $grossProfit,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Stripe Processing Fees
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $processingFees = (int) $successfulTransactions
                ->sum('processing_fee');

            $this->logComplete(
                'processingFees calculation',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'processing_fees' => $processingFees,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Real Net Profit
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $hasNetAmounts = $successfulTransactions->contains(
                fn ($transaction) =>
                    isset($transaction->net_amount)
            );

            if ($hasNetAmounts) {
                $netProfit = (int) $successfulTransactions
                    ->sum('net_amount');
            } else {
                $netProfit =
                    $grossProfit
                    + $processingFees;
            }

            $this->logComplete(
                'netProfit calculation',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'has_net_amounts' => $hasNetAmounts,
                    'net_profit' => $netProfit,
                ]
            );

            /* Profit Distribution
                - Pablo's Comission 
                - Net Amount 
                - Team share after Pablo's share 
                - Owner share (Khaled)
                - Partner share (Karam)
                - calculates these per invoice before aggregating.
                - Pablo's rate depends on the individual invoice gross amount
            */
                $pabloCommission = 0; 
                $teamShareAfterPablo = 0;
                $ownerShare = 0;
                $partnerShare = 0;

                foreach ($paidInvoices as $invoice) {
                    $pabloCommission += $invoice->pabloCommissionInCents();
                    $teamShareAfterPablo += $invoice->teamShareAfterPabloInCents();
                    $ownerShare += $invoice->ownerShareInCents();
                    $partnerShare += $invoice->partnerShareInCents();
                }

                // backwards compatible aliases 
                $khaledShare = $ownerShare;
                $karamShare = $partnerShare;

                // Pablo's Rate 
                // invoice determines the rate per invoice 
                $pabloRate = $grossProfit > 0 ? $pabloCommission / $grossProfit : 0;
                
                $this->logComplete(
                'profit distribution calculation',
                $stepStartedAt,
                [
                    'team_id' =>
                        $team->id,

                    'invoice_count' =>
                        $paidInvoices->count(),

                    'pablo_commission' =>
                        $pabloCommission,

                    'pablo_rate' => $pabloRate, 

                    'team_share_after_pablo' =>
                        $teamShareAfterPablo,

                    'owner_share' =>
                        $ownerShare,

                    'partner_share' =>
                        $partnerShare,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Losses
            |--------------------------------------------------------------------------
            */

            $losses = (int) $failedTransactions
                ->sum('amount');

            /*
            |--------------------------------------------------------------------------
            | Current MRR / ARR
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $subscriptionInvoices =
                $this->subscriptionInvoices($team);

            $this->logComplete(
                'subscriptionInvoices',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'count' => $subscriptionInvoices->count(),
                ]
            );

            $stepStartedAt = microtime(true);

            $mrr = (int) $subscriptionInvoices->sum(
                fn (Invoice $invoice) =>
                    (int) $invoice
                        ->monthlyRecurringAmountInCents()
            );

            $arr = $mrr * 12;

            $this->logComplete(
                'MRR / ARR calculation',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'mrr' => $mrr,
                    'arr' => $arr,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Gross Margin
            |--------------------------------------------------------------------------
            */

            $grossMarginPercentage = $grossProfit > 0
                ? round(
                    (
                        $grossProfit
                        / max(
                            $grossProfit + $losses,
                            1
                        )
                    ) * 100,
                    2
                )
                : 0;

            /*
            |--------------------------------------------------------------------------
            | Customer Revenue
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            Log::debug(
                'TeamDashboardService: customerRevenue START',
                [
                    'team_id' => $team->id,
                    'transaction_count' =>
                        $successfulTransactions->count(),
                    'paid_invoice_count' =>
                        $paidInvoices->count(),
                ]
            );

            $customerRevenue = $this->customerRevenue(
                $successfulTransactions,
                $paidInvoices
            );

            $this->logComplete(
                'customerRevenue',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'customer_count' =>
                        $customerRevenue->count(),
                ]
            );

            $highestProfitingCustomer =
                $customerRevenue->first();

            /*
            |--------------------------------------------------------------------------
            | Monthly Revenue
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            Log::debug(
                'TeamDashboardService: monthlyRevenue START',
                [
                    'team_id' => $team->id,
                    'filter' => $filter,
                    'transaction_count' =>
                        $successfulTransactions->count(),
                ]
            );

            $monthlyRevenue = $this->monthlyRevenue(
                $successfulTransactions,
                $filter
            );

            $this->logComplete(
                'monthlyRevenue',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'filter' => $filter,
                    'result_count' =>
                        $monthlyRevenue->count(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Revenue By Payment Type
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $subscriptionRevenue =
                $this->revenueForPaymentType(
                    $successfulTransactions,
                    $paidInvoices,
                    PaymentType::Subscription
                );

            $this->logComplete(
                'subscriptionRevenue',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'amount' => $subscriptionRevenue,
                ]
            );

            $stepStartedAt = microtime(true);

            $oneTimeRevenue =
                $this->revenueForPaymentType(
                    $successfulTransactions,
                    $paidInvoices,
                    PaymentType::OneTime
                );

            $this->logComplete(
                'oneTimeRevenue',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'amount' => $oneTimeRevenue,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Invoice Counts
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            Log::debug(
                'TeamDashboardService: invoice count queries START',
                [
                    'team_id' => $team->id,
                    'filter' => $filter,
                ]
            );

            $invoiceTicketIds = $team
                ->tickets()
                ->pluck('id');

            Log::debug(
                'TeamDashboardService: invoice ticket IDs loaded',
                [
                    'team_id' => $team->id,
                    'ticket_count' =>
                        $invoiceTicketIds->count(),
                ]
            );

            $totalInvoicesQuery = Invoice::query()
                ->whereIn(
                    'ticket_id',
                    $invoiceTicketIds
                );

            $paidInvoicesQuery = Invoice::query()
                ->whereIn(
                    'ticket_id',
                    $invoiceTicketIds
                )
                ->where(
                    'status',
                    InvoiceStatus::Paid
                );

            $dateRange = $this->filterDateRange($filter);

            if ($dateRange) {
                [$start, $end] = $dateRange;

                Log::debug(
                    'TeamDashboardService: invoice date range applied',
                    [
                        'team_id' => $team->id,
                        'filter' => $filter,
                        'start' =>
                            $start->toDateTimeString(),
                        'end' =>
                            $end->toDateTimeString(),
                    ]
                );

                $totalInvoicesQuery->whereBetween(
                    'created_at',
                    [$start, $end]
                );

                $paidInvoicesQuery->whereBetween(
                    'created_at',
                    [$start, $end]
                );
            }

            $countStartedAt = microtime(true);

            $totalInvoices =
                $totalInvoicesQuery->count();

            $this->logComplete(
                'totalInvoices count',
                $countStartedAt,
                [
                    'team_id' => $team->id,
                    'count' => $totalInvoices,
                ]
            );

            $countStartedAt = microtime(true);

            $paidInvoicesCount =
                $paidInvoicesQuery->count();

            $this->logComplete(
                'paidInvoices count',
                $countStartedAt,
                [
                    'team_id' => $team->id,
                    'count' => $paidInvoicesCount,
                ]
            );

            $this->logComplete(
                'invoice count queries',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'total_invoices' => $totalInvoices,
                    'paid_invoices' => $paidInvoicesCount,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Active Subscriptions
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $activeSubscriptions = Invoice::query()
                ->whereIn(
                    'ticket_id',
                    $invoiceTicketIds
                )
                ->where(
                    'payment_type',
                    PaymentType::Subscription
                )
                ->where(
                    'status',
                    InvoiceStatus::Paid
                )
                ->count();

            $this->logComplete(
                'activeSubscriptions',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'count' => $activeSubscriptions,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Average Invoice
            |--------------------------------------------------------------------------
            */

            $averageInvoiceValue = $paidInvoicesCount > 0
                ? (int) round(
                    $grossProfit / $paidInvoicesCount
                )
                : 0;

            /*
            |--------------------------------------------------------------------------
            | Final Dashboard Logging
            |--------------------------------------------------------------------------
            */

            Log::info(
                'TeamDashboardService: dashboardData END',
                [
                    'team_id' => $team->id,
                    'team_slug' => $team->slug,
                    'filter' => $filter,

                    'gross_profit' => $grossProfit,
                    'processing_fees' => $processingFees,
                    'net_profit' => $netProfit,

                    'pablo_rate' => $pabloRate,
                    'pablo_commission' => $pabloCommission,

                    'team_share_after_pablo' =>
                        $teamShareAfterPablo,

                    'khaled_share' => $khaledShare,
                    'karam_share' => $karamShare,

                    'losses' => $losses,

                    'mrr' => $mrr,
                    'arr' => $arr,

                    'gross_margin_percentage' =>
                        $grossMarginPercentage,

                    'monthly_revenue_count' =>
                        $monthlyRevenue->count(),

                    'customer_revenue_count' =>
                        $customerRevenue->count(),

                    'subscription_revenue' =>
                        $subscriptionRevenue,

                    'one_time_revenue' =>
                        $oneTimeRevenue,

                    'total_invoices' =>
                        $totalInvoices,

                    'paid_invoices' =>
                        $paidInvoicesCount,

                    'active_subscriptions' =>
                        $activeSubscriptions,

                    'average_invoice' =>
                        $averageInvoiceValue,

                    'total_duration_ms' => round(
                        (microtime(true) - $startedAt) * 1000,
                        2
                    ),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Return Dashboard Data
            |--------------------------------------------------------------------------
            */

            return [
                'team' => $team,

                'grossProfit' => $grossProfit,

                'netProfit' => $netProfit,

                'processingFees' => $processingFees,

                'pabloRate' => $pabloRate,

                'pabloCommission' => $pabloCommission,

                'teamShareAfterPablo' =>
                    $teamShareAfterPablo,

                'khaledShare' =>
                    $khaledShare,

                'karamShare' =>
                    $karamShare,

                'ownerShare' =>
                    $ownerShare,

                'partnerShare' =>
                    $partnerShare,

                'losses' => $losses,

                'mrr' => $mrr,

                'arr' => $arr,

                'grossMarginPercentage' =>
                    $grossMarginPercentage,

                'highestProfitingCustomer' =>
                    $highestProfitingCustomer,

                'monthlyRevenue' =>
                    $monthlyRevenue,

                'customerRevenue' =>
                    $customerRevenue,

                'subscriptionRevenue' =>
                    $subscriptionRevenue,

                'oneTimeRevenue' =>
                    $oneTimeRevenue,

                'totalInvoices' =>
                    $totalInvoices,

                'paidInvoices' =>
                    $paidInvoicesCount,

                'activeSubscriptions' =>
                    $activeSubscriptions,

                'averageInvoiceValue' =>
                    $averageInvoiceValue,

                // Backwards-compatible frontend alias.
                'averageInvoice' =>
                    $averageInvoiceValue,
            ];
        } catch (Throwable $e) {
            Log::error(
                'TeamDashboardService: dashboardData FAILED',
                [
                    'team_id' => $team->id ?? null,
                    'team_slug' => $team->slug ?? null,
                    'filter' => $filter ?? null,

                    'exception_class' =>
                        get_class($e),

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),

                    'duration_ms' => round(
                        (microtime(true) - $startedAt) * 1000,
                        2
                    ),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Team Invoice IDs
    |--------------------------------------------------------------------------
    */

    protected function teamInvoiceIds(
        Team $team
    ): Collection {
        return Invoice::query()
            ->whereIn(
                'ticket_id',
                $team->tickets()->pluck('id')
            )
            ->pluck('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Successful Transactions
    |--------------------------------------------------------------------------
    */

    protected function successfulTransactions(
        Collection $teamInvoiceIds
    ): EloquentCollection {
        return Transaction::query()
            ->whereIn(
                'invoice_id',
                $teamInvoiceIds
            )
            ->where(
                'status',
                TransactionStatus::Succeeded
            )
            ->whereNotNull(
                'stripe_payment_intent_id'
            )
            ->where(
                'stripe_payment_intent_id',
                '!=',
                ''
            )
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Failed Transactions
    |--------------------------------------------------------------------------
    */

    protected function failedTransactions(
        Collection $teamInvoiceIds
    ): EloquentCollection {
        return Transaction::query()
            ->whereIn(
                'invoice_id',
                $teamInvoiceIds
            )
            ->where(
                'status',
                TransactionStatus::Failed
            )
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Paid Invoices
    |--------------------------------------------------------------------------
    */

    protected function paidInvoices(
        Team $team
    ): EloquentCollection {
        return Invoice::query()
            ->whereIn(
                'ticket_id',
                $team->tickets()->pluck('id')
            )
            ->where(
                'status',
                InvoiceStatus::Paid
            )
            ->with('customer')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Subscription Invoices
    |--------------------------------------------------------------------------
    */

    protected function subscriptionInvoices(
        Team $team
    ): EloquentCollection {
        return Invoice::query()
            ->whereIn(
                'ticket_id',
                $team->tickets()->pluck('id')
            )
            ->where(
                'payment_type',
                PaymentType::Subscription
            )
            ->where(
                'status',
                InvoiceStatus::Paid
            )
            ->with('items')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Merge Helpers
    |--------------------------------------------------------------------------
    */

    protected function mergeSuccessfulTransactions(
        Collection $localTransactions,
        Collection $stripeTransactions
    ): Collection {
        $merged = $localTransactions->mapWithKeys(
            function ($transaction) {
                return [
                    $this->successfulTransactionKey(
                        $transaction
                    ) => $transaction,
                ];
            }
        );

        foreach ($stripeTransactions as $transaction) {
            $merged[
                $this->successfulTransactionKey(
                    $transaction
                )
            ] = $transaction;
        }

        return $merged->values();
    }

    protected function successfulTransactionKey(
        object $transaction
    ): string {
        if (
            ! empty(
                $transaction->stripe_payment_intent_id
            )
        ) {
            return (string)
                $transaction->stripe_payment_intent_id;
        }

        if (
            ! empty(
                $transaction->invoice_id
            )
        ) {
            return (string)
                $transaction->invoice_id;
        }

        $amount = (string) (
            $transaction->amount ?? 0
        );

        $customerId = (string) (
            $transaction->customer_id
                ?? 'unknown'
        );

        $timestamp = (string) (
            $transaction->created_at?->timestamp
                ?? now()->timestamp
        );

        return $customerId
            . '-'
            . $amount
            . '-'
            . $timestamp;
    }

    /*
    |--------------------------------------------------------------------------
    | Customer Revenue
    |--------------------------------------------------------------------------
    */

    protected function customerRevenue(
        Collection $successfulTransactions,
        Collection $paidInvoices
    ): Collection {
        if ($successfulTransactions->isEmpty()) {
            return collect();
        }

        return $successfulTransactions
            ->groupBy('customer_id')
            ->map(
                function (
                    $transactions,
                    $customerId
                ) {
                    $customer =
                        Customer::query()
                            ->find($customerId);

                    return [
                        'name' =>
                            $customer?->name
                            ?: $customer?->email
                            ?: 'Unknown',

                        'amount' =>
                            $transactions->sum(
                                'amount'
                            ),
                    ];
                }
            )
            ->sortByDesc('amount')
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Monthly Revenue
    |--------------------------------------------------------------------------
    */

    protected function monthlyRevenue(
        Collection $successfulTransactions,
        string $filter = 'max'
    ): Collection {
        $startedAt = microtime(true);

        $filter = $this->normalizeFilter(
            $filter
        );

        Log::debug(
            'TeamDashboardService: monthlyRevenue START',
            [
                'filter' => $filter,
                'transaction_count' =>
                    $successfulTransactions->count(),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Month To Date
        |--------------------------------------------------------------------------
        */

        if ($filter === 'mtd') {
            $result = collect([
                [
                    'month' =>
                        now()->format('M Y'),

                    'value' =>
                        (int) $successfulTransactions
                            ->sum('amount'),
                ],
            ]);

            $this->logComplete(
                'monthlyRevenue MTD',
                $startedAt,
                [
                    'result_count' => $result->count(),
                    'value' =>
                        $result->first()['value'] ?? 0,
                ]
            );

            return $result;
        }

        /**
 * |--------------------------------------------------------------------------
 * | Year To Date
 * |--------------------------------------------------------------------------
 */
if ($filter === 'ytd') {
    $start = now()->startOfYear();
    $now = now();

    $months = collect();

    Log::debug('TeamDashboardService: monthlyRevenue YTD grouping', [
        'start' => $start->toDateTimeString(),
        'now' => $now->toDateTimeString(),
        'transaction_count' => $successfulTransactions->count(),
    ]);

    $stepStartedAt = microtime(true);

    // Group successful transactions by year-month.
    $grouped = $successfulTransactions->groupBy(function ($transaction) {
        return $transaction->created_at
            ?->format('Y-m');
    });

    $this->logComplete(
        'monthlyRevenue YTD groupBy',
        $stepStartedAt,
        [
            'group_count' => $grouped->count(),
            'grouped_keys' => $grouped->keys()->values()->all(),
        ]
    );

    /*
     * Build one entry for every month from January
     * through the current month.
     *
     * We create a new Carbon instance on every iteration
     * so that the date cannot be accidentally reused/mutated.
     */
    $current = $start->copy()->startOfMonth();

    $iteration = 0;

    while ($current->lte($now)) {
        $iteration++;

        if ($iteration > 24) {
            Log::critical(
                'TeamDashboardService: monthlyRevenue YTD safety break triggered',
                [
                    'current' => $current->toDateTimeString(),
                    'now' => $now->toDateTimeString(),
                    'iterations' => $iteration,
                ]
            );

            break;
        }

        $monthKey = $current->format('Y-m');

        Log::debug(
            'TeamDashboardService: monthlyRevenue YTD month',
            [
                'iteration' => $iteration,
                'month_key' => $monthKey,
                'month_label' => $current->format('M'),
            ]
        );

        $amount = $grouped
            ->get($monthKey, collect())
            ->sum('amount');

        $months->push([
            // Display label: Jan, Feb, Mar, etc.
            'month' => $current->format('M'),

            // Unique month identifier: 2026-01, 2026-02, etc.
            'month_key' => $monthKey,

            // Total revenue for that month.
            'value' => (int) $amount,
        ]);

        // IMPORTANT:
        // Use a fresh Carbon copy for the next iteration.
        $current = $current->copy()->addMonth()->startOfMonth();
    }

    $this->logComplete(
        'monthlyRevenue YTD',
        $startedAt,
        [
            'result_count' => $months->count(),
            'months' => $months->all(),
        ]
    );

    return $months;
}

        /*
        |--------------------------------------------------------------------------
        | All Time
        |--------------------------------------------------------------------------
        */

        $monthlySeries = collect(
            range(0, 11)
        )->map(
            function ($offset) {
                $month = now()
                    ->copy()
                    ->startOfMonth()
                    ->subMonths(
                        11 - $offset
                    );

                return [
                    'label' =>
                        $month->format('M'),

                    'amount' => 0,

                    'month' =>
                        $month->format('Y-m'),
                ];
            }
        );

        if (
            $successfulTransactions->isEmpty()
        ) {
            $result = $monthlySeries->map(
                fn ($entry) => [
                    'month' =>
                        $entry['label'],

                    'value' => 0,
                ]
            );

            $this->logComplete(
                'monthlyRevenue MAX empty',
                $startedAt,
                [
                    'result_count' => $result->count(),
                ]
            );

            return $result;
        }

        $stepStartedAt = microtime(true);

        $grouped =
            $successfulTransactions
                ->groupBy(
                    function ($transaction) {
                        return $transaction
                            ->created_at
                            ?->format('Y-m')
                            ?? now()->format('Y-m');
                    }
                );

        $this->logComplete(
            'monthlyRevenue MAX groupBy',
            $stepStartedAt,
            [
                'group_count' =>
                    $grouped->count(),
            ]
        );

        $result = $monthlySeries->map(
            function ($entry) use ($grouped) {
                $key = $entry['month'];

                $amount =
                    $grouped->has($key)
                        ? $grouped
                            ->get($key)
                            ->sum('amount')
                        : 0;

                return [
                    'month' =>
                        $entry['label'],

                    'value' =>
                        (int) $amount,
                ];
            }
        );

        $this->logComplete(
            'monthlyRevenue MAX',
            $startedAt,
            [
                'result_count' => $result->count(),
            ]
        );

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | Revenue By Payment Type
    |--------------------------------------------------------------------------
    */

    protected function revenueForPaymentType(
        Collection $successfulTransactions,
        Collection $paidInvoices,
        PaymentType $paymentType,
        bool $invert = false
    ): int {
        if ($successfulTransactions->isEmpty()) {
            return 0;
        }

        return (int) $successfulTransactions
            ->filter(
                fn ($transaction) =>
                    $this->resolveTransactionPaymentType(
                        $transaction
                    ) === $paymentType
            )
            ->sum('amount');
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Payment Type
    |--------------------------------------------------------------------------
    */

    protected function resolveTransactionPaymentType(
        object $transaction
    ): PaymentType {
        if (! empty($transaction->invoice)) {
            $invoiceType =
                $transaction->invoice->payment_type
                    ?? null;

            if (
                $invoiceType instanceof PaymentType
            ) {
                return $invoiceType;
            }

            if (
                is_string($invoiceType)
                || is_int($invoiceType)
            ) {
                return PaymentType::tryFrom(
                    (string) $invoiceType
                ) ?? PaymentType::OneTime;
            }
        }

        if (! empty($transaction->invoice_id)) {
            $invoice = Invoice::query()
                ->find($transaction->invoice_id);

            if ($invoice) {
                $paymentType =
                    $invoice->payment_type;

                if (
                    $paymentType instanceof PaymentType
                ) {
                    return $paymentType;
                }

                if (
                    is_string($paymentType)
                    || is_int($paymentType)
                ) {
                    return PaymentType::tryFrom(
                        (string) $paymentType
                    ) ?? PaymentType::OneTime;
                }
            }
        }

        return PaymentType::OneTime;
    }

    /*
    |--------------------------------------------------------------------------
    | Stripe Revenue
    |--------------------------------------------------------------------------
    */

    protected function stripeRevenueForTeam(
        Team $team
    ): array {
        $startedAt = microtime(true);

        $this->logStart(
            'stripeRevenueForTeam',
            [
                'team_id' => $team->id,
                'team_slug' => $team->slug,
            ]
        );

        try {
            $transactions = collect();

            $totalRevenue = 0;

            /*
            |--------------------------------------------------------------------------
            | Team Customer Stripe IDs
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            Log::debug(
                'TeamDashboardService: Stripe customer IDs query START',
                [
                    'team_id' => $team->id,
                ]
            );

            $customerStripeIds = $team
                ->tickets()
                ->with('customer')
                ->get()
                ->pluck('customer.stripe_id')
                ->filter()
                ->unique()
                ->values();

            $this->logComplete(
                'Stripe customer IDs query',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'customer_count' =>
                        $customerStripeIds->count(),
                    'stripe_customer_ids' =>
                        $customerStripeIds->all(),
                ]
            );

            if ($customerStripeIds->isEmpty()) {
                Log::info(
                    'TeamDashboardService: no Stripe customers found',
                    [
                        'team_id' => $team->id,
                    ]
                );

                return [
                    'transactions' =>
                        $transactions,

                    'revenue' => 0,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Team Invoice IDs
            |--------------------------------------------------------------------------
            */

            $stepStartedAt = microtime(true);

            $teamInvoiceIds = $this
                ->teamInvoiceIds($team)
                ->all();

            $teamInvoiceIdLookup = collect(
                $teamInvoiceIds
            )
                ->map(
                    fn ($id) => (string) $id
                )
                ->flip();

            $this->logComplete(
                'Stripe team invoice IDs',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'invoice_count' =>
                        count($teamInvoiceIds),
                ]
            );

            $seenInvoiceKeys = [];

            /*
            |--------------------------------------------------------------------------
            | Search Stripe Payment Intents
            |--------------------------------------------------------------------------
            */

            foreach (
                $customerStripeIds as $customerStripeId
            ) {
                $customerStartedAt = microtime(true);

                Log::info(
                    'TeamDashboardService: Stripe customer SEARCH START',
                    [
                        'team_id' => $team->id,
                        'stripe_customer_id' =>
                            $customerStripeId,
                    ]
                );

                $searchStartedAt = microtime(true);

                $results = $this->stripe
                    ->paymentIntents
                    ->search([
                        'query' =>
                            "customer:'{$customerStripeId}' "
                            . "AND status:'succeeded'",

                        'limit' => 100,
                    ]);

                $this->logComplete(
                    'Stripe customer SEARCH',
                    $searchStartedAt,
                    [
                        'team_id' => $team->id,
                        'stripe_customer_id' =>
                            $customerStripeId,

                        'payment_intent_count' =>
                            count(
                                $results->data ?? []
                            ),
                    ]
                );

                $paymentIntentIteration = 0;

                foreach (
                    $results->data ?? [] as $paymentIntent
                ) {
                    $paymentIntentIteration++;

                    $paymentIntentStartedAt =
                        microtime(true);

                    Log::debug(
                        'TeamDashboardService: Stripe PaymentIntent START',
                        [
                            'team_id' => $team->id,
                            'iteration' =>
                                $paymentIntentIteration,

                            'payment_intent_id' =>
                                $paymentIntent->id
                                ?? null,

                            'amount' =>
                                $paymentIntent->amount
                                ?? null,

                            'created' =>
                                $paymentIntent->created
                                ?? null,
                        ]
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Metadata
                    |--------------------------------------------------------------------------
                    */

                    $stepStartedAt = microtime(true);

                    $metadata =
                        $paymentIntent->metadata
                        ?? (object) [];

                    $invoiceId =
                        $metadata->invoice_id
                        ?? null;

                    $this->logComplete(
                        'PaymentIntent metadata',
                        $stepStartedAt,
                        [
                            'team_id' => $team->id,
                            'payment_intent_id' =>
                                $paymentIntent->id
                                ?? null,

                            'invoice_id' =>
                                $invoiceId,
                        ]
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Invoice Lookup
                    |--------------------------------------------------------------------------
                    */

                    $invoice = null;

                    if ($invoiceId) {
                        $stepStartedAt = microtime(true);

                        Log::debug(
                            'TeamDashboardService: Invoice lookup START',
                            [
                                'team_id' => $team->id,
                                'invoice_id' =>
                                    $invoiceId,
                                'payment_intent_id' =>
                                    $paymentIntent->id
                                    ?? null,
                            ]
                        );

                        $invoice = Invoice::query()
                            ->find($invoiceId);

                        $this->logComplete(
                            'Invoice lookup',
                            $stepStartedAt,
                            [
                                'team_id' => $team->id,
                                'invoice_id' =>
                                    $invoiceId,
                                'found' => $invoice !== null,
                            ]
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Verify Invoice Belongs To Team
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $invoice
                        && ! $teamInvoiceIdLookup->has(
                            (string) $invoice->id
                        )
                    ) {
                        Log::debug(
                            'TeamDashboardService: PaymentIntent skipped because invoice belongs to another team',
                            [
                                'team_id' => $team->id,
                                'invoice_id' =>
                                    $invoice->id,

                                'payment_intent_id' =>
                                    $paymentIntent->id
                                    ?? null,
                            ]
                        );

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Deduplicate Payment Intents
                    |--------------------------------------------------------------------------
                    */

                    $dedupeKey = $invoiceId
                        ? 'invoice:'
                            . (string) $invoiceId
                        : 'intent:'
                            . (
                                string
                            ) (
                                $paymentIntent->id
                                ?? 'unknown'
                            );

                    if (
                        isset(
                            $seenInvoiceKeys[$dedupeKey]
                        )
                    ) {
                        Log::debug(
                            'TeamDashboardService: PaymentIntent duplicate skipped',
                            [
                                'team_id' => $team->id,
                                'dedupe_key' =>
                                    $dedupeKey,
                                'payment_intent_id' =>
                                    $paymentIntent->id
                                    ?? null,
                            ]
                        );

                        continue;
                    }

                    $seenInvoiceKeys[$dedupeKey] = true;

                    /*
                    |--------------------------------------------------------------------------
                    | Resolve Invoice / Customer
                    |--------------------------------------------------------------------------
                    */

                    $customerId =
                        $paymentIntent->customer
                        ?? $customerStripeId;

                    Log::debug(
                        'TeamDashboardService: resolving transaction invoice/customer',
                        [
                            'team_id' => $team->id,
                            'payment_intent_id' =>
                                $paymentIntent->id
                                ?? null,

                            'customer_id' =>
                                $customerId,

                            'invoice_id' =>
                                $invoiceId,
                        ]
                    );

                    $stepStartedAt = microtime(true);

                    $invoiceForTransaction =
                        $invoice
                        ?? $this->teamInvoicesForCustomer(
                            $team,
                            $customerId
                        )->first();

                    $this->logComplete(
                        'resolve transaction invoice/customer',
                        $stepStartedAt,
                        [
                            'team_id' => $team->id,
                            'payment_intent_id' =>
                                $paymentIntent->id
                                ?? null,

                            'invoice_id' =>
                                $invoiceForTransaction?->id,

                            'customer_id' =>
                                $invoiceForTransaction?->customer_id,
                        ]
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Stripe Payment Intent Details
                    |--------------------------------------------------------------------------
                    */

                    $stepStartedAt = microtime(true);

                    Log::info(
                        'TeamDashboardService: Stripe PaymentIntent RETRIEVE START',
                        [
                            'team_id' => $team->id,
                            'payment_intent_id' =>
                                $paymentIntent->id
                                ?? null,
                        ]
                    );

                    $paymentIntentDetails =
                        $this->stripe
                            ->paymentIntents
                            ->retrieve(
                                $paymentIntent->id,
                                [
                                    'expand' => [
                                        'charges.data.balance_transaction',
                                    ],
                                ]
                            );

                    $this->logComplete(
                        'Stripe PaymentIntent RETRIEVE',
                        $stepStartedAt,
                        [
                            'team_id' => $team->id,
                            'payment_intent_id' =>
                                $paymentIntent->id
                                ?? null,
                        ]
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Resolve Charge / Balance Transaction
                    |--------------------------------------------------------------------------
                    */

                    $stepStartedAt = microtime(true);

                    $charges =
                        $paymentIntentDetails
                            ->charges
                            ->data
                            ?? [];

                    $balanceTransaction =
                        $charges[0]
                            ->balance_transaction
                            ?? null;

                    $this->logComplete(
                        'Stripe charge/balance transaction lookup',
                        $stepStartedAt,
                        [
                            'team_id' => $team->id,
                            'payment_intent_id' =>
                                $paymentIntent->id
                                ?? null,

                            'charge_count' =>
                                count($charges),

                            'has_balance_transaction' =>
                                $balanceTransaction !== null,
                        ]
                    );

                    if (
                        ! $balanceTransaction
                        && ! empty(
                            $paymentIntent->latest_charge
                        )
                    ) {
                        $stepStartedAt = microtime(true);

                        Log::info(
                            'TeamDashboardService: Stripe Charge RETRIEVE START',
                            [
                                'team_id' => $team->id,
                                'payment_intent_id' =>
                                    $paymentIntent->id
                                    ?? null,

                                'charge_id' =>
                                    $paymentIntent->latest_charge,
                            ]
                        );

                        $charge =
                            $this->stripe
                                ->charges
                                ->retrieve(
                                    $paymentIntent
                                        ->latest_charge,
                                    [
                                        'expand' => [
                                            'balance_transaction',
                                        ],
                                    ]
                                );

                        $balanceTransaction =
                            $charge
                                ->balance_transaction
                                ?? null;

                        $this->logComplete(
                            'Stripe Charge RETRIEVE',
                            $stepStartedAt,
                            [
                                'team_id' => $team->id,
                                'payment_intent_id' =>
                                    $paymentIntent->id
                                    ?? null,

                                'charge_id' =>
                                    $paymentIntent->latest_charge,

                                'has_balance_transaction' =>
                                    $balanceTransaction !== null,
                            ]
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Stripe Processing Fee
                    |--------------------------------------------------------------------------
                    */

                    $processingFee =
                        isset(
                            $balanceTransaction->fee
                        )
                            ? -abs(
                                (int)
                                    $balanceTransaction->fee
                            )
                            : 0;

                    /*
                    |--------------------------------------------------------------------------
                    | Stripe Net
                    |--------------------------------------------------------------------------
                    */

                    $netAmount =
                        isset(
                            $balanceTransaction->net
                        )
                            ? (int)
                                $balanceTransaction->net
                            : (
                                (int) (
                                    $paymentIntent
                                        ->amount_received
                                        ?? $paymentIntent
                                            ->amount
                                        ?? 0
                                )
                                + $processingFee
                            );

                    /*
                    |--------------------------------------------------------------------------
                    | Payment Intent Created Date
                    |--------------------------------------------------------------------------
                    */

                    $createdTimestamp =
                        (int) (
                            $paymentIntent->created
                            ?? now()->timestamp
                        );

                    $createdAt =
                        Carbon::createFromTimestamp(
                            $createdTimestamp,
                            config('app.timezone')
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Build Transaction Object
                    |--------------------------------------------------------------------------
                    */

                    $transaction = (object) [
                        'customer_id' =>
                            $invoiceForTransaction
                                ?->customer_id,

                        'invoice_id' =>
                            $invoiceForTransaction
                                ?->id
                            ?? $invoiceId,

                        'stripe_payment_intent_id' =>
                            $paymentIntent->id
                            ?? null,

                        'amount' =>
                            (int) (
                                $paymentIntent->amount
                                ?? 0
                            ),

                        'processing_fee' =>
                            $processingFee,

                        'net_amount' =>
                            $netAmount,

                        'created_at' =>
                            $createdAt,

                        'status' =>
                            TransactionStatus::Succeeded,

                        'invoice' => (object) [
                            'payment_type' =>
                                $invoiceForTransaction
                                    ?->payment_type
                                ?? PaymentType::OneTime,
                        ],
                    ];

                    $transactions->push(
                        $transaction
                    );

                    $totalRevenue +=
                        (int) (
                            $paymentIntent->amount
                            ?? 0
                        );

                    $this->logComplete(
                        'Stripe PaymentIntent',
                        $paymentIntentStartedAt,
                        [
                            'team_id' => $team->id,
                            'payment_intent_id' =>
                                $paymentIntent->id
                                ?? null,

                            'amount' =>
                                $transaction->amount,

                            'processing_fee' =>
                                $processingFee,

                            'net_amount' =>
                                $netAmount,

                            'created_at' =>
                                $createdAt
                                    ->toDateTimeString(),

                            'invoice_id' =>
                                $transaction->invoice_id,

                            'customer_id' =>
                                $transaction->customer_id,
                        ]
                    );
                }

                $this->logComplete(
                    'Stripe customer processing',
                    $customerStartedAt,
                    [
                        'team_id' => $team->id,
                        'stripe_customer_id' =>
                            $customerStripeId,

                        'payment_intent_count' =>
                            $paymentIntentIteration,
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Final Stripe Result
            |--------------------------------------------------------------------------
            */

            Log::info(
                'TeamDashboardService: stripeRevenueForTeam END',
                [
                    'team_id' => $team->id,
                    'transaction_count' =>
                        $transactions->count(),

                    'total_revenue' =>
                        $totalRevenue,

                    'total_duration_ms' =>
                        round(
                            (
                                microtime(true)
                                - $startedAt
                            ) * 1000,
                            2
                        ),
                ]
            );

            return [
                'transactions' =>
                    $transactions,

                'revenue' =>
                    $totalRevenue,
            ];
        } catch (Throwable $e) {
            Log::error(
                'TeamDashboardService: stripeRevenueForTeam FAILED',
                [
                    'team_id' => $team->id ?? null,
                    'team_slug' => $team->slug ?? null,

                    'exception_class' =>
                        get_class($e),

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),

                    'duration_ms' =>
                        round(
                            (
                                microtime(true)
                                - $startedAt
                            ) * 1000,
                            2
                        ),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Team Invoices For Stripe Customer
    |--------------------------------------------------------------------------
    */

    protected function teamInvoicesForCustomer(
        Team $team,
        string $customerId
    ): Collection {
        $startedAt = microtime(true);

        Log::debug(
            'TeamDashboardService: teamInvoicesForCustomer START',
            [
                'team_id' => $team->id,
                'customer_id' => $customerId,
            ]
        );

        $tickets = $team
            ->tickets()
            ->pluck('id');

        Log::debug(
            'TeamDashboardService: teamInvoicesForCustomer ticket IDs loaded',
            [
                'team_id' => $team->id,
                'customer_id' => $customerId,
                'ticket_count' => $tickets->count(),
            ]
        );

        $result = Invoice::query()
            ->whereIn(
                'ticket_id',
                $tickets
            )
            ->where(
                function ($query) use (
                    $customerId
                ) {
                    $query
                        ->where(
                            'customer_id',
                            $customerId
                        )
                        ->orWhereHas(
                            'customer',
                            fn ($customerQuery) =>
                                $customerQuery->where(
                                    'stripe_id',
                                    $customerId
                                )
                        );
                }
            )
            ->get();

        $this->logComplete(
            'teamInvoicesForCustomer',
            $startedAt,
            [
                'team_id' => $team->id,
                'customer_id' => $customerId,
                'invoice_count' => $result->count(),
            ]
        );

        return $result;
    }
}