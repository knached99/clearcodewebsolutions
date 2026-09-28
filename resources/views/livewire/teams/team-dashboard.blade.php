<div class="dashboard-shell">

    @php
        $grossProfitDisplay = '$' . number_format(
            ($grossProfit ?? 0) / 100,
            2
        );

        $processingFeesDisplay = '$' . number_format(
            abs(($processingFees ?? 0) / 100),
            2
        );

        $netProfitDisplay = '$' . number_format(
            ($netProfit ?? 0) / 100,
            2
        );

        $pabloCommissionDisplay = '$' . number_format(
            ($pabloCommission ?? 0) / 100,
            2
        );

        $ownerShareDisplay = '$' . number_format(
            ($ownerShare ?? $khaledShare ?? 0) / 100,
            2
        );

        $partnerShareDisplay = '$' . number_format(
            ($partnerShare ?? $karamShare ?? 0) / 100,
            2
        );

        $pabloRateDisplay = number_format(
            (($pabloRate ?? 0) * 100),
            0
        ) . '%';

        $teamShareAfterPabloDisplay = '$' . number_format(
            ($teamShareAfterPablo ?? 0) / 100,
            2
        );

        $distributionTotalDisplay = '$' . number_format(
            (
                ($pabloCommission ?? 0)
                + ($khaledShare ?? $ownerShare ?? 0)
                + ($karamShare ?? $partnerShare ?? 0)
            ) / 100,
            2
        );

        $lossDisplay = '$' . number_format(
            ($losses ?? 0) / 100,
            2
        );

        $mrrDisplay = '$' . number_format(
            ($mrr ?? 0) / 100,
            2
        );

        $arrDisplay = '$' . number_format(
            ($arr ?? 0) / 100,
            2
        );

        $grossMarginDisplay = number_format(
            ($grossMarginPercentage ?? 0),
            2
        ) . '%';

        $highestCustomerName =
            $highestProfitingCustomer['name']
            ?? 'No customer data';

        $highestCustomerAmount =
            isset($highestProfitingCustomer['amount'])
            ? '$' . number_format(
                $highestProfitingCustomer['amount'] / 100,
                2
            )
            : '$0.00';

        $revenueLabels = collect(
            $monthlyRevenue ?? []
        )
            ->pluck('month')
            ->values()
            ->all();

        $revenueValues = collect(
            $monthlyRevenue ?? []
        )
            ->pluck('value')
            ->map(
                fn ($value) => (int) $value
            )
            ->values()
            ->all();

        $customerChartLabels = collect(
            $customerRevenue ?? []
        )
            ->pluck('name')
            ->values()
            ->all();

        $customerChartValues = collect(
            $customerRevenue ?? []
        )
            ->pluck('amount')
            ->map(
                fn ($value) => (int) $value
            )
            ->values()
            ->all();
    @endphp


    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="dashboard-header">

        <div>

            <div class="dashboard-eyebrow">
                Operations overview
            </div>

            <h1>
                {{ $team->name }} analytics
            </h1>

            <p>
                Financial performance and business activity at a glance.
            </p>

        </div>


        <div class="dashboard-filter-wrap">

            <label for="dashboard-filter">
                Reporting period
            </label>

            <select
                id="dashboard-filter"
                class="metric-select"
                wire:change="setFilter($event.target.value)"
            >
                <option
                    value="max"
                    @selected($filter === 'max')
                >
                    All Time
                </option>

                <option
                    value="mtd"
                    @selected($filter === 'mtd')
                >
                    Month to Date
                </option>

                <option
                    value="ytd"
                    @selected($filter === 'ytd')
                >
                    Year to Date
                </option>
            </select>

        </div>

    </div>


    {{-- =========================================================
         STATUS
    ========================================================== --}}

    <div class="dashboard-status-row">

        <div class="status-pill status-pill-success">

            <span class="status-dot"></span>

            <span>
                {{ number_format($paidInvoices ?? 0) }}
                paid invoices
            </span>

        </div>


        <div class="status-pill status-pill-primary">

            <span class="status-dot"></span>

            <span>
                {{ number_format($activeSubscriptions ?? 0) }}
                active subscriptions
            </span>

        </div>

    </div>


    {{-- =========================================================
         PRIMARY METRICS
    ========================================================== --}}

    <section class="metrics-grid">

        <article class="metric-card">

            <div class="metric-card-top">

                <span class="metric-label">
                    Gross profit
                </span>

                <div class="metric-icon metric-icon-purple">
                    $
                </div>

            </div>

            <div class="metric-value">
                {{ $grossProfitDisplay }}
            </div>

            <div class="metric-description">
                Total revenue before expenses
            </div>

        </article>


        <article class="metric-card">

        <div class="metric-card-top">
            <span class="metric-label">
                Stripe fees
            </span>

            <div class="metric-icon metric-icon-orange">
                %
            </div>
        </div>

        <div
            class="metric-value"
            style="{{ ($processingFees ?? 0) != 0 ? 'color: #ff4747;' : '' }}"
        >
            {{ ($processingFees ?? 0) != 0 ? '-' : '' }}{{ $processingFeesDisplay }}
        </div>

        <div class="metric-description">
            Payment processing costs
        </div>

    </article>


        <article class="metric-card">

            <div class="metric-card-top">

                <span class="metric-label">
                    Net profit
                </span>

                <div class="metric-icon metric-icon-green">
                    +
                </div>

            </div>

            <div class="metric-value">
                {{ $netProfitDisplay }}
            </div>

            <div class="metric-description">
                Revenue after processing fees
            </div>

        </article>


        <article class="metric-card">

            <div class="metric-card-top">

                <span class="metric-label">
                    MRR
                </span>

                <div class="metric-icon metric-icon-blue">
                    ↗
                </div>

            </div>

            <div class="metric-value">
                {{ $mrrDisplay }}
            </div>

            <div class="metric-description">
              (NET) Monthly recurring revenue
            </div>

        </article>


        <article class="metric-card">

            <div class="metric-card-top">

                <span class="metric-label">
                    ARR
                </span>

                <div class="metric-icon metric-icon-cyan">
                    ↑
                </div>

            </div>

            <div class="metric-value">
                {{ $arrDisplay }}
            </div>

            <div class="metric-description">
              (NET)  Annualized recurring revenue
            </div>

        </article>


        <article class="metric-card">

            <div class="metric-card-top">

                <span class="metric-label">
                    Losses
                </span>

                <div class="metric-icon metric-icon-red">
                    −
                </div>

            </div>

            <div class="metric-value">
                {{ $lossDisplay }}
            </div>

            <div class="metric-description">
                Recorded business losses
            </div>

        </article>


        <article class="metric-card">

            <div class="metric-card-top">

                <span class="metric-label">
                    Gross margin
                </span>

                <div class="metric-icon metric-icon-green">
                    %
                </div>

            </div>

            <div class="metric-value">
                {{ $grossMarginDisplay }}
            </div>

            <div class="metric-description">
                Revenue retained before expenses
            </div>

        </article>


        <article class="metric-card">

            <div class="metric-card-top">

                <span class="metric-label">
                    Highest profit customer
                </span>

                <div class="metric-icon metric-icon-purple">
                    ★
                </div>

            </div>

            <div class="metric-value metric-value-customer">
                {{ $highestCustomerName }}
            </div>

            <div class="metric-description">
                {{ $highestCustomerAmount }} generated
            </div>

        </article>

    </section>


    {{-- =========================================================
         SECONDARY METRICS
    ========================================================== --}}

    <section class="secondary-grid">

        <article class="secondary-card">

            <div class="secondary-label">
                Average invoice
            </div>

            <div class="secondary-value">
                ${{ number_format(
                    ($averageInvoiceValue ?? 0) / 100,
                    2
                ) }}
            </div>

        </article>


        <article class="secondary-card">

            <div class="secondary-label">
                Subscription revenue
            </div>

            <div class="secondary-value">
                ${{ number_format(
                    ($subscriptionRevenue ?? 0) / 100,
                    2
                ) }}
            </div>

        </article>


        <article class="secondary-card">

            <div class="secondary-label">
                One-time revenue
            </div>

            <div class="secondary-value">
                ${{ number_format(
                    ($oneTimeRevenue ?? 0) / 100,
                    2
                ) }}
            </div>

        </article>


        <article class="secondary-card">

            <div class="secondary-label">
                Total invoices
            </div>

            <div class="secondary-value">
                {{ number_format($totalInvoices ?? 0) }}
            </div>

        </article>

    </section>


    {{-- =========================================================
         PROFIT DISTRIBUTION
    ========================================================== --}}

    <section class="profit-distribution-card">

        <div class="profit-distribution-header">

            <div>

                <div class="chart-eyebrow">
                    Profit distribution
                </div>

                <h2>
                    Commission and ownership breakdown
                </h2>

                <p>
                    How the net profit is distributed after Stripe processing fees.
                </p>

            </div>

            <div class="chart-badge">
                {{ $pabloRateDisplay }} Pablo commission
            </div>

        </div>


        <div class="distribution-summary">

            <div class="distribution-summary-item">

                <span class="distribution-summary-label">
                    Pablo's commission
                </span>

                <strong class="distribution-summary-value">
                    {{ $pabloCommissionDisplay }}
                </strong>

                <span class="distribution-summary-description">
                    {{ $pabloRateDisplay }} of net revenue
                </span>

            </div>


            <div class="distribution-summary-divider"></div>


            <div class="distribution-summary-item">

                <span class="distribution-summary-label">
                    After Pablo's commission
                </span>

                <strong class="distribution-summary-value">
                    {{ $teamShareAfterPabloDisplay }}
                </strong>

                <span class="distribution-summary-description">
                    Available for Khaled and Karam
                </span>

            </div>

        </div>


        <div class="distribution-grid">

            {{-- Pablo --}}

            <article class="distribution-recipient distribution-recipient-pablo">

                <div class="distribution-recipient-top">

                    <div class="distribution-recipient-icon">
                        %
                    </div>

                    <span class="distribution-recipient-badge">
                        Commission
                    </span>

                </div>

                <div class="distribution-recipient-name">
                    Pablo
                </div>

                <div class="distribution-recipient-amount">
                    {{ $pabloCommissionDisplay }}
                </div>

                <div class="distribution-recipient-description">
                    {{ $pabloRateDisplay }} of net revenue
                </div>

                <div class="distribution-recipient-rule">

                    @if (($grossProfit ?? 0) < 200000)

                        Gross revenue below $2,000

                    @else

                        Gross revenue of $2,000 or more

                    @endif

                </div>

            </article>


            {{-- Khaled --}}

            <article class="distribution-recipient distribution-recipient-khaled">

                <div class="distribution-recipient-top">

                    <div class="distribution-recipient-icon">
                        K
                    </div>

                    <span class="distribution-recipient-badge">
                        Owner share
                    </span>

                </div>

                <div class="distribution-recipient-name">
                    Khaled
                </div>

                <div class="distribution-recipient-amount">
                    {{ $ownerShareDisplay }}
                </div>

                <div class="distribution-recipient-description">
                    50% of the remaining profit
                </div>

                <div class="distribution-recipient-rule">
                    After Pablo's commission
                </div>

            </article>


            {{-- Karam --}}

            <article class="distribution-recipient distribution-recipient-karam">

                <div class="distribution-recipient-top">

                    <div class="distribution-recipient-icon">
                        K
                    </div>

                    <span class="distribution-recipient-badge">
                        Partner share
                    </span>

                </div>

                <div class="distribution-recipient-name">
                    Karam
                </div>

                <div class="distribution-recipient-amount">
                    {{ $partnerShareDisplay }}
                </div>

                <div class="distribution-recipient-description">
                    50% of the remaining profit
                </div>

                <div class="distribution-recipient-rule">
                    After Pablo's commission
                </div>

            </article>

        </div>


        <div class="distribution-footer">

            <div class="distribution-footer-label">
                Total allocated
            </div>

            <div class="distribution-footer-value">
                {{ $distributionTotalDisplay }}
            </div>

            <div class="distribution-footer-description">
                Pablo commission + Khaled share + Karam share
            </div>

        </div>

    </section>


    {{-- =========================================================
         CHARTS
    ========================================================== --}}

    <section class="charts-grid">

        <article class="chart-card chart-card-large">

            <div class="chart-header">

                <div>

                    <div class="chart-eyebrow">
                        Revenue trend
                    </div>

                    <h2>
                        12 month performance
                    </h2>

                </div>

                <div class="chart-badge">
                    Revenue
                </div>

            </div>

            <div
                id="revenueChart"
                class="chart-container"
                wire:ignore
            ></div>

        </article>


        <article class="chart-card chart-card-large">

            <div class="chart-header">

                <div>

                    <div class="chart-eyebrow">
                        Customer mix
                    </div>

                    <h2>
                        Top customers
                    </h2>

                </div>

                <div class="chart-badge">
                    Customers
                </div>

            </div>


            @if(empty($customerRevenue))

                <div class="chart-empty">

                    <div class="empty-icon">
                        $
                    </div>

                    <strong>
                        No customer data
                    </strong>

                    <span>
                        Customer revenue will appear here once payments are recorded.
                    </span>

                </div>

            @else

                <div
                    id="customerChart"
                    class="chart-container"
                    wire:ignore
                ></div>

            @endif

        </article>

    </section>


    {{-- =========================================================
         REVENUE SPLIT
    ========================================================== --}}

    <section class="chart-card chart-card-split">

        <div class="chart-header">

            <div>

                <div class="chart-eyebrow">
                    Revenue split
                </div>

                <h2>
                    Payment type contribution
                </h2>

            </div>

            <div class="chart-badge">
                Breakdown
            </div>

        </div>


        <div class="donut-layout">

            <div
                id="donutChart"
                class="donut-chart"
                wire:ignore
            ></div>


            <div class="donut-legend">

                <div class="legend-item">

                    <span class="legend-color legend-color-subscription"></span>

                    <div>

                        <strong>
                            Subscription revenue
                        </strong>

                        <span>
                            ${{ number_format(
                                ($subscriptionRevenue ?? 0) / 100,
                                2
                            ) }}
                        </span>

                    </div>

                </div>


                <div class="legend-item">

                    <span class="legend-color legend-color-onetime"></span>

                    <div>

                        <strong>
                            One-time revenue
                        </strong>

                        <span>
                            ${{ number_format(
                                ($oneTimeRevenue ?? 0) / 100,
                                2
                            ) }}
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
         STYLES
    ========================================================== --}}

    <style>

        .dashboard-shell {
            --dashboard-bg: #f5f7fb;
            --dashboard-surface: #ffffff;
            --dashboard-surface-soft: #f8fafc;
            --dashboard-border: #e5e7eb;

            --dashboard-text: #0f172a;
            --dashboard-muted: #64748b;
            --dashboard-subtle: #94a3b8;

            --dashboard-primary: #7c3aed;
            --dashboard-primary-soft: #f3e8ff;

            --dashboard-success: #059669;
            --dashboard-success-soft: #ecfdf5;

            --dashboard-warning: #d97706;
            --dashboard-warning-soft: #fffbeb;

            --dashboard-danger: #dc2626;
            --dashboard-danger-soft: #fef2f2;

            --dashboard-blue: #2563eb;
            --dashboard-blue-soft: #eff6ff;

            --dashboard-cyan: #0891b2;
            --dashboard-cyan-soft: #ecfeff;

            --dashboard-shadow:
                0 10px 30px rgba(15, 23, 42, 0.06);

            --dashboard-shadow-hover:
                0 16px 40px rgba(15, 23, 42, 0.10);

            width: 100%;
            min-height: 100%;

            padding: 32px;

            background: var(--dashboard-bg);
            color: var(--dashboard-text);

            transition:
                background-color 180ms ease,
                color 180ms ease;
        }


        .dark .dashboard-shell {
            --dashboard-bg: #070d18;
            --dashboard-surface: #0f172a;
            --dashboard-surface-soft: #111c30;
            --dashboard-border: rgba(148, 163, 184, 0.16);

            --dashboard-text: #f1f5f9;
            --dashboard-muted: #94a3b8;
            --dashboard-subtle: #64748b;

            --dashboard-primary: #a78bfa;
            --dashboard-primary-soft: rgba(167, 139, 250, 0.13);

            --dashboard-success: #34d399;
            --dashboard-success-soft: rgba(52, 211, 153, 0.12);

            --dashboard-warning: #fbbf24;
            --dashboard-warning-soft: rgba(251, 191, 36, 0.12);

            --dashboard-danger: #f87171;
            --dashboard-danger-soft: rgba(248, 113, 113, 0.12);

            --dashboard-blue: #60a5fa;
            --dashboard-blue-soft: rgba(96, 165, 250, 0.12);

            --dashboard-cyan: #22d3ee;
            --dashboard-cyan-soft: rgba(34, 211, 238, 0.12);

            --dashboard-shadow:
                0 18px 40px rgba(0, 0, 0, 0.30);

            --dashboard-shadow-hover:
                0 24px 52px rgba(0, 0, 0, 0.42);
        }


        .dashboard-shell *,
        .dashboard-shell *::before,
        .dashboard-shell *::after {
            box-sizing: border-box;
        }


        .dashboard-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;

            gap: 24px;
            margin-bottom: 22px;
        }


        .dashboard-eyebrow {
            margin-bottom: 7px;

            color: var(--dashboard-primary);

            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            line-height: 1;
            text-transform: uppercase;
        }


        .dashboard-header h1 {
            margin: 0;

            color: var(--dashboard-text);

            font-size: clamp(28px, 4vw, 38px);
            font-weight: 750;
            letter-spacing: -0.035em;
            line-height: 1.1;
        }


        .dashboard-header p {
            margin: 9px 0 0;

            color: var(--dashboard-muted);

            font-size: 14px;
            line-height: 1.5;
        }


        .dashboard-filter-wrap {
            min-width: 175px;
        }


        .dashboard-filter-wrap label {
            display: block;

            margin-bottom: 7px;

            color: var(--dashboard-muted);

            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }


        .metric-select {
            width: 100%;
            height: 42px;

            padding: 0 13px;

            border: 1px solid var(--dashboard-border);
            border-radius: 11px;

            background: var(--dashboard-surface);
            color: var(--dashboard-text);

            box-shadow:
                0 2px 7px rgba(15, 23, 42, 0.04);

            font-size: 13px;
            font-weight: 600;

            outline: none;
        }


        .dark .metric-select {
            box-shadow:
                0 2px 10px rgba(0, 0, 0, 0.20);
        }


        .metric-select:focus {
            border-color:
                rgba(124, 58, 237, 0.45);

            box-shadow:
                0 0 0 3px rgba(124, 58, 237, 0.10);
        }


        .metric-select option {
            background: var(--dashboard-surface);
            color: var(--dashboard-text);
        }


        .dashboard-status-row {
            display: flex;
            flex-wrap: wrap;

            gap: 9px;

            margin-bottom: 22px;
        }


        .status-pill {
            display: inline-flex;
            align-items: center;

            gap: 8px;

            min-height: 31px;

            padding: 0 11px;

            border: 1px solid transparent;
            border-radius: 999px;

            font-size: 12px;
            font-weight: 650;
        }


        .status-dot {
            width: 7px;
            height: 7px;

            flex: 0 0 7px;

            border-radius: 999px;
        }


        .status-pill-success {
            background: var(--dashboard-success-soft);
            border-color: #a7f3d0;
            color: #047857;
        }


        .dark .status-pill-success {
            border-color: rgba(52, 211, 153, 0.20);
            color: #6ee7b7;
        }


        .status-pill-success .status-dot {
            background: #10b981;
        }


        .status-pill-primary {
            background: var(--dashboard-primary-soft);
            border-color: #ddd6fe;
            color: #6d28d9;
        }


        .dark .status-pill-primary {
            border-color: rgba(167, 139, 250, 0.20);
            color: #c4b5fd;
        }


        .status-pill-primary .status-dot {
            background: #8b5cf6;
        }


        .metrics-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 14px;

            margin-bottom: 14px;
        }


        .metric-card {
            min-width: 0;

            padding: 20px;

            border: 1px solid var(--dashboard-border);
            border-radius: 16px;

            background: var(--dashboard-surface);
            color: var(--dashboard-text);

            box-shadow: var(--dashboard-shadow);

            transition:
                transform 180ms ease,
                box-shadow 180ms ease,
                border-color 180ms ease,
                background-color 180ms ease;
        }


        .metric-card:hover {
            transform: translateY(-2px);

            border-color:
                rgba(124, 58, 237, 0.20);

            box-shadow: var(--dashboard-shadow-hover);
        }


        .metric-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 12px;
        }


        .metric-label {
            color: var(--dashboard-muted);

            font-size: 12px;
            font-weight: 650;
        }


        .metric-icon {
            display: flex;
            align-items: center;
            justify-content: center;

            width: 32px;
            height: 32px;

            flex: 0 0 32px;

            border-radius: 9px;

            font-size: 14px;
            font-weight: 800;
        }


        .metric-icon-purple {
            background: var(--dashboard-primary-soft);
            color: var(--dashboard-primary);
        }


        .metric-icon-orange {
            background: var(--dashboard-warning-soft);
            color: var(--dashboard-warning);
        }


        .metric-icon-green {
            background: var(--dashboard-success-soft);
            color: var(--dashboard-success);
        }


        .metric-icon-blue {
            background: var(--dashboard-blue-soft);
            color: var(--dashboard-blue);
        }


        .metric-icon-cyan {
            background: var(--dashboard-cyan-soft);
            color: var(--dashboard-cyan);
        }


        .metric-icon-red {
            background: var(--dashboard-danger-soft);
            color: var(--dashboard-danger);
        }


        .metric-value {
            margin-top: 17px;

            overflow: hidden;

            color: var(--dashboard-text);

            font-size: clamp(22px, 2.3vw, 29px);
            font-weight: 750;
            letter-spacing: -0.035em;
            line-height: 1.1;

            text-overflow: ellipsis;
            white-space: nowrap;
        }


        .metric-value-customer {
            font-size: 20px;
        }


        .metric-description {
            margin-top: 7px;

            overflow: hidden;

            color: var(--dashboard-subtle);

            font-size: 11px;
            line-height: 1.45;

            text-overflow: ellipsis;
            white-space: nowrap;
        }


        .secondary-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 14px;

            margin-bottom: 22px;
        }


        .secondary-card {
            padding: 17px 18px;

            border: 1px solid var(--dashboard-border);
            border-radius: 14px;

            background: var(--dashboard-surface-soft);
            color: var(--dashboard-text);
        }


        .secondary-label {
            color: var(--dashboard-muted);

            font-size: 11px;
            font-weight: 650;
        }


        .secondary-value {
            margin-top: 7px;

            color: var(--dashboard-text);

            font-size: 19px;
            font-weight: 750;
            letter-spacing: -0.02em;
        }


        .charts-grid {
            display: grid;

            grid-template-columns:
                minmax(0, 1.35fr)
                minmax(0, 1fr);

            gap: 14px;

            margin-bottom: 14px;
        }


        .chart-card {
            min-width: 0;

            padding: 21px;

            border: 1px solid var(--dashboard-border);
            border-radius: 17px;

            background: var(--dashboard-surface);
            color: var(--dashboard-text);

            box-shadow: var(--dashboard-shadow);
        }


        .chart-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            gap: 16px;

            margin-bottom: 12px;
        }


        .chart-eyebrow {
            margin-bottom: 5px;

            color: var(--dashboard-subtle);

            font-size: 10px;
            font-weight: 750;
            letter-spacing: 0.09em;
            text-transform: uppercase;
        }


        .chart-header h2 {
            margin: 0;

            color: var(--dashboard-text);

            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.015em;
        }


        .chart-badge {
            flex: 0 0 auto;

            padding: 6px 9px;

            border: 1px solid var(--dashboard-border);
            border-radius: 8px;

            background: var(--dashboard-surface-soft);
            color: var(--dashboard-muted);

            font-size: 10px;
            font-weight: 700;
        }


        .chart-container {
            width: 100%;
            min-height: 320px;
        }


        .chart-empty {
            display: flex;
            align-items: center;
            justify-content: center;

            flex-direction: column;

            min-height: 320px;

            padding: 30px;

            text-align: center;
        }


        .empty-icon {
            display: flex;
            align-items: center;
            justify-content: center;

            width: 48px;
            height: 48px;

            margin-bottom: 13px;

            border-radius: 13px;

            background: var(--dashboard-primary-soft);
            color: var(--dashboard-primary);

            font-size: 20px;
            font-weight: 800;
        }


        .chart-empty strong {
            color: var(--dashboard-text);

            font-size: 14px;
        }


        .chart-empty span {
            max-width: 280px;

            margin-top: 6px;

            color: var(--dashboard-muted);

            font-size: 12px;
            line-height: 1.5;
        }


        .chart-card-split {
            margin-bottom: 10px;
        }


        .donut-layout {
            display: grid;

            grid-template-columns:
                minmax(240px, 1fr)
                minmax(220px, 0.8fr);

            align-items: center;

            gap: 30px;
        }


        .donut-chart {
            min-height: 300px;
        }


        .donut-legend {
            display: flex;
            flex-direction: column;

            gap: 18px;
        }


        .legend-item {
            display: flex;
            align-items: flex-start;

            gap: 11px;
        }


        .legend-color {
            width: 10px;
            height: 10px;

            flex: 0 0 10px;

            margin-top: 4px;

            border-radius: 999px;
        }


        .legend-color-subscription {
            background: #7c3aed;
        }


        .legend-color-onetime {
            background: #0ea5e9;
        }


        .legend-item div {
            display: flex;
            flex-direction: column;

            gap: 3px;
        }


        .legend-item strong {
            color: var(--dashboard-text);

            font-size: 13px;
            font-weight: 650;
        }


        .legend-item span:not(.legend-color) {
            color: var(--dashboard-muted);

            font-size: 12px;
        }


        .profit-distribution-card {
            min-width: 0;

            margin-bottom: 22px;
            padding: 24px;

            border: 1px solid var(--dashboard-border);
            border-radius: 17px;

            background: var(--dashboard-surface);
            color: var(--dashboard-text);

            box-shadow: var(--dashboard-shadow);
        }


        .profit-distribution-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 22px;
        }


        .profit-distribution-header h2 {
            margin: 0;

            color: var(--dashboard-text);

            font-size: 19px;
            font-weight: 750;
            letter-spacing: -0.025em;
        }


        .profit-distribution-header p {
            margin: 8px 0 0;

            color: var(--dashboard-muted);

            font-size: 12px;
            line-height: 1.5;
        }


        .distribution-summary {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                auto
                minmax(0, 1fr);

            align-items: center;

            gap: 24px;

            margin-bottom: 22px;
            padding: 18px;

            border: 1px solid var(--dashboard-border);
            border-radius: 13px;

            background: var(--dashboard-surface-soft);
        }


        .distribution-summary-item {
            display: flex;

            min-width: 0;

            flex-direction: column;

            gap: 5px;
        }


        .distribution-summary-label {
            color: var(--dashboard-muted);

            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }


        .distribution-summary-value {
            color: var(--dashboard-text);

            font-size: clamp(22px, 3vw, 30px);
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.15;
        }


        .distribution-summary-description {
            color: var(--dashboard-subtle);

            font-size: 11px;
            line-height: 1.4;
        }


        .distribution-summary-divider {
            width: 1px;
            height: 65px;

            background: var(--dashboard-border);
        }


        .distribution-grid {
            display: grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 14px;
        }


        .distribution-recipient {
            min-width: 0;

            padding: 19px;

            border: 1px solid var(--dashboard-border);
            border-radius: 14px;

            background: var(--dashboard-surface-soft);

            transition:
                transform 180ms ease,
                box-shadow 180ms ease,
                border-color 180ms ease;
        }


        .distribution-recipient:hover {
            transform: translateY(-2px);

            border-color:
                rgba(124, 58, 237, 0.25);

            box-shadow: var(--dashboard-shadow-hover);
        }


        .distribution-recipient-top {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 10px;
        }


        .distribution-recipient-icon {
            display: flex;
            align-items: center;
            justify-content: center;

            width: 35px;
            height: 35px;

            border-radius: 10px;

            font-size: 14px;
            font-weight: 800;
        }


        .distribution-recipient-badge {
            padding: 5px 8px;

            border: 1px solid var(--dashboard-border);
            border-radius: 7px;

            color: var(--dashboard-muted);

            font-size: 10px;
            font-weight: 700;
        }


        .distribution-recipient-name {
            margin-top: 19px;

            color: var(--dashboard-muted);

            font-size: 12px;
            font-weight: 700;
        }


        .distribution-recipient-amount {
            margin-top: 7px;

            overflow: hidden;

            color: var(--dashboard-text);

            font-size: clamp(23px, 3vw, 32px);
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.15;

            text-overflow: ellipsis;
            white-space: nowrap;
        }


        .distribution-recipient-description {
            margin-top: 8px;

            color: var(--dashboard-muted);

            font-size: 11px;
            line-height: 1.45;
        }


        .distribution-recipient-rule {
            margin-top: 15px;
            padding-top: 12px;

            border-top: 1px solid var(--dashboard-border);

            color: var(--dashboard-subtle);

            font-size: 10px;
            line-height: 1.45;
        }


        .distribution-recipient-pablo
            .distribution-recipient-icon {
            background: var(--dashboard-warning-soft);
            color: var(--dashboard-warning);
        }


        .distribution-recipient-khaled
            .distribution-recipient-icon {
            background: var(--dashboard-primary-soft);
            color: var(--dashboard-primary);
        }


        .distribution-recipient-karam
            .distribution-recipient-icon {
            background: var(--dashboard-blue-soft);
            color: var(--dashboard-blue);
        }


        .distribution-footer {
            display: flex;
            align-items: baseline;

            flex-wrap: wrap;

            gap: 9px;

            margin-top: 18px;
            padding-top: 17px;

            border-top: 1px solid var(--dashboard-border);
        }


        .distribution-footer-label {
            color: var(--dashboard-muted);

            font-size: 12px;
            font-weight: 700;
        }


        .distribution-footer-value {
            color: var(--dashboard-text);

            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }


        .distribution-footer-description {
            color: var(--dashboard-subtle);

            font-size: 11px;
        }


        @media (max-width: 1200px) {

            .metrics-grid {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }

            .charts-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 900px) {

            .metrics-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .secondary-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .donut-layout {
                grid-template-columns: 1fr;
            }

            .distribution-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 640px) {

            .dashboard-shell {
                padding: 20px 14px;
            }

            .dashboard-header {
                align-items: stretch;
                flex-direction: column;
            }

            .dashboard-filter-wrap {
                width: 100%;
            }

            .metrics-grid {
                grid-template-columns: 1fr;
            }

            .secondary-grid {
                grid-template-columns: 1fr;
            }

            .chart-card {
                padding: 16px;
            }

            .chart-container {
                min-height: 270px;
            }

            .donut-chart {
                min-height: 270px;
            }

            .profit-distribution-card {
                padding: 17px;
            }

            .profit-distribution-header {
                flex-direction: column;
                gap: 13px;
            }

            .distribution-summary {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .distribution-summary-divider {
                width: 100%;
                height: 1px;
            }

            .distribution-summary-value {
                font-size: 27px;
            }

            .distribution-recipient {
                padding: 17px;
            }

            .distribution-recipient-amount {
                font-size: 28px;
            }

        }

    </style>


    {{-- =========================================================
         APEXCHARTS / LIVEWIRE BRIDGE
         
         IMPORTANT:
         This is a normal script inside the ONE root element.
         There is intentionally no @script or @assets directive.
    ========================================================== --}}

    <script>
        (() => {
            const initialData = {
                revenue: {
                    labels: @js($revenueLabels),
                    values: @js($revenueValues),
                },

                customers: {
                    labels: @js($customerChartLabels),
                    values: @js($customerChartValues),
                },

                donut: {
                    subscription: {{ (int) ($subscriptionRevenue ?? 0) }},
                    oneTime: {{ (int) ($oneTimeRevenue ?? 0) }},
                },
            };


            /*
            |--------------------------------------------------------------------------
            | Create Chart Manager Once
            |--------------------------------------------------------------------------
            */

            if (!window.TeamDashboardCharts) {

                window.TeamDashboardCharts = (() => {

                    let dashboardData = {
                        revenue: {
                            labels: [],
                            values: [],
                        },

                        customers: {
                            labels: [],
                            values: [],
                        },

                        donut: {
                            subscription: 0,
                            oneTime: 0,
                        },
                    };


                    let initialized = false;

                    let apexLoadPromise = null;


                    /*
                    |--------------------------------------------------------------------------
                    | Theme
                    |--------------------------------------------------------------------------
                    */

                    function isDarkMode() {
                        return document.documentElement
                            .classList
                            .contains('dark');
                    }


                    function chartTheme() {
                        return isDarkMode()
                            ? 'dark'
                            : 'light';
                    }


                    function chartTextColor() {
                        return isDarkMode()
                            ? '#94a3b8'
                            : '#64748b';
                    }


                    function chartPrimaryTextColor() {
                        return isDarkMode()
                            ? '#f1f5f9'
                            : '#0f172a';
                    }


                    function chartGridColor() {
                        return isDarkMode()
                            ? 'rgba(148, 163, 184, 0.14)'
                            : '#e5e7eb';
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ApexCharts Loader
                    |--------------------------------------------------------------------------
                    */

                    function ensureApexCharts() {

                        if (
                            typeof window.ApexCharts
                            !== 'undefined'
                        ) {
                            return Promise.resolve();
                        }


                        if (apexLoadPromise) {
                            return apexLoadPromise;
                        }


                        apexLoadPromise =
                            new Promise(
                                (
                                    resolve,
                                    reject
                                ) => {

                                    const existing =
                                        document.querySelector(
                                            'script[data-team-dashboard-apexcharts]'
                                        );


                                    if (existing) {

                                        existing.addEventListener(
                                            'load',
                                            () => resolve()
                                        );

                                        existing.addEventListener(
                                            'error',
                                            reject
                                        );

                                        return;
                                    }


                                    const script =
                                        document.createElement(
                                            'script'
                                        );

                                    script.src =
                                        'https://cdn.jsdelivr.net/npm/apexcharts';

                                    script.dataset
                                        .teamDashboardApexcharts =
                                        'true';

                                    script.onload = () =>
                                        resolve();

                                    script.onerror =
                                        reject;

                                    document.head.appendChild(
                                        script
                                    );

                                }
                            );

                        return apexLoadPromise;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Data
                    |--------------------------------------------------------------------------
                    */

                    function setData(data) {

                        if (!data) {
                            return;
                        }


                        dashboardData = {

                            revenue: {
                                labels:
                                    Array.isArray(
                                        data.revenue?.labels
                                    )
                                        ? data.revenue.labels
                                        : [],

                                values:
                                    Array.isArray(
                                        data.revenue?.values
                                    )
                                        ? data.revenue.values
                                        : [],
                            },


                            customers: {
                                labels:
                                    Array.isArray(
                                        data.customers?.labels
                                    )
                                        ? data.customers.labels
                                        : [],

                                values:
                                    Array.isArray(
                                        data.customers?.values
                                    )
                                        ? data.customers.values
                                        : [],
                            },


                            donut: {
                                subscription:
                                    Number(
                                        data.donut
                                            ?.subscription
                                            ?? 0
                                    ),

                                oneTime:
                                    Number(
                                        data.donut
                                            ?.oneTime
                                            ?? 0
                                    ),
                            },

                        };
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Destroy Chart
                    |--------------------------------------------------------------------------
                    */

                    function destroyChart(id) {

                        const element =
                            document.getElementById(id);


                        if (!element) {
                            return;
                        }


                        if (element.__apexChart) {

                            try {
                                element.__apexChart.destroy();
                            } catch (error) {
                                console.warn(
                                    `Unable to destroy ${id}`,
                                    error
                                );
                            }

                            element.__apexChart = null;
                        }


                        element.innerHTML = '';
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Revenue Chart
                    |--------------------------------------------------------------------------
                    */

                    function renderRevenueChart() {

                        const element =
                            document.getElementById(
                                'revenueChart'
                            );


                        if (
                            !element
                            || typeof window.ApexCharts
                                === 'undefined'
                        ) {
                            return;
                        }


                        destroyChart(
                            'revenueChart'
                        );


                        const labels =
                            dashboardData.revenue
                                ?.labels
                                ?? [];


                        const values =
                            dashboardData.revenue
                                ?.values
                                ?? [];


                        const textColor =
                            chartTextColor();


                        const gridColor =
                            chartGridColor();


                        const chart =
                            new window.ApexCharts(
                                element,
                                {

                                    chart: {
                                        type: 'area',
                                        height: 320,

                                        toolbar: {
                                            show: false,
                                        },

                                        zoom: {
                                            enabled: false,
                                        },

                                        fontFamily:
                                            'inherit',

                                        background:
                                            'transparent',

                                        animations: {
                                            enabled: true,
                                        },
                                    },


                                    theme: {
                                        mode:
                                            chartTheme(),
                                    },


                                    series: [
                                        {
                                            name:
                                                'Revenue',

                                            data:
                                                values.map(
                                                    value =>
                                                        Number(
                                                            value
                                                        ) / 100
                                                ),
                                        },
                                    ],


                                    xaxis: {

                                        categories:
                                            labels,

                                        labels: {

                                            style: {
                                                colors:
                                                    textColor,

                                                fontSize:
                                                    '11px',
                                            },

                                        },

                                        axisBorder: {
                                            show: false,
                                        },

                                        axisTicks: {
                                            show: false,
                                        },

                                    },


                                    yaxis: {

                                        labels: {

                                            formatter:
                                                value =>
                                                    '$' +
                                                    Number(
                                                        value
                                                    ).toLocaleString(
                                                        undefined,
                                                        {
                                                            maximumFractionDigits:
                                                                0,
                                                        }
                                                    ),

                                            style: {
                                                colors:
                                                    textColor,

                                                fontSize:
                                                    '11px',
                                            },

                                        },

                                    },


                                    colors: [
                                        isDarkMode()
                                            ? '#a78bfa'
                                            : '#7c3aed',
                                    ],


                                    stroke: {

                                        curve:
                                            'smooth',

                                        width: 3,

                                    },


                                    fill: {

                                        type:
                                            'gradient',

                                        gradient: {

                                            shadeIntensity:
                                                1,

                                            opacityFrom:
                                                isDarkMode()
                                                    ? 0.24
                                                    : 0.28,

                                            opacityTo:
                                                0.03,

                                            stops: [
                                                0,
                                                100,
                                            ],

                                        },

                                    },


                                    grid: {

                                        borderColor:
                                            gridColor,

                                        strokeDashArray:
                                            4,

                                        xaxis: {

                                            lines: {
                                                show: false,
                                            },

                                        },

                                    },


                                    dataLabels: {
                                        enabled: false,
                                    },


                                    tooltip: {

                                        theme:
                                            chartTheme(),

                                        y: {

                                            formatter:
                                                value =>
                                                    '$' +
                                                    Number(
                                                        value
                                                    ).toLocaleString(
                                                        undefined,
                                                        {
                                                            minimumFractionDigits:
                                                                2,

                                                            maximumFractionDigits:
                                                                2,
                                                        }
                                                    ),

                                        },

                                    },


                                    noData: {
                                        text:
                                            'No revenue data',
                                    },

                                }
                            );


                        chart.render();

                        element.__apexChart =
                            chart;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Customer Chart
                    |--------------------------------------------------------------------------
                    */

                    function renderCustomerChart() {

                        const element =
                            document.getElementById(
                                'customerChart'
                            );


                        if (
                            !element
                            || typeof window.ApexCharts
                                === 'undefined'
                        ) {
                            return;
                        }


                        destroyChart(
                            'customerChart'
                        );


                        const categories =
                            dashboardData.customers
                                ?.labels
                                ?? [];


                        const values =
                            dashboardData.customers
                                ?.values
                                ?? [];


                        const chartValues =
                            values.map(
                                value =>
                                    Number(
                                        value
                                    ) / 100
                            );


                        const textColor =
                            chartTextColor();


                        const gridColor =
                            chartGridColor();


                        const chart =
                            new window.ApexCharts(
                                element,
                                {

                                    chart: {

                                        type:
                                            'bar',

                                        height:
                                            320,

                                        toolbar: {
                                            show: false,
                                        },

                                        fontFamily:
                                            'inherit',

                                        background:
                                            'transparent',

                                        animations: {
                                            enabled: true,
                                        },

                                    },


                                    theme: {
                                        mode:
                                            chartTheme(),
                                    },


                                    series: [
                                        {
                                            name:
                                                'Revenue',

                                            data:
                                                chartValues,
                                        },
                                    ],


                                    plotOptions: {

                                        bar: {

                                            borderRadius:
                                                7,

                                            horizontal:
                                                true,

                                            barHeight:
                                                '55%',

                                        },

                                    },


                                    colors: [

                                        isDarkMode()
                                            ? '#60a5fa'
                                            : '#2563eb',

                                    ],


                                    xaxis: {

                                        categories:
                                            categories,

                                        labels: {

                                            formatter:
                                                value =>
                                                    '$' +
                                                    Number(
                                                        value
                                                    ).toLocaleString(
                                                        undefined,
                                                        {
                                                            maximumFractionDigits:
                                                                0,
                                                        }
                                                    ),

                                            style: {
                                                colors:
                                                    textColor,

                                                fontSize:
                                                    '11px',
                                            },

                                        },

                                        axisBorder: {
                                            show: false,
                                        },

                                        axisTicks: {
                                            show: false,
                                        },

                                    },


                                    yaxis: {

                                        labels: {

                                            style: {

                                                colors:
                                                    textColor,

                                                fontSize:
                                                    '11px',

                                            },

                                        },

                                    },


                                    grid: {

                                        borderColor:
                                            gridColor,

                                        strokeDashArray:
                                            4,

                                    },


                                    dataLabels: {
                                        enabled: false,
                                    },


                                    tooltip: {

                                        theme:
                                            chartTheme(),

                                        y: {

                                            formatter:
                                                value =>
                                                    '$' +
                                                    Number(
                                                        value
                                                    ).toLocaleString(
                                                        undefined,
                                                        {
                                                            minimumFractionDigits:
                                                                2,

                                                            maximumFractionDigits:
                                                                2,
                                                        }
                                                    ),

                                        },

                                    },


                                    noData: {
                                        text:
                                            'No customer data',
                                    },

                                }
                            );


                        chart.render();

                        element.__apexChart =
                            chart;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Donut Chart
                    |--------------------------------------------------------------------------
                    */

                    function renderDonutChart() {

                        const element =
                            document.getElementById(
                                'donutChart'
                            );


                        if (
                            !element
                            || typeof window.ApexCharts
                                === 'undefined'
                        ) {
                            return;
                        }


                        destroyChart(
                            'donutChart'
                        );


                        const subscriptionRevenue =
                            Number(
                                dashboardData.donut
                                    ?.subscription
                                    ?? 0
                            ) / 100;


                        const oneTimeRevenue =
                            Number(
                                dashboardData.donut
                                    ?.oneTime
                                    ?? 0
                            ) / 100;


                        const textColor =
                            chartTextColor();


                        const primaryTextColor =
                            chartPrimaryTextColor();


                        const chart =
                            new window.ApexCharts(
                                element,
                                {

                                    chart: {

                                        type:
                                            'donut',

                                        height:
                                            300,

                                        fontFamily:
                                            'inherit',

                                        background:
                                            'transparent',

                                        animations: {
                                            enabled: true,
                                        },

                                    },


                                    theme: {
                                        mode:
                                            chartTheme(),
                                    },


                                    series: [

                                        subscriptionRevenue,

                                        oneTimeRevenue,

                                    ],


                                    labels: [

                                        'Subscription',

                                        'One-time',

                                    ],


                                    colors: [

                                        isDarkMode()
                                            ? '#a78bfa'
                                            : '#7c3aed',

                                        isDarkMode()
                                            ? '#38bdf8'
                                            : '#0ea5e9',

                                    ],


                                    stroke: {

                                        width: 4,

                                        colors: [

                                            isDarkMode()
                                                ? '#0f172a'
                                                : '#ffffff',

                                        ],

                                    },


                                    legend: {
                                        show: false,
                                    },


                                    dataLabels: {
                                        enabled: false,
                                    },


                                    plotOptions: {

                                        pie: {

                                            donut: {

                                                size:
                                                    '72%',


                                                labels: {

                                                    show:
                                                        true,


                                                    name: {

                                                        show:
                                                            true,

                                                        color:
                                                            textColor,

                                                        fontSize:
                                                            '12px',

                                                    },


                                                    value: {

                                                        show:
                                                            true,

                                                        color:
                                                            primaryTextColor,

                                                        fontSize:
                                                            '20px',

                                                        fontWeight:
                                                            700,


                                                        formatter:
                                                            value =>
                                                                '$' +
                                                                Number(
                                                                    value
                                                                ).toLocaleString(
                                                                    undefined,
                                                                    {
                                                                        maximumFractionDigits:
                                                                            0,
                                                                    }
                                                                ),

                                                    },


                                                    total: {

                                                        show:
                                                            true,

                                                        label:
                                                            'Total',

                                                        color:
                                                            textColor,


                                                        formatter:
                                                            w => {

                                                                const total =
                                                                    w
                                                                        .globals
                                                                        .seriesTotals
                                                                        .reduce(
                                                                            (
                                                                                a,
                                                                                b
                                                                            ) =>
                                                                                a + b,
                                                                            0
                                                                        );


                                                                return '$' +
                                                                    Number(
                                                                        total
                                                                    ).toLocaleString(
                                                                        undefined,
                                                                        {
                                                                            maximumFractionDigits:
                                                                                0,
                                                                        }
                                                                    );
                                                            },

                                                    },

                                                },

                                            },

                                        },

                                    },


                                    tooltip: {

                                        theme:
                                            chartTheme(),

                                        y: {

                                            formatter:
                                                value =>
                                                    '$' +
                                                    Number(
                                                        value
                                                    ).toLocaleString(
                                                        undefined,
                                                        {
                                                            minimumFractionDigits:
                                                                2,

                                                            maximumFractionDigits:
                                                                2,
                                                        }
                                                    ),

                                        },

                                    },


                                    noData: {
                                        text:
                                            'No revenue data',
                                    },

                                }
                            );


                        chart.render();

                        element.__apexChart =
                            chart;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Render Everything
                    |--------------------------------------------------------------------------
                    */

                    function renderAll() {

                        ensureApexCharts()
                            .then(() => {

                                requestAnimationFrame(
                                    () => {

                                        renderRevenueChart();

                                        renderCustomerChart();

                                        renderDonutChart();

                                    }
                                );

                            })
                            .catch(
                                error => {
                                    console.error(
                                        'Unable to load ApexCharts.',
                                        error
                                    );
                                }
                            );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Refresh After Livewire Morph
                    |--------------------------------------------------------------------------
                    */

                    function refreshAfterLivewire() {

                        requestAnimationFrame(() => {

                            requestAnimationFrame(() => {

                                renderAll();

                            });

                        });
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Initial Start
                    |--------------------------------------------------------------------------
                    */

                    function initialize() {

                        if (initialized) {
                            return;
                        }

                        initialized = true;

                        renderAll();
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Public API
                    |--------------------------------------------------------------------------
                    */

                    return {

                        setData,

                        initialize,

                        refreshAfterLivewire,

                    };

                })();

            }


            /*
            |--------------------------------------------------------------------------
            | Set Initial Data
            |--------------------------------------------------------------------------
            */

            window.TeamDashboardCharts
                .setData(initialData);


            /*
            |--------------------------------------------------------------------------
            | Dashboard Update Event
            |--------------------------------------------------------------------------
            |
            | Livewire dispatches:
            |
            | $this->dispatch(
            |     'dashboard-updated',
            |     data: $this->chartData()
            | );
            |
            |--------------------------------------------------------------------------
            */

            if (
                ! window.__teamDashboardFilterListener
            ) {

                window.__teamDashboardFilterListener =
                    true;


                window.addEventListener(
                    'dashboard-updated',
                    event => {

                        const data =
                            event.detail?.data
                            ?? null;


                        if (! data) {
                            return;
                        }


                        window.TeamDashboardCharts
                            .setData(data);


                        window.TeamDashboardCharts
                            .refreshAfterLivewire();

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Theme Observer
            |--------------------------------------------------------------------------
            */

            if (
                ! window.__teamDashboardThemeObserver
            ) {

                window.__teamDashboardThemeObserver =
                    new MutationObserver(() => {

                        clearTimeout(
                            window.__teamDashboardThemeTimeout
                        );


                        window.__teamDashboardThemeTimeout =
                            setTimeout(() => {

                                window.TeamDashboardCharts
                                    .refreshAfterLivewire();

                            }, 50);

                    });


                window.__teamDashboardThemeObserver
                    .observe(
                        document.documentElement,
                        {
                            attributes: true,

                            attributeFilter: [
                                'class',
                                'data-theme',
                            ],
                        }
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | Initialize Once
            |--------------------------------------------------------------------------
            */

            window.TeamDashboardCharts
                .initialize();

        })();
    </script>

</div>