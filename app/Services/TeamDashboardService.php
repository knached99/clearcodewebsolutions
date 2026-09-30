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
use Throwable;

class TeamDashboardService
{
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
                $filter,
                'created_at'
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
                $filter,
                'updated_at'
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
            | Collected Invoices
            |--------------------------------------------------------------------------
            |
            | Includes partial and fully paid invoices.
            |
            */

            $stepStartedAt = microtime(true);

            $allCollectedInvoices =
                $this->collectedInvoices($team);

            $collectedInvoices =
                $this->applyDateFilter(
                    $allCollectedInvoices,
                    $filter,
                    'updated_at'
                );

            $this->logComplete(
                'collectedInvoices',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'count' => $collectedInvoices->count(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Successful Stripe Transactions
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | stripeRevenueForTeam() is retained for compatibility with the
            | existing service, but it no longer calls Stripe.
            |
            | Transactions are synchronized from Stripe separately and the
            | dashboard reads the local transactions table.
            |
            */

            $stepStartedAt = microtime(true);

            Log::info(
                'TeamDashboardService: transaction ledger about to run',
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
            | Successful Transactions
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
            |
            | A successful payment belongs to the date it was actually paid,
            | so paid_at is used instead of created_at when available.
            |
            */

            $stepStartedAt = microtime(true);

            $successfulTransactions = $this->applyDateFilter(
                $allSuccessfulTransactions,
                $filter,
                'paid_at'
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
            |
            | Gross revenue is ACTUAL SUCCESSFUL MONEY COLLECTED.
            |
            | It is NOT Invoice::total_amount.
            |
            */

            $stepStartedAt = microtime(true);

            $grossProfit = (int) $successfulTransactions
                ->sum('amount');

            $this->logComplete(
                'grossProfit calculation',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'gross_profit' => $grossProfit,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Stripe Processing Fees
            |--------------------------------------------------------------------------
            |
            | processing_fee is stored as a POSITIVE amount.
            |
            */

            $stepStartedAt = microtime(true);

            $processingFees = (int) $successfulTransactions
                ->sum(function ($transaction): int {
                    return max(
                        (int) ($transaction->processing_fee ?? 0),
                        0
                    );
                });

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
            |
            | Prefer the stored transaction net amount.
            |
            | For old rows where net_amount may be zero/unavailable,
            | calculate:
            |
            |     amount - processing_fee
            |
            */

            $stepStartedAt = microtime(true);

            $netProfit = (int) $successfulTransactions
                ->sum(
                    function ($transaction): int {
                        $amount = max(
                            (int) ($transaction->amount ?? 0),
                            0
                        );

                        $processingFee = max(
                            (int) ($transaction->processing_fee ?? 0),
                            0
                        );

                        $storedNet = $transaction->net_amount ?? null;

                        if (
                            $storedNet !== null
                            && (int) $storedNet > 0
                        ) {
                            return (int) $storedNet;
                        }

                        return max(
                            $amount - $processingFee,
                            0
                        );
                    }
                );

            $this->logComplete(
                'netProfit calculation',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'net_profit' => $netProfit,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Profit Distribution
            |--------------------------------------------------------------------------
            |
            | Calculate this from the actual successful transaction data
            | in the selected reporting period.
            |
            | This avoids using Invoice::total_paid for a historical period,
            | which would incorrectly include payments outside the filter.
            |
            */

            $stepStartedAt = microtime(true);

            $distribution =
                $this->calculateProfitDistribution(
                    $successfulTransactions
                );

            $pabloCommission =
                $distribution['pabloCommission'];

            $teamShareAfterPablo =
                $distribution['teamShareAfterPablo'];

            $ownerShare =
                $distribution['ownerShare'];

            $partnerShare =
                $distribution['partnerShare'];

            /*
             * Backwards-compatible aliases.
             */
            $khaledShare = $ownerShare;
            $karamShare = $partnerShare;

            /*
            |--------------------------------------------------------------------------
            | Pablo Rate
            |--------------------------------------------------------------------------
            */

            $pabloRate = $grossProfit > 0
                ? $pabloCommission / $grossProfit
                : 0;

            $this->logComplete(
                'profit distribution calculation',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'invoice_count' =>
                        $successfulTransactions
                            ->pluck('invoice_id')
                            ->filter()
                            ->unique()
                            ->count(),
                    'pablo_commission' =>
                        $pabloCommission,
                    'pablo_rate' =>
                        $pabloRate,
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
                ->sum(
                    fn ($transaction): int =>
                        max(
                            (int) (
                                $transaction->amount ?? 0
                            ),
                            0
                        )
                );

            /*
            |--------------------------------------------------------------------------
            | Current MRR / ARR
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | MRR/ARR are recurring BILLING metrics, not cash-collection
            | metrics.
            |
            | We therefore use recurring invoice items here.
            |
            | The transaction ledger may be used to determine the actual
            | Stripe fee attached to a recurring invoice.
            |
            */

            $stepStartedAt = microtime(true);

            $allSubscriptionInvoices =
                $this->subscriptionInvoicesForMrr($team);

            $this->logComplete(
                'subscriptionInvoices',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'count' => $allSubscriptionInvoices->count(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | MRR
            |--------------------------------------------------------------------------
            */

            $mrr = 0;

            foreach (
                $allSubscriptionInvoices
                as $invoice
            ) {
                $recurringAmount =
                    max(
                        0,
                        (int) $invoice
                            ->monthlyRecurringAmountInCents()
                    );

                if ($recurringAmount <= 0) {
                    continue;
                }

                $grossInvoice =
                    max(
                        0,
                        (int) $invoice->total_amount
                    );

                if ($grossInvoice <= 0) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Actual Stripe fee where available.
                |--------------------------------------------------------------------------
                */

                $actualStripeFee =
                    (int) $invoice
                        ->stripeProcessingFeeInCents();

                if ($actualStripeFee > 0) {
                    $stripeFee = $actualStripeFee;
                } else {
                    /*
                    |--------------------------------------------------------------------------
                    | Fallback estimate for recurring-value reporting only.
                    |--------------------------------------------------------------------------
                    */

                    $stripeFee =
                        (int) $invoice
                            ->estimatedMonthlyStripeFeeInCents();
                }

                /*
                |--------------------------------------------------------------------------
                | Allocate invoice fee proportionally to recurring revenue.
                |--------------------------------------------------------------------------
                */

                $recurringStripeFee =
                    (int) round(
                        $stripeFee
                        * (
                            $recurringAmount
                            / $grossInvoice
                        )
                    );

                /*
                |--------------------------------------------------------------------------
                | Existing dashboard behavior:
                |
                | MRR is shown net of estimated/actual Stripe fees.
                |--------------------------------------------------------------------------
                */

                $mrr += max(
                    $recurringAmount
                        - $recurringStripeFee,
                    0
                );
            }

            $mrr = (int) $mrr;

            /*
            |--------------------------------------------------------------------------
            | ARR
            |--------------------------------------------------------------------------
            */

            $arr = $mrr * 12;

            $this->logComplete(
                'MRR / ARR calculation',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'subscription_invoice_count' =>
                        $allSubscriptionInvoices->count(),
                    'mrr' =>
                        $mrr,
                    'arr' =>
                        $arr,
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
            |
            | This is now based on ACTUAL transaction cash.
            |
            | Mixed invoices are allocated based on their recurring
            | line-item portion.
            |
            */

            $stepStartedAt = microtime(true);

            $subscriptionRevenue =
                $this->revenueForPaymentType(
                    $successfulTransactions,
                    $paidInvoices,
                    PaymentType::Subscription
                );

            $oneTimeRevenue =
                $this->revenueForPaymentType(
                    $successfulTransactions,
                    $paidInvoices,
                    PaymentType::OneTime
                );

            /*
            |--------------------------------------------------------------------------
            | Safety reconciliation
            |--------------------------------------------------------------------------
            |
            | Subscription + One-Time should equal gross revenue.
            |
            */

            $revenueTypeTotal =
                $subscriptionRevenue
                + $oneTimeRevenue;

            if (
                $revenueTypeTotal !== $grossProfit
            ) {
                Log::warning(
                    'TeamDashboardService: revenue type totals did not exactly reconcile',
                    [
                        'team_id' =>
                            $team->id,

                        'gross_profit' =>
                            $grossProfit,

                        'subscription_revenue' =>
                            $subscriptionRevenue,

                        'one_time_revenue' =>
                            $oneTimeRevenue,

                        'revenue_type_total' =>
                            $revenueTypeTotal,

                        'difference' =>
                            $grossProfit
                            - $revenueTypeTotal,
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | Make one-time revenue the reconciliation bucket.
                |--------------------------------------------------------------------------
                */

                $oneTimeRevenue = max(
                    $grossProfit
                    - $subscriptionRevenue,
                    0
                );
            }

            $this->logComplete(
                'subscriptionRevenue / oneTimeRevenue',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'gross_profit' =>
                        $grossProfit,
                    'subscription_revenue' =>
                        $subscriptionRevenue,
                    'one_time_revenue' =>
                        $oneTimeRevenue,
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

            /*
            |--------------------------------------------------------------------------
            | Total Invoices
            |--------------------------------------------------------------------------
            */

            $totalInvoicesQuery = Invoice::query()
                ->whereIn(
                    'ticket_id',
                    $invoiceTicketIds
                );

            /*
            |--------------------------------------------------------------------------
            | Paid Invoices
            |--------------------------------------------------------------------------
            */

            $paidInvoicesQuery = Invoice::query()
                ->whereIn(
                    'ticket_id',
                    $invoiceTicketIds
                )
                ->where(
                    'status',
                    InvoiceStatus::Paid->value
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

            /*
            |--------------------------------------------------------------------------
            | Total Invoice Count
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Paid Invoice Count
            |--------------------------------------------------------------------------
            */

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
                    'total_invoices' =>
                        $totalInvoices,
                    'paid_invoices' =>
                        $paidInvoicesCount,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Active Subscriptions
            |--------------------------------------------------------------------------
            |
            | Only fully paid subscription-producing invoices are counted
            | here, matching the previous dashboard behavior.
            |
            | Multiple invoices belonging to the same Stripe subscription
            | are deduplicated by stripe_subscription_id.
            |
            */

            $stepStartedAt = microtime(true);

            $activeSubscriptionInvoices =
                $allPaidInvoices
                    ->filter(
                        function (Invoice $invoice): bool {
                            return (int) $invoice
                                ->monthlyRecurringAmountInCents() > 0;
                        }
                    );

            $activeSubscriptionKeys =
                $activeSubscriptionInvoices
                    ->map(
                        fn (Invoice $invoice): string =>
                            $this->subscriptionKey($invoice)
                    )
                    ->unique()
                    ->values();

            $activeSubscriptions =
                $activeSubscriptionKeys->count();

            $this->logComplete(
                'activeSubscriptions',
                $stepStartedAt,
                [
                    'team_id' => $team->id,
                    'recurring_invoice_count' =>
                        $activeSubscriptionInvoices->count(),
                    'count' =>
                        $activeSubscriptions,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Average Invoice
            |--------------------------------------------------------------------------
            */

            $averageInvoiceValue = $paidInvoicesCount > 0
                ? (int) round(
                    $grossProfit
                    / $paidInvoicesCount
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

                    'gross_profit' =>
                        $grossProfit,

                    'processing_fees' =>
                        $processingFees,

                    'net_profit' =>
                        $netProfit,

                    'pablo_rate' =>
                        $pabloRate,

                    'pablo_commission' =>
                        $pabloCommission,

                    'team_share_after_pablo' =>
                        $teamShareAfterPablo,

                    'khaled_share' =>
                        $khaledShare,

                    'karam_share' =>
                        $karamShare,

                    'losses' =>
                        $losses,

                    'mrr' =>
                        $mrr,

                    'arr' =>
                        $arr,

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

                    'collected_invoice_count' =>
                        $collectedInvoices->count(),

                    'total_duration_ms' => round(
                        (
                            microtime(true)
                            - $startedAt
                        ) * 1000,
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
                'team' =>
                    $team,

                'grossProfit' =>
                    $grossProfit,

                'netProfit' =>
                    $netProfit,

                'processingFees' =>
                    $processingFees,

                'pabloRate' =>
                    $pabloRate,

                'pabloCommission' =>
                    $pabloCommission,

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

                'losses' =>
                    $losses,

                'mrr' =>
                    $mrr,

                'arr' =>
                    $arr,

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

                /*
                 * Backwards-compatible frontend alias.
                 */
                'averageInvoice' =>
                    $averageInvoiceValue,

                /*
                 * Additional useful value while preserving the
                 * original response shape.
                 */
                'collectedInvoices' =>
                    $collectedInvoices,
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
    |
    | This is now the local source of truth for actual collected revenue.
    |
    | Stripe synchronization should populate the transactions table.
    |
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
                TransactionStatus::Succeeded->value
            )
            ->whereNotNull(
                'stripe_payment_intent_id'
            )
            ->where(
                'stripe_payment_intent_id',
                '!=',
                ''
            )
            ->with([
                'customer',
                'invoice.items',
            ])
            ->orderBy(
                'paid_at'
            )
            ->orderBy(
                'created_at'
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
                TransactionStatus::Failed->value
            )
            ->with([
                'customer',
                'invoice',
            ])
            ->orderBy(
                'created_at'
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
                InvoiceStatus::Paid->value
            )
            ->with([
                'customer',
                'items',
                'transactions',
            ])
            ->orderBy(
                'updated_at'
            )
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Collected Invoices
    |--------------------------------------------------------------------------
    |
    | Includes both:
    |
    |   - partial payments
    |   - fully paid invoices
    |
    */

    protected function collectedInvoices(
        Team $team
    ): EloquentCollection {
        return Invoice::query()
            ->whereIn(
                'ticket_id',
                $team->tickets()->pluck('id')
            )
            ->where(
                'total_paid',
                '>',
                0
            )
            ->whereNotIn(
                'status',
                [
                    InvoiceStatus::Failed->value,
                    InvoiceStatus::Void->value,
                ]
            )
            ->with([
                'customer',
                'items',
                'transactions',
            ])
            ->orderBy(
                'updated_at'
            )
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
                PaymentType::Subscription->value
            )
            ->where(
                'status',
                InvoiceStatus::Paid->value
            )
            ->with([
                'items',
                'transactions',
            ])
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Subscription Invoices For MRR
    |--------------------------------------------------------------------------
    |
    | A mixed invoice may be PaymentType::OneTime while still containing
    | recurring addons. Therefore MRR is determined from recurring items,
    | not payment_type alone.
    |
    | Multiple historical invoices for the same Stripe subscription are
    | reduced to the latest relevant invoice.
    |
    */

    protected function subscriptionInvoicesForMrr(
        Team $team
    ): Collection {
        $invoices = Invoice::query()
            ->whereIn(
                'ticket_id',
                $team->tickets()->pluck('id')
            )
            ->where(
                'status',
                InvoiceStatus::Paid->value
            )
            ->whereHas(
                'items',
                function ($query) {
                    $query
                        ->where(
                            'is_subscription',
                            true
                        )
                        ->where(
                            'amount',
                            '>',
                            0
                        );
                }
            )
            ->with([
                'items',
                'transactions',
            ])
            ->orderByDesc(
                'updated_at'
            )
            ->get();

        return $invoices
            ->groupBy(
                fn (Invoice $invoice): string =>
                    $this->subscriptionKey($invoice)
            )
            ->map(
                function (Collection $subscriptionInvoices) {
                    return $subscriptionInvoices
                        ->sortByDesc(
                            fn (Invoice $invoice) =>
                                $invoice->updated_at
                                ?? $invoice->created_at
                        )
                        ->first();
                }
            )
            ->filter()
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Subscription Key
    |--------------------------------------------------------------------------
    */

    protected function subscriptionKey(
        Invoice $invoice
    ): string {
        if (
            !empty(
                $invoice->stripe_subscription_id
            )
        ) {
            return 'stripe-subscription:'
                . trim(
                    (string) $invoice->stripe_subscription_id
                );
        }

        return 'invoice:'
            . (string) $invoice->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Merge Helpers
    |--------------------------------------------------------------------------
    |
    | Retained for compatibility with existing callers.
    |
    | The dashboard itself no longer merges live Stripe records because the
    | database transaction ledger is the reporting source.
    |
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
            !empty(
                $transaction->stripe_payment_intent_id
            )
        ) {
            return (string)
                $transaction->stripe_payment_intent_id;
        }

        if (
            !empty(
                $transaction->invoice_id
            )
        ) {
            return 'invoice:'
                . (string) $transaction->invoice_id
                . ':'
                . (string) ($transaction->id ?? '');
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
                    Collection $transactions,
                    $customerId
                ) {
                    /*
                    |--------------------------------------------------------------------------
                    | Prefer eager-loaded customer.
                    |--------------------------------------------------------------------------
                    */

                    $customer =
                        $transactions
                            ->first()
                            ?->customer;

                    /*
                    |--------------------------------------------------------------------------
                    | Fallback query.
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !$customer
                        && $customerId
                    ) {
                        $customer =
                            Customer::query()
                                ->find($customerId);
                    }

                    return [
                        'customer_id' =>
                            $customerId,

                        'name' =>
                            $customer?->name
                            ?: $customer?->email
                            ?: 'Unknown',

                        'email' =>
                            $customer?->email,

                        'amount' =>
                            (int) $transactions
                                ->sum('amount'),
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
        | Date Resolver
        |--------------------------------------------------------------------------
        |
        | Successful cash revenue is reported by paid_at.
        |
        */

        $transactionMonth = function (
            $transaction
        ): string {
            $date =
                $transaction->paid_at
                ?? $transaction->created_at;

            if (!$date) {
                return now()->format('Y-m');
            }

            if (!$date instanceof Carbon) {
                $date = Carbon::parse($date);
            }

            return $date->format('Y-m');
        };

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
                    'result_count' =>
                        $result->count(),

                    'value' =>
                        $result
                            ->first()['value']
                            ?? 0,
                ]
            );

            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | Year To Date
        |--------------------------------------------------------------------------
        */

        if ($filter === 'ytd') {
            $start =
                now()
                    ->copy()
                    ->startOfYear();

            $now =
                now();

            $months =
                collect();

            Log::debug(
                'TeamDashboardService: monthlyRevenue YTD grouping',
                [
                    'start' =>
                        $start->toDateTimeString(),

                    'now' =>
                        $now->toDateTimeString(),

                    'transaction_count' =>
                        $successfulTransactions->count(),
                ]
            );

            $stepStartedAt =
                microtime(true);

            $grouped =
                $successfulTransactions
                    ->groupBy(
                        $transactionMonth
                    );

            $this->logComplete(
                'monthlyRevenue YTD groupBy',
                $stepStartedAt,
                [
                    'group_count' =>
                        $grouped->count(),

                    'grouped_keys' =>
                        $grouped
                            ->keys()
                            ->values()
                            ->all(),
                ]
            );

            /*
             * Build one entry for every month from January
             * through the current month.
             */

            $current =
                $start
                    ->copy()
                    ->startOfMonth();

            $iteration = 0;

            while (
                $current->lte($now)
            ) {
                $iteration++;

                if ($iteration > 24) {
                    Log::critical(
                        'TeamDashboardService: monthlyRevenue YTD safety break triggered',
                        [
                            'current' =>
                                $current->toDateTimeString(),

                            'now' =>
                                $now->toDateTimeString(),

                            'iterations' =>
                                $iteration,
                        ]
                    );

                    break;
                }

                $monthKey =
                    $current->format('Y-m');

                Log::debug(
                    'TeamDashboardService: monthlyRevenue YTD month',
                    [
                        'iteration' =>
                            $iteration,

                        'month_key' =>
                            $monthKey,

                        'month_label' =>
                            $current->format('M'),
                    ]
                );

                $amount =
                    $grouped
                        ->get(
                            $monthKey,
                            collect()
                        )
                        ->sum('amount');

                $months->push([
                    'month' =>
                        $current->format('M'),

                    'month_key' =>
                        $monthKey,

                    'value' =>
                        (int) $amount,
                ]);

                $current =
                    $current
                        ->copy()
                        ->addMonth()
                        ->startOfMonth();
            }

            $this->logComplete(
                'monthlyRevenue YTD',
                $startedAt,
                [
                    'result_count' =>
                        $months->count(),

                    'months' =>
                        $months->all(),
                ]
            );

            return $months;
        }

        /*
        |--------------------------------------------------------------------------
        | All Time
        |--------------------------------------------------------------------------
        |
        | Existing dashboard behavior is retained:
        | last 12 months are shown.
        |
        */

        $monthlySeries = collect(
            range(0, 11)
        )->map(
            function ($offset) {
                $month =
                    now()
                        ->copy()
                        ->startOfMonth()
                        ->subMonths(
                            11 - $offset
                        );

                return [
                    'label' =>
                        $month->format('M'),

                    'amount' =>
                        0,

                    'month' =>
                        $month->format('Y-m'),
                ];
            }
        );

        if (
            $successfulTransactions->isEmpty()
        ) {
            $result =
                $monthlySeries
                    ->map(
                        fn ($entry) => [
                            'month' =>
                                $entry['label'],

                            'value' =>
                                0,
                        ]
                    );

            $this->logComplete(
                'monthlyRevenue MAX empty',
                $startedAt,
                [
                    'result_count' =>
                        $result->count(),
                ]
            );

            return $result;
        }

        $stepStartedAt =
            microtime(true);

        $grouped =
            $successfulTransactions
                ->groupBy(
                    $transactionMonth
                );

        $this->logComplete(
            'monthlyRevenue MAX groupBy',
            $stepStartedAt,
            [
                'group_count' =>
                    $grouped->count(),
            ]
        );

        $result =
            $monthlySeries->map(
                function ($entry) use ($grouped) {
                    $key =
                        $entry['month'];

                    $amount =
                        $grouped
                            ->get(
                                $key,
                                collect()
                            )
                            ->sum('amount');

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
                'result_count' =>
                    $result->count(),
            ]
        );

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | Revenue By Payment Type
    |--------------------------------------------------------------------------
    |
    | Mixed invoices are handled here.
    |
    | Example:
    |
    |     invoice total = 20,358
    |     recurring      = 358
    |     one-time       = 20,000
    |
    | If the transaction collected 20,358:
    |
    |     subscription revenue = 358
    |     one-time revenue     = 20,000
    |
    | If the transaction collected only 10,000:
    |
    |     the 10,000 payment is allocated proportionally.
    |
    */

    protected function revenueForPaymentType(
        Collection $successfulTransactions,
        Collection $paidInvoices,
        PaymentType $paymentType,
        bool $invert = false
    ): int {
        if (
            $successfulTransactions->isEmpty()
        ) {
            return 0;
        }

        $total = 0;

        foreach (
            $successfulTransactions
            as $transaction
        ) {
            $subscriptionAmount =
                $this->transactionRecurringAmount(
                    $transaction
                );

            $transactionAmount =
                max(
                    (int) (
                        $transaction->amount
                        ?? 0
                    ),
                    0
                );

            $oneTimeAmount =
                max(
                    $transactionAmount
                    - $subscriptionAmount,
                    0
                );

            $amountForPaymentType =
                match ($paymentType) {
                    PaymentType::Subscription =>
                        $subscriptionAmount,

                    PaymentType::OneTime =>
                        $oneTimeAmount,

                    default =>
                        0,
                };

            if ($invert) {
                $amountForPaymentType =
                    max(
                        $transactionAmount
                        - $amountForPaymentType,
                        0
                    );
            }

            $total +=
                $amountForPaymentType;
        }

        return (int) $total;
    }

    /*
    |--------------------------------------------------------------------------
    | Transaction Recurring Amount
    |--------------------------------------------------------------------------
    */

    protected function transactionRecurringAmount(
        object $transaction
    ): int {
        $invoice =
            $transaction->invoice
            ?? null;

        if (
            !$invoice
            && !empty($transaction->invoice_id)
        ) {
            $invoice =
                Invoice::query()
                    ->with('items')
                    ->find(
                        $transaction->invoice_id
                    );
        }

        if (!$invoice) {
            return 0;
        }

        $recurringAmount =
            max(
                (int) $invoice
                    ->monthlyRecurringAmountInCents(),
                0
            );

        $invoiceAmount =
            max(
                (int) $invoice->total_amount,
                0
            );

        $transactionAmount =
            max(
                (int) (
                    $transaction->amount
                    ?? 0
                ),
                0
            );

        if (
            $recurringAmount <= 0
            || $invoiceAmount <= 0
            || $transactionAmount <= 0
        ) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | If the payment covers the entire invoice, cap recurring revenue
        | at the recurring invoice component.
        |--------------------------------------------------------------------------
        */

        if (
            $transactionAmount >= $invoiceAmount
        ) {
            return min(
                $recurringAmount,
                $transactionAmount
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Partial payment:
        |
        | Allocate payment proportionally between recurring and one-time
        | portions of the invoice.
        |--------------------------------------------------------------------------
        */

        return min(
            (int) round(
                $transactionAmount
                * (
                    $recurringAmount
                    / $invoiceAmount
                )
            ),
            $transactionAmount
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Payment Type
    |--------------------------------------------------------------------------
    |
    | Kept for compatibility with existing code.
    |
    | Note:
    | For mixed invoices, this invoice-level enum is NOT used to calculate
    | recurring/one-time revenue. The line-item allocation logic above is.
    |
    */

    protected function resolveTransactionPaymentType(
        object $transaction
    ): PaymentType {
        $invoice =
            $transaction->invoice
            ?? null;

        if (
            !$invoice
            && !empty($transaction->invoice_id)
        ) {
            $invoice =
                Invoice::query()
                    ->find(
                        $transaction->invoice_id
                    );
        }

        if (!$invoice) {
            return PaymentType::OneTime;
        }

        if (
            $invoice->payment_type instanceof PaymentType
        ) {
            return $invoice->payment_type;
        }

        return PaymentType::tryFrom(
            (string) $invoice->payment_type
        ) ?? PaymentType::OneTime;
    }

    /*
    |--------------------------------------------------------------------------
    | Stripe Revenue
    |--------------------------------------------------------------------------
    |
    | COMPATIBILITY METHOD
    |--------------------------------------------------------------------------
    |
    | The name is retained so existing dashboard code doesn't need to change.
    |
    | However, this method NO LONGER calls Stripe.
    |
    | Stripe data must already have been synchronized into transactions.
    |
    */

    protected function stripeRevenueForTeam(
        Team $team
    ): array {
        $startedAt = microtime(true);

        $this->logStart(
            'stripeRevenueForTeam local transaction ledger',
            [
                'team_id' => $team->id,
                'team_slug' => $team->slug,
            ]
        );

        try {
            /*
            |--------------------------------------------------------------------------
            | Team Invoice IDs
            |--------------------------------------------------------------------------
            */

            $teamInvoiceIds =
                $this->teamInvoiceIds(
                    $team
                );

            /*
            |--------------------------------------------------------------------------
            | Read successful transactions from DB
            |--------------------------------------------------------------------------
            */

            $transactions =
                Transaction::query()
                    ->whereIn(
                        'invoice_id',
                        $teamInvoiceIds
                    )
                    ->where(
                        'status',
                        TransactionStatus::Succeeded->value
                    )
                    ->whereNotNull(
                        'stripe_payment_intent_id'
                    )
                    ->where(
                        'stripe_payment_intent_id',
                        '!=',
                        ''
                    )
                    ->with([
                        'customer',
                        'invoice.items',
                    ])
                    ->orderBy(
                        'paid_at'
                    )
                    ->orderBy(
                        'created_at'
                    )
                    ->get();

            /*
            |--------------------------------------------------------------------------
            | Ensure amounts are safe / normalized.
            |--------------------------------------------------------------------------
            */

            $transactions =
                $transactions
                    ->map(
                        function (
                            Transaction $transaction
                        ) {
                            $transaction->amount =
                                max(
                                    (int) $transaction->amount,
                                    0
                                );

                            $transaction->processing_fee =
                                max(
                                    (int) (
                                        $transaction
                                            ->processing_fee
                                        ?? 0
                                    ),
                                    0
                                );

                            /*
                            |--------------------------------------------------------------------------
                            | Repair net_amount in memory for older rows.
                            |--------------------------------------------------------------------------
                            */

                            if (
                                (int) (
                                    $transaction->net_amount
                                    ?? 0
                                ) <= 0
                            ) {
                                $transaction->net_amount =
                                    max(
                                        $transaction->amount
                                        - $transaction->processing_fee,
                                        0
                                    );
                            }

                            return $transaction;
                        }
                    )
                    ->values();

            $totalRevenue =
                (int) $transactions
                    ->sum('amount');

            $this->logComplete(
                'stripeRevenueForTeam local transaction ledger',
                $startedAt,
                [
                    'team_id' =>
                        $team->id,

                    'invoice_count' =>
                        $teamInvoiceIds->count(),

                    'transaction_count' =>
                        $transactions->count(),

                    'total_revenue' =>
                        $totalRevenue,
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
                'TeamDashboardService: local transaction ledger FAILED',
                [
                    'team_id' =>
                        $team->id ?? null,

                    'team_slug' =>
                        $team->slug ?? null,

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
    |
    | Retained for compatibility.
    |
    | IMPORTANT:
    |
    | $customerId is a Stripe customer ID here, so we compare it against
    | customers.stripe_id.
    |
    | We do NOT compare it against invoices.customer_id because that column
    | contains your local UUID.
    |
    */

    protected function teamInvoicesForCustomer(
        Team $team,
        string $customerId
    ): Collection {
        $startedAt = microtime(true);

        $customerId = trim($customerId);

        Log::debug(
            'TeamDashboardService: teamInvoicesForCustomer START',
            [
                'team_id' => $team->id,
                'stripe_customer_id' => $customerId,
            ]
        );

        if ($customerId === '') {
            return collect();
        }

        $tickets = $team
            ->tickets()
            ->pluck('id');

        $result = Invoice::query()
            ->whereIn(
                'ticket_id',
                $tickets
            )
            ->whereHas(
                'customer',
                function ($customerQuery) use ($customerId) {
                    $customerQuery->where(
                        'stripe_id',
                        $customerId
                    );
                }
            )
            ->with([
                'customer',
                'items',
                'transactions',
            ])
            ->get();

        $this->logComplete(
            'teamInvoicesForCustomer',
            $startedAt,
            [
                'team_id' => $team->id,
                'stripe_customer_id' => $customerId,
                'invoice_count' => $result->count(),
            ]
        );

        return $result;
    }
    /*
    |--------------------------------------------------------------------------
    | Resolve Invoice Payment Type
    |--------------------------------------------------------------------------
    |
    | Compatibility method.
    |
    | The old implementation inspected invoice.stripe_id for pi_/sub_.
    | That is no longer valid because the schema now separates:
    |
    |   stripe_invoice_id
    |   stripe_subscription_id
    |
    | Local payment_type remains the fallback.
    |
    */

    protected function resolveInvoicePaymentType(
        Invoice $invoice
    ): PaymentType {
        try {
            /*
            |--------------------------------------------------------------------------
            | Explicit Stripe subscription ID.
            |--------------------------------------------------------------------------
            */

            if (
                !empty(
                    $invoice->stripe_subscription_id
                )
            ) {
                return PaymentType::Subscription;
            }

            /*
            |--------------------------------------------------------------------------
            | Local payment type.
            |--------------------------------------------------------------------------
            */

            if (
                $invoice->payment_type
                instanceof PaymentType
            ) {
                return $invoice->payment_type;
            }

            $paymentType =
                PaymentType::tryFrom(
                    (string) $invoice->payment_type
                );

            if ($paymentType) {
                return $paymentType;
            }

            /*
            |--------------------------------------------------------------------------
            | Safe fallback.
            |--------------------------------------------------------------------------
            |
            | Unknown data should not inflate MRR.
            |--------------------------------------------------------------------------
            */

            return PaymentType::OneTime;
        } catch (Throwable $e) {
            Log::warning(
                __CLASS__
                . ' failed to resolve invoice payment type',
                [
                    'invoice_id' =>
                        $invoice->id
                        ?? null,

                    'stripe_invoice_id' =>
                        $invoice->stripe_invoice_id
                        ?? null,

                    'stripe_subscription_id' =>
                        $invoice->stripe_subscription_id
                        ?? null,

                    'payment_type' =>
                        $invoice->payment_type
                        ?? null,

                    'message' =>
                        $e->getMessage(),

                    'exception_class' =>
                        get_class($e),
                ]
            );

            return PaymentType::OneTime;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Profit Distribution
    |--------------------------------------------------------------------------
    |
    | Pablo:
    |
    |   < $2,000 net  => 10%
    |   >= $2,000 net => 20%
    |
    | Remaining net:
    |
    |   50% owner
    |   50% partner
    |
    | This is calculated PER INVOICE using only the successful
    | transactions in the selected reporting period.
    |
    */

    protected function calculateProfitDistribution(
        Collection $successfulTransactions
    ): array {
        if (
            $successfulTransactions->isEmpty()
        ) {
            return [
                'pabloCommission' =>
                    0,

                'teamShareAfterPablo' =>
                    0,

                'ownerShare' =>
                    0,

                'partnerShare' =>
                    0,
            ];
        }

        $pabloCommission = 0;
        $teamShareAfterPablo = 0;
        $ownerShare = 0;
        $partnerShare = 0;

        /*
        |--------------------------------------------------------------------------
        | Group actual payments by invoice.
        |--------------------------------------------------------------------------
        */

        $transactionsByInvoice =
            $successfulTransactions
                ->groupBy(
                    function (
                        $transaction
                    ): string {
                        if (
                            !empty(
                                $transaction->invoice_id
                            )
                        ) {
                            return (string)
                                $transaction->invoice_id;
                        }

                        return 'transaction:'
                            . (string) $transaction->id;
                    }
                );

        foreach (
            $transactionsByInvoice
            as $invoiceTransactions
        ) {
            /*
            |--------------------------------------------------------------------------
            | Actual net amount collected in this reporting period.
            |--------------------------------------------------------------------------
            */

            $net =
                (int) $invoiceTransactions
                    ->sum(
                        function (
                            $transaction
                        ): int {
                            $amount =
                                max(
                                    (int) (
                                        $transaction->amount
                                        ?? 0
                                    ),
                                    0
                                );

                            $fee =
                                max(
                                    (int) (
                                        $transaction
                                            ->processing_fee
                                        ?? 0
                                    ),
                                    0
                                );

                            $storedNet =
                                $transaction
                                    ->net_amount
                                    ?? null;

                            if (
                                $storedNet !== null
                                && (int) $storedNet > 0
                            ) {
                                return (int) $storedNet;
                            }

                            return max(
                                $amount - $fee,
                                0
                            );
                        }
                    );

            if ($net <= 0) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Pablo
            |--------------------------------------------------------------------------
            */

            $rate =
                $net < 200000
                    ? 0.10
                    : 0.20;

            $invoicePabloCommission =
                (int) round(
                    $net * $rate
                );

            /*
            |--------------------------------------------------------------------------
            | Team
            |--------------------------------------------------------------------------
            */

            $invoiceTeamShare =
                max(
                    $net
                    - $invoicePabloCommission,
                    0
                );

            /*
            |--------------------------------------------------------------------------
            | 50 / 50 split
            |--------------------------------------------------------------------------
            */

            $invoiceOwnerShare =
                (int) round(
                    $invoiceTeamShare / 2
                );

            $invoicePartnerShare =
                max(
                    $invoiceTeamShare
                    - $invoiceOwnerShare,
                    0
                );

            $pabloCommission +=
                $invoicePabloCommission;

            $teamShareAfterPablo +=
                $invoiceTeamShare;

            $ownerShare +=
                $invoiceOwnerShare;

            $partnerShare +=
                $invoicePartnerShare;
        }

        return [
            'pabloCommission' =>
                (int) $pabloCommission,

            'teamShareAfterPablo' =>
                (int) $teamShareAfterPablo,

            'ownerShare' =>
                (int) $ownerShare,

            'partnerShare' =>
                (int) $partnerShare,
        ];
    }
}