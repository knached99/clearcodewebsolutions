<div class="dashboard-shell">
    @php
        /*
        |--------------------------------------------------------------------------
        | Revenue and Profit Formatting
        |--------------------------------------------------------------------------
        |
        | All financial amounts are stored in cents.
        |
        */

        $grossProfitDisplay = '$' . number_format(
            ($grossProfit ?? 0) / 100,
            2
        );

        $processingFeesDisplay = '$' . number_format(
            abs((int) ($processingFees ?? 0)) / 100,
            2
        );

        $netProfitDisplay = '$' . number_format(
            ($netProfit ?? 0) / 100,
            2
        );


        /*
        |--------------------------------------------------------------------------
        | Ownership Distribution
        |--------------------------------------------------------------------------
        |
        | Khaled = Owner
        | Karam  = Partner
        |
        | Net profit is divided 50/50 after Stripe processing fees.
        | No commission deductions are applied.
        |
        */

        $ownerShareAmount = (int) (
            $ownerShare ?? $khaledShare ?? 0
        );

        $partnerShareAmount = (int) (
            $partnerShare ?? $karamShare ?? 0
        );

        $distributionTotalAmount = (int) (
            $distributionTotal
            ?? ($ownerShareAmount + $partnerShareAmount)
        );


        /*
        |--------------------------------------------------------------------------
        | Ownership Display Values
        |--------------------------------------------------------------------------
        */

        $ownerShareDisplay = '$' . number_format(
            $ownerShareAmount / 100,
            2
        );

        $partnerShareDisplay = '$' . number_format(
            $partnerShareAmount / 100,
            2
        );

        $distributionTotalDisplay = '$' . number_format(
            $distributionTotalAmount / 100,
            2
        );


        /*
        |--------------------------------------------------------------------------
        | Losses and Recurring Revenue
        |--------------------------------------------------------------------------
        */

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
            $grossMarginPercentage ?? 0,
            2
        ) . '%';


        /*
        |--------------------------------------------------------------------------
        | Highest Profiting Customer
        |--------------------------------------------------------------------------
        */

        $highestCustomerName =
            $highestProfitingCustomer['name']
            ?? 'No customer data';

        $highestCustomerAmount = isset(
            $highestProfitingCustomer['amount']
        )
            ? '$' . number_format(
                $highestProfitingCustomer['amount'] / 100,
                2
            )
            : '$0.00';


        /*
        |--------------------------------------------------------------------------
        | Monthly Revenue Chart
        |--------------------------------------------------------------------------
        */

        $revenueLabels = collect($monthlyRevenue ?? [])
            ->pluck('month')
            ->values()
            ->all();

        $revenueValues = collect($monthlyRevenue ?? [])
            ->pluck('value')
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();


        /*
        |--------------------------------------------------------------------------
        | Customer Revenue Chart
        |--------------------------------------------------------------------------
        */

        $customerChartLabels = collect($customerRevenue ?? [])
            ->pluck('name')
            ->values()
            ->all();

        $customerChartValues = collect($customerRevenue ?? [])
            ->pluck('amount')
            ->map(fn ($value) => (int) $value)
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


        <div
        class="dashboard-filter-wrap"
        x-data="dashboardDatePicker({
            showCustom: @js($filter === 'custom'),
            startDate: @js($customStartDate),
            endDate: @js($customEndDate),
            filter: @js($filter),
        })"
    >
            <label for="dashboard-filter" class="dashboard-filter-label">
                Reporting period
            </label>

            <div class="dashboard-filter-control">

                <div class="dashboard-select-shell">

               <select
                id="dashboard-filter"
                class="metric-select dashboard-period-select"
                x-on:change="handleFilterChange($event.target.value)"
                x-bind:disabled="isApplying || isLoadingFilter"
            >
                <option value="max">
                    All Time
                </option>

                <option value="mtd">
                    Month to Date
                </option>

                <option value="3m">
                    Last 3 Months
                </option>

                <option value="6m">
                    Last 6 Months
                </option>

                <option value="ytd">
                    Year to Date
                </option>

                {{-- Actual option users select to open the date picker --}}
                <option value="custom">
                    Custom Range
                </option>

                {{-- Hidden selected option used after a custom range is applied --}}
                <option value="custom-active" hidden>
                    Custom Range
                </option>
            </select>

                    <svg
                        class="dashboard-select-chevron"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m6 9 6 6 6-6"
                        />
                    </svg>

                </div>


                {{-- Custom range picker --}}

                <div
                    x-show="showCustom"
                    x-cloak
                    x-transition:enter="custom-range-enter"
                    x-transition:enter-start="custom-range-enter-start"
                    x-transition:enter-end="custom-range-enter-end"
                    class="custom-range-panel"
                >

                    <div class="custom-range-header">

                        <div>

                            <span class="custom-range-eyebrow">
                                CUSTOM REPORTING PERIOD
                            </span>

                            <h4>
                                Choose your date range
                            </h4>

                        </div>

                        <button
                            type="button"
                            class="custom-range-close"
                            x-on:click="closeCustom()"
                            aria-label="Close date picker"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18 18 6M6 6l12 12"
                                />
                            </svg>
                        </button>

                    </div>


                    <div class="custom-range-fields">

                        <div class="custom-date-field">

                            <span>
                                FROM
                            </span>

                            <input
                                type="date"
                                x-model="startDate"
                                x-on:change="datesChanged()"
                            >

                        </div>


                        <div class="custom-range-arrow">
                            →
                        </div>


                        <div class="custom-date-field">

                            <span>
                                TO
                            </span>

                            <input
                                type="date"
                                x-model="endDate"
                                x-on:change="datesChanged()"
                            >

                        </div>

                    </div>


                   <div
                    x-show="dateError || requestError"
                    x-cloak
                    x-transition
                    class="custom-range-error"
                    role="alert"
                >
                    <span
                        x-text="
                            requestError
                                ? 'Unable to update the dashboard. Please try again.'
                                : 'Please choose a valid date range.'
                        "
                    ></span>
                </div>


                    <div class="custom-range-footer">

                        <div class="custom-range-preview">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 4h10m-9 5h4m-4 4h7M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"
                                />
                            </svg>

                            <span x-text="formattedRange"></span>

                        </div>


                       <button
                    type="button"
                    class="custom-range-apply"
                    x-on:click="applyRange()"
                    x-bind:disabled="!canApply"
                    x-bind:aria-busy="isApplying"
                >
                    <span
                        x-text="isApplying ? 'Applying...' : 'Apply Range'"
                    ></span>

                    <svg
                        x-show="!isApplying"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 12h14m-6-6 6 6-6 6"
                        />
                    </svg>

                    <svg
                        x-show="isApplying"
                        x-cloak
                        class="animate-spin"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                            stroke-opacity=".25"
                        />

                        <path
                            d="M21 12a9 9 0 0 0-9-9"
                            stroke-linecap="round"
                        />
                    </svg>
                </button>

                    </div>

                </div>

            </div>

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
                (NET) Annualized recurring revenue
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
                ${{ number_format(($averageInvoiceValue ?? 0) / 100, 2) }}
            </div>

        </article>


        <article class="secondary-card">

            <div class="secondary-label">
                Subscription revenue
            </div>

            <div class="secondary-value">
                ${{ number_format(($subscriptionRevenue ?? 0) / 100, 2) }}
            </div>

        </article>


        <article class="secondary-card">

            <div class="secondary-label">
                One-time revenue
            </div>

            <div class="secondary-value">
                ${{ number_format(($oneTimeRevenue ?? 0) / 100, 2) }}
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

        {{-- Header --}}

        <div class="profit-distribution-header">

            <div>

                <div class="chart-eyebrow">
                    Profit distribution
                </div>

                <h2>
                    Ownership breakdown
                </h2>

                <p>
                    Net profit is split equally between Khaled and Karam
                    after Stripe processing fees.
                </p>

            </div>

            <div class="chart-badge">
                50/50 ownership split
            </div>

        </div>


        {{-- Summary --}}

        <div class="distribution-summary">

            {{-- Distributable Net Profit --}}

            <div class="distribution-summary-item">

                <span class="distribution-summary-label">
                    Net profit available
                </span>

                <strong class="distribution-summary-value">
                    {{ $distributionTotalDisplay }}
                </strong>

                <span class="distribution-summary-description">
                    After Stripe processing fees
                </span>

            </div>


            <div class="distribution-summary-divider"></div>


            {{-- Ownership Split --}}

            <div class="distribution-summary-item">

                <span class="distribution-summary-label">
                    Ownership split
                </span>

                <strong class="distribution-summary-value">
                    50% / 50%
                </strong>

                <span class="distribution-summary-description">
                    Khaled (owner) and Karam (partner)
                </span>

            </div>

        </div>


        {{-- =====================================================
             DISTRIBUTION RECIPIENTS
        ====================================================== --}}

        <div class="distribution-grid">

            {{-- =================================================
                 KHALED — OWNER
            ================================================== --}}

            <article class="distribution-recipient distribution-recipient-khaled">

                <div class="distribution-recipient-top">

                    <div class="distribution-recipient-icon">
                        K
                    </div>

                    <span class="distribution-recipient-badge">
                        Owner
                    </span>

                </div>


                <div class="distribution-recipient-name">
                    Khaled
                </div>


                <div class="distribution-recipient-amount">
                    {{ $ownerShareDisplay }}
                </div>


                <div class="distribution-recipient-description">
                    50% of net profit
                </div>


                <div class="distribution-recipient-rule">
                    After Stripe processing fees
                </div>

            </article>


            {{-- =================================================
                 KARAM — PARTNER
            ================================================== --}}

            <article class="distribution-recipient distribution-recipient-karam">

                <div class="distribution-recipient-top">

                    <div class="distribution-recipient-icon">
                        K
                    </div>

                    <span class="distribution-recipient-badge">
                        Partner
                    </span>

                </div>


                <div class="distribution-recipient-name">
                    Karam
                </div>


                <div class="distribution-recipient-amount">
                    {{ $partnerShareDisplay }}
                </div>


                <div class="distribution-recipient-description">
                    50% of net profit
                </div>


                <div class="distribution-recipient-rule">
                    After Stripe processing fees
                </div>

            </article>

        </div>


        {{-- =====================================================
             DISTRIBUTION TOTAL
        ====================================================== --}}

        <div class="distribution-footer">

            <div class="distribution-footer-label">
                Total allocated
            </div>


            <div class="distribution-footer-value">
                {{ $distributionTotalDisplay }}
            </div>


            <div class="distribution-footer-description">
                Khaled's owner share + Karam's partner share
            </div>

        </div>

    </section>

    {{-- =========================================================
         PAYMENT MAP
    ========================================================== --}}

    <livewire:teams.team-dashboard-map
        :team="$team"
        :filter="$filter"
        :custom-start-date="$customStartDate"
        :custom-end-date="$customEndDate"
    />


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


            @if (empty($customerRevenue))

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
                            ${{ number_format(($subscriptionRevenue ?? 0) / 100, 2) }}
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
                            ${{ number_format(($oneTimeRevenue ?? 0) / 100, 2) }}
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

            background:
                var(--dashboard-bg);

            color:
                var(--dashboard-text);

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

            color:
                var(--dashboard-primary);

            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            line-height: 1;
            text-transform: uppercase;
        }


        .dashboard-header h1 {
            margin: 0;

            color:
                var(--dashboard-text);

            font-size:
                clamp(28px, 4vw, 38px);

            font-weight: 750;
            letter-spacing: -0.035em;
            line-height: 1.1;
        }


        .dashboard-header p {
            margin: 9px 0 0;

            color:
                var(--dashboard-muted);

            font-size: 14px;
            line-height: 1.5;
        }


        .dashboard-filter-wrap {
            min-width: 175px;
            position: relative;

            display: flex;
            flex-direction: column;

            gap: 7px;
        }


        .dashboard-filter-wrap label {
            display: block;

            margin-bottom: 0;

            color:
                var(--dashboard-muted);

            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }


        .dashboard-filter-control {
            position: relative;
        }


        .dashboard-select-shell {
            position: relative;

            display: flex;
            align-items: center;
        }


        .dashboard-period-select {
            width: 100%;
            height: 46px;

            padding: 0 42px 0 42px;

            appearance: none;
            -webkit-appearance: none;

            border: 1px solid rgba(139, 92, 246, 0.25);
            border-radius: 13px;

            background:
                linear-gradient(
                    135deg,
                    rgba(139, 92, 246, 0.08),
                    rgba(139, 92, 246, 0.025)
                );

            color:
                var(--dashboard-text);

            font-size: 14px;
            font-weight: 600;

            outline: none;
            cursor: pointer;

            transition:
                border-color 180ms ease,
                box-shadow 180ms ease,
                background 180ms ease;
        }


        .dashboard-period-select:hover {
            border-color:
                rgba(139, 92, 246, 0.5);
        }


        .dashboard-period-select:focus {
            border-color:
                #8b5cf6;

            box-shadow:
                0 0 0 3px rgba(139, 92, 246, 0.12),
                0 8px 25px rgba(0, 0, 0, 0.12);
        }


        .dashboard-select-chevron {
            position: absolute;

            right: 14px;

            width: 16px;
            height: 16px;

            color: #8b8f98;

            pointer-events: none;
        }


        .custom-range-panel {
            position: absolute;
            z-index: 100;

            top: calc(100% + 10px);
            right: 0;

            width:
                min(460px, calc(100vw - 32px));

            padding: 20px;

            border: 1px solid rgba(139, 92, 246, 0.22);
            border-radius: 20px;

            background:
                linear-gradient(
                    145deg,
                    rgba(25, 25, 32, 0.98),
                    rgba(16, 16, 21, 0.98)
                );

            box-shadow:
                0 25px 70px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.025),
                0 0 45px rgba(139, 92, 246, 0.08);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }


        .custom-range-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;

            margin-bottom: 20px;
        }


        .custom-range-eyebrow {
            display: block;

            margin-bottom: 5px;

            color: #8b5cf6;

            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.12em;
        }


        .custom-range-header h4 {
            margin: 0;

            color: #fff;

            font-size: 17px;
            font-weight: 700;
        }


        .custom-range-close {
            display: flex;
            align-items: center;
            justify-content: center;

            width: 32px;
            height: 32px;

            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;

            background: rgba(255, 255, 255, 0.035);

            color: #8b8f98;

            cursor: pointer;

            transition:
                background 160ms ease,
                color 160ms ease,
                border-color 160ms ease;
        }


        .custom-range-close svg {
            width: 15px;
            height: 15px;
        }


        .custom-range-close:hover {
            border-color:
                rgba(139, 92, 246, 0.35);

            background:
                rgba(139, 92, 246, 0.1);

            color: #fff;
        }


        .custom-range-fields {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr)
                auto
                minmax(0, 1fr);

            align-items: center;

            gap: 10px;
        }


        .custom-date-field {
            display: flex;
            flex-direction: column;

            gap: 7px;
        }


        .custom-date-field > span {
            color: #777b85;

            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.1em;
        }


        .custom-date-field input {
            width: 100%;
            height: 45px;

            padding: 0 11px;

            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 11px;

            background:
                rgba(255, 255, 255, 0.045);

            color: #fff;

            font-size: 13px;
            font-weight: 600;

            outline: none;

            color-scheme: dark;

            transition:
                border-color 160ms ease,
                background 160ms ease,
                box-shadow 160ms ease;
        }


        .custom-date-field input:hover {
            border-color:
                rgba(139, 92, 246, 0.35);
        }


        .custom-date-field input:focus {
            border-color:
                #8b5cf6;

            background:
                rgba(139, 92, 246, 0.06);

            box-shadow:
                0 0 0 3px rgba(139, 92, 246, 0.1);
        }


        .custom-range-arrow {
            margin-top: 19px;

            color: #676b75;

            font-size: 18px;
        }


        .custom-range-error {
            margin-top: 10px;

            padding: 9px 11px;

            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 9px;

            background:
                rgba(239, 68, 68, 0.06);

            color: #f87171;

            font-size: 12px;
            font-weight: 600;
        }


        .custom-range-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 14px;

            margin-top: 20px;
            padding-top: 16px;

            border-top:
                1px solid rgba(255, 255, 255, 0.07);
        }


        .custom-range-preview {
            display: flex;
            align-items: center;

            min-width: 0;

            gap: 8px;

            color: #8f939d;

            font-size: 11px;
            font-weight: 600;
        }


        .custom-range-preview svg {
            flex: 0 0 auto;

            width: 16px;
            height: 16px;

            color: #8b5cf6;
        }


        .custom-range-preview span {
            overflow: hidden;

            text-overflow: ellipsis;
            white-space: nowrap;
        }


        .custom-range-apply {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            flex: 0 0 auto;

            gap: 7px;

            height: 39px;

            padding: 0 14px;

            border: 0;
            border-radius: 10px;

            background:
                linear-gradient(
                    135deg,
                    #8b5cf6,
                    #7c3aed
                );

            color: #fff;

            font-size: 12px;
            font-weight: 700;

            cursor: pointer;

            box-shadow:
                0 7px 20px rgba(124, 58, 237, 0.2);

            transition:
                transform 160ms ease,
                box-shadow 160ms ease,
                opacity 160ms ease;
        }


        .custom-range-apply svg {
            width: 14px;
            height: 14px;
        }


        .custom-range-apply:hover:not(:disabled) {
            transform: translateY(-1px);

            box-shadow:
                0 10px 26px rgba(124, 58, 237, 0.3);
        }


        .custom-range-apply:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            box-shadow: none;
        }


        .custom-range-enter {
            transition:
                opacity 180ms ease,
                transform 180ms ease;
        }


        .custom-range-enter-start {
            opacity: 0;
            transform: translateY(-7px) scale(0.98);
        }


        .custom-range-enter-end {
            opacity: 1;
            transform: translateY(0) scale(1);
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
            background:
                var(--dashboard-success-soft);

            border-color:
                #a7f3d0;

            color:
                #047857;
        }


        .dark .status-pill-success {
            border-color:
                rgba(52, 211, 153, 0.20);

            color:
                #6ee7b7;
        }


        .status-pill-success .status-dot {
            background:
                #10b981;
        }


        .status-pill-primary {
            background:
                var(--dashboard-primary-soft);

            border-color:
                #ddd6fe;

            color:
                #6d28d9;
        }


        .dark .status-pill-primary {
            border-color:
                rgba(167, 139, 250, 0.20);

            color:
                #c4b5fd;
        }


        .status-pill-primary .status-dot {
            background:
                #8b5cf6;
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

            box-shadow:
                var(--dashboard-shadow-hover);
        }


        .metric-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 12px;
        }


        .metric-label {
            color:
                var(--dashboard-muted);

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
            background:
                var(--dashboard-primary-soft);

            color:
                var(--dashboard-primary);
        }


        .metric-icon-orange {
            background:
                var(--dashboard-warning-soft);

            color:
                var(--dashboard-warning);
        }


        .metric-icon-green {
            background:
                var(--dashboard-success-soft);

            color:
                var(--dashboard-success);
        }


        .metric-icon-blue {
            background:
                var(--dashboard-blue-soft);

            color:
                var(--dashboard-blue);
        }


        .metric-icon-cyan {
            background:
                var(--dashboard-cyan-soft);

            color:
                var(--dashboard-cyan);
        }


        .metric-icon-red {
            background:
                var(--dashboard-danger-soft);

            color:
                var(--dashboard-danger);
        }


        .metric-value {
            margin-top: 17px;

            overflow: hidden;

            color:
                var(--dashboard-text);

            font-size:
                clamp(22px, 2.3vw, 29px);

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

            color:
                var(--dashboard-subtle);

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

            background:
                var(--dashboard-surface-soft);

            color:
                var(--dashboard-text);
        }


        .secondary-label {
            color:
                var(--dashboard-muted);

            font-size: 11px;
            font-weight: 650;
        }


        .secondary-value {
            margin-top: 7px;

            color:
                var(--dashboard-text);

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

            background:
                var(--dashboard-surface);

            color:
                var(--dashboard-text);

            box-shadow:
                var(--dashboard-shadow);
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

            color:
                var(--dashboard-subtle);

            font-size: 10px;
            font-weight: 750;
            letter-spacing: 0.09em;
            text-transform: uppercase;
        }


        .chart-header h2 {
            margin: 0;

            color:
                var(--dashboard-text);

            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.015em;
        }


        .chart-badge {
            flex: 0 0 auto;

            padding: 6px 9px;

            border: 1px solid var(--dashboard-border);
            border-radius: 8px;

            background:
                var(--dashboard-surface-soft);

            color:
                var(--dashboard-muted);

            font-size: 10px;
            font-weight: 700;
        }


        .chart-container {
            width: 100%;
            min-width: 0;
            min-height: 320px;
        }


        .chart-container .apexcharts-canvas,
        .chart-container svg {
            max-width: 100% !important;
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

            background:
                var(--dashboard-primary-soft);

            color:
                var(--dashboard-primary);

            font-size: 20px;
            font-weight: 800;
        }


        .chart-empty strong {
            color:
                var(--dashboard-text);

            font-size: 14px;
        }


        .chart-empty span {
            max-width: 280px;

            margin-top: 6px;

            color:
                var(--dashboard-muted);

            font-size: 12px;
            line-height: 1.5;
        }


        /* =====================================================================
           DONUT CHART
        ====================================================================== */

        .chart-card-split {
            margin-bottom: 10px;

            min-width: 0;

            overflow: hidden;
        }


        .donut-layout {
            display: grid;

            grid-template-columns:
                minmax(0, 1.15fr)
                minmax(180px, 0.85fr);

            align-items: center;

            gap: 24px;

            width: 100%;
            min-width: 0;
        }


        .donut-chart {
            width: 100%;
            min-width: 0;

            min-height: 300px;

            max-width: 100%;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;
        }


        .donut-chart .apexcharts-canvas {
            width: 100% !important;
            max-width: 100% !important;
        }


        .donut-chart svg {
            max-width: 100% !important;
        }


        .donut-legend {
            display: flex;
            flex-direction: column;

            gap: 18px;

            min-width: 0;
            width: 100%;
        }


        .legend-item {
            display: flex;
            align-items: flex-start;

            gap: 11px;

            min-width: 0;
        }


        .legend-color {
            width: 10px;
            height: 10px;

            flex: 0 0 10px;

            margin-top: 4px;

            border-radius: 999px;
        }


        .legend-color-subscription {
            background:
                #7c3aed;
        }


        .legend-color-onetime {
            background:
                #0ea5e9;
        }


        .legend-item div {
            display: flex;
            flex-direction: column;

            gap: 3px;

            min-width: 0;
        }


        .legend-item strong {
            color:
                var(--dashboard-text);

            font-size: 13px;
            font-weight: 650;
        }


        .legend-item span:not(.legend-color) {
            color:
                var(--dashboard-muted);

            font-size: 12px;
        }


        /* =====================================================================
           PROFIT DISTRIBUTION
        ====================================================================== */

        .profit-distribution-card {
            min-width: 0;

            margin-bottom: 22px;
            padding: 24px;

            border: 1px solid var(--dashboard-border);
            border-radius: 17px;

            background:
                var(--dashboard-surface);

            color:
                var(--dashboard-text);

            box-shadow:
                var(--dashboard-shadow);
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

            color:
                var(--dashboard-text);

            font-size: 19px;
            font-weight: 750;
            letter-spacing: -0.025em;
        }


        .profit-distribution-header p {
            margin: 8px 0 0;

            color:
                var(--dashboard-muted);

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

            background:
                var(--dashboard-surface-soft);
        }


        .distribution-summary-item {
            display: flex;

            min-width: 0;

            flex-direction: column;

            gap: 5px;
        }


        .distribution-summary-label {
            color:
                var(--dashboard-muted);

            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }


        .distribution-summary-value {
            color:
                var(--dashboard-text);

            font-size:
                clamp(22px, 3vw, 30px);

            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.15;
        }


        .distribution-summary-description {
            color:
                var(--dashboard-subtle);

            font-size: 11px;
            line-height: 1.4;
        }


        .distribution-summary-divider {
            width: 1px;
            height: 65px;

            background:
                var(--dashboard-border);
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

            background:
                var(--dashboard-surface-soft);

            transition:
                transform 180ms ease,
                box-shadow 180ms ease,
                border-color 180ms ease;
        }


        .distribution-recipient:hover {
            transform: translateY(-2px);

            border-color:
                rgba(124, 58, 237, 0.25);

            box-shadow:
                var(--dashboard-shadow-hover);
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

            color:
                var(--dashboard-muted);

            font-size: 10px;
            font-weight: 700;
        }


        .distribution-recipient-name {
            margin-top: 19px;

            color:
                var(--dashboard-muted);

            font-size: 12px;
            font-weight: 700;
        }


        .distribution-recipient-amount {
            margin-top: 7px;

            overflow: hidden;

            color:
                var(--dashboard-text);

            font-size:
                clamp(23px, 3vw, 32px);

            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.15;

            text-overflow: ellipsis;
            white-space: nowrap;
        }


        .distribution-recipient-description {
            margin-top: 8px;

            color:
                var(--dashboard-muted);

            font-size: 11px;
            line-height: 1.45;
        }


        .distribution-recipient-rule {
            margin-top: 15px;
            padding-top: 12px;

            border-top:
                1px solid var(--dashboard-border);

            color:
                var(--dashboard-subtle);

            font-size: 10px;
            line-height: 1.45;
        }


        .distribution-recipient-pablo .distribution-recipient-icon {
            background:
                var(--dashboard-warning-soft);

            color:
                var(--dashboard-warning);
        }


        .distribution-recipient-khaled .distribution-recipient-icon {
            background:
                var(--dashboard-primary-soft);

            color:
                var(--dashboard-primary);
        }


        .distribution-recipient-karam .distribution-recipient-icon {
            background:
                var(--dashboard-blue-soft);

            color:
                var(--dashboard-blue);
        }


        .distribution-footer {
            display: flex;
            align-items: baseline;

            flex-wrap: wrap;

            gap: 9px;

            margin-top: 18px;
            padding-top: 17px;

            border-top:
                1px solid var(--dashboard-border);
        }


        .distribution-footer-label {
            color:
                var(--dashboard-muted);

            font-size: 12px;
            font-weight: 700;
        }


        .distribution-footer-value {
            color:
                var(--dashboard-text);

            font-size: 17px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }


        .distribution-footer-description {
            color:
                var(--dashboard-subtle);

            font-size: 11px;
        }


        /* =====================================================================
           RESPONSIVE
        ====================================================================== */

        @media (max-width: 1200px) {

            .metrics-grid {
                grid-template-columns:
                    repeat(3, minmax(0, 1fr));
            }


            .charts-grid {
                grid-template-columns:
                    1fr;
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
                grid-template-columns:
                    1fr;

                gap: 18px;
            }


            .donut-chart {
                width: 100%;
                min-height: 280px;
            }


            .donut-legend {
                width: 100%;
            }


            .distribution-grid {
                grid-template-columns:
                    1fr;
            }

        }


        @media (max-width: 640px) {

            .dashboard-shell {
                padding:
                    20px 14px;
            }


            .dashboard-header {
                align-items: stretch;
                flex-direction: column;
            }


            .dashboard-filter-wrap {
                width: 100%;
                min-width: 0;
            }


            .dashboard-period-select {
                min-width: 0;
            }


            .metrics-grid {
                grid-template-columns:
                    1fr;
            }


            .secondary-grid {
                grid-template-columns:
                    1fr;
            }


            .chart-card {
                padding: 16px;
            }


            .chart-header {
                flex-direction: column;
                gap: 10px;
            }


            .chart-badge {
                align-self: flex-start;
            }


            .chart-container {
                min-height: 270px;
            }


            .donut-layout {
                display: flex;

                flex-direction: column;

                align-items: stretch;

                width: 100%;

                gap: 16px;
            }


            .donut-chart {
                width: 100%;

                height: 250px;

                min-height: 250px;

                max-height: 250px;

                display: flex;

                align-items: center;
                justify-content: center;

                overflow: hidden;
            }


            .donut-chart .apexcharts-canvas {
                width: 100% !important;
                max-width: 100% !important;
            }


            .donut-chart svg {
                max-width: 100% !important;
            }


            .donut-legend {
                width: 100%;

                gap: 8px;
            }


            .legend-item {
                width: 100%;

                padding:
                    8px 0;
            }


            .legend-item strong {
                font-size: 12px;
            }


            .legend-item span:not(.legend-color) {
                font-size: 11px;
            }


            .profit-distribution-card {
                padding: 17px;
            }


            .profit-distribution-header {
                flex-direction: column;

                gap: 13px;
            }


            .distribution-summary {
                grid-template-columns:
                    1fr;

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


            .custom-range-panel {
                position: fixed;

                top: auto;
                right: 16px;
                bottom: 16px;
                left: 16px;

                width: auto;
            }


            .custom-range-fields {
                grid-template-columns:
                    1fr;
            }


            .custom-range-arrow {
                display: none;
            }


            .custom-range-footer {
                align-items: stretch;

                flex-direction: column;
            }


            .custom-range-apply {
                width: 100%;
            }

        }

    </style>


    {{-- =========================================================
         APEXCHARTS ASSET
    ========================================================== --}}

    @assets

        <script
            src="https://cdn.jsdelivr.net/npm/apexcharts"
            defer
        ></script>

    @endassets


    {{-- =========================================================
         APEXCHARTS / LIVEWIRE BRIDGE
    ========================================================== --}}

    @script

        <script>
            (() => {

                const initialData = {

                    revenue: {

                        labels:
                            @js($revenueLabels),

                        values:
                            @js($revenueValues),

                    },


                    customers: {

                        labels:
                            @js($customerChartLabels),

                        values:
                            @js($customerChartValues),

                    },


                    donut: {

                        subscription:
                            {{ (int) ($subscriptionRevenue ?? 0) }},

                        oneTime:
                            {{ (int) ($oneTimeRevenue ?? 0) }},

                    },

                };


                /*
                |--------------------------------------------------------------------------
                | Global chart manager
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


                        let renderFrameOne = null;
                        let renderFrameTwo = null;
                        let apexWaitTimer = null;
                        let resizeTimeout = null;


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
                                            data.donut?.subscription ?? 0
                                        ),


                                    oneTime:
                                        Number(
                                            data.donut?.oneTime ?? 0
                                        ),

                                },

                            };

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Wait for ApexCharts
                        |--------------------------------------------------------------------------
                        */

                        function waitForApexCharts(callback) {

                            if (
                                typeof window.ApexCharts !==
                                'undefined'
                            ) {

                                callback();

                                return;

                            }


                            clearTimeout(
                                apexWaitTimer
                            );


                            apexWaitTimer =
                                setTimeout(() => {

                                    if (
                                        typeof window.ApexCharts !==
                                        'undefined'
                                    ) {

                                        callback();

                                        return;

                                    }


                                    waitForApexCharts(
                                        callback
                                    );

                                }, 50);

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Destroy chart
                        |--------------------------------------------------------------------------
                        */

                        function destroyChart(id) {

                            const element =
                                document.getElementById(
                                    id
                                );


                            if (!element) {
                                return;
                            }


                            if (
                                element.__apexChart &&
                                typeof element.__apexChart.destroy ===
                                    'function'
                            ) {

                                try {

                                    element.__apexChart.destroy();

                                } catch (error) {
                                    // Ignore cleanup errors.
                                }

                            }


                            element.__apexChart =
                                null;


                            element.innerHTML =
                                '';

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
                                !element ||
                                typeof window.ApexCharts ===
                                    'undefined'
                            ) {
                                return;
                            }


                            destroyChart(
                                'revenueChart'
                            );


                            const labels =
                                dashboardData.revenue?.labels ??
                                [];


                            const values =
                                dashboardData.revenue?.values ??
                                [];


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
                                                'area',

                                            height:
                                                320,

                                            width:
                                                '100%',

                                            toolbar: {
                                                show:
                                                    false,
                                            },

                                            zoom: {
                                                enabled:
                                                    false,
                                            },

                                            redrawOnWindowResize:
                                                true,

                                            redrawOnParentResize:
                                                true,

                                            parentHeightOffset:
                                                0,

                                            fontFamily:
                                                'inherit',

                                            background:
                                                'transparent',

                                            animations: {
                                                enabled:
                                                    true,
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
                                                show:
                                                    false,
                                            },


                                            axisTicks: {
                                                show:
                                                    false,
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

                                            width:
                                                3,

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

                                                stops:
                                                    [
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

                                                    show:
                                                        false,

                                                },

                                            },

                                        },


                                        dataLabels: {
                                            enabled:
                                                false,
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
                                !element ||
                                typeof window.ApexCharts ===
                                    'undefined'
                            ) {
                                return;
                            }


                            destroyChart(
                                'customerChart'
                            );


                            const categories =
                                dashboardData.customers?.labels ??
                                [];


                            const values =
                                dashboardData.customers?.values ??
                                [];


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

                                            width:
                                                '100%',

                                            toolbar: {
                                                show:
                                                    false,
                                            },

                                            redrawOnWindowResize:
                                                true,

                                            redrawOnParentResize:
                                                true,

                                            parentHeightOffset:
                                                0,

                                            fontFamily:
                                                'inherit',

                                            background:
                                                'transparent',

                                            animations: {
                                                enabled:
                                                    true,
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
                                                show:
                                                    false,
                                            },


                                            axisTicks: {
                                                show:
                                                    false,
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
                                            enabled:
                                                false,
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
                                !element ||
                                typeof window.ApexCharts ===
                                    'undefined'
                            ) {
                                return;
                            }


                            destroyChart(
                                'donutChart'
                            );


                            const subscriptionRevenue =
                                Number(
                                    dashboardData.donut?.subscription ??
                                    0
                                ) / 100;


                            const oneTimeRevenue =
                                Number(
                                    dashboardData.donut?.oneTime ??
                                    0
                                ) / 100;


                            const textColor =
                                chartTextColor();


                            const primaryTextColor =
                                chartPrimaryTextColor();


                            const mobile =
                                window.innerWidth <= 640;


                            const tablet =
                                window.innerWidth <= 900;


                            const chartHeight =
                                mobile
                                    ? 250
                                    : tablet
                                        ? 280
                                        : 300;


                            const customScale =
                                mobile
                                    ? 0.78
                                    : tablet
                                        ? 0.88
                                        : 1;


                            const chart =
                                new window.ApexCharts(
                                    element,
                                    {

                                        chart: {

                                            type:
                                                'donut',

                                            width:
                                                '100%',

                                            height:
                                                chartHeight,

                                            toolbar: {
                                                show:
                                                    false,
                                            },

                                            parentHeightOffset:
                                                0,

                                            redrawOnWindowResize:
                                                true,

                                            redrawOnParentResize:
                                                true,

                                            fontFamily:
                                                'inherit',

                                            background:
                                                'transparent',

                                            animations: {
                                                enabled:
                                                    true,
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

                                            width:
                                                4,

                                            colors: [

                                                isDarkMode()
                                                    ? '#0f172a'
                                                    : '#ffffff',

                                            ],

                                        },


                                        legend: {

                                            show:
                                                false,

                                        },


                                        dataLabels: {

                                            enabled:
                                                false,

                                        },


                                        plotOptions: {

                                            pie: {

                                                expandOnClick:
                                                    false,

                                                customScale:
                                                    customScale,


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
                                                                mobile
                                                                    ? '11px'
                                                                    : '12px',

                                                        },


                                                        value: {

                                                            show:
                                                                true,

                                                            color:
                                                                primaryTextColor,

                                                            fontSize:
                                                                mobile
                                                                    ? '18px'
                                                                    : '20px',

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
                                                                        w.globals
                                                                            .seriesTotals
                                                                            .reduce(
                                                                                (
                                                                                    a,
                                                                                    b
                                                                                ) =>
                                                                                    a +
                                                                                    b,

                                                                                0
                                                                            );


                                                                    return (
                                                                        '$' +
                                                                        Number(
                                                                            total
                                                                        ).toLocaleString(
                                                                            undefined,
                                                                            {
                                                                                maximumFractionDigits:
                                                                                    0,
                                                                            }
                                                                        )
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


                                        responsive: [

                                            {

                                                breakpoint:
                                                    640,

                                                options: {

                                                    chart: {

                                                        height:
                                                            250,

                                                    },


                                                    plotOptions: {

                                                        pie: {

                                                            customScale:
                                                                0.78,

                                                        },

                                                    },

                                                },

                                            },


                                            {

                                                breakpoint:
                                                    900,

                                                options: {

                                                    chart: {

                                                        height:
                                                            280,

                                                    },


                                                    plotOptions: {

                                                        pie: {

                                                            customScale:
                                                                0.88,

                                                        },

                                                    },

                                                },

                                            },

                                        ],


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
                        | Render all
                        |--------------------------------------------------------------------------
                        */

                        function renderAll() {

                            if (
                                typeof window.ApexCharts ===
                                'undefined'
                            ) {
                                return;
                            }


                            renderRevenueChart();

                            renderCustomerChart();

                            renderDonutChart();

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Safe refresh
                        |--------------------------------------------------------------------------
                        */

                        function refreshAfterLivewire() {

                            if (
                                renderFrameOne !==
                                null
                            ) {

                                cancelAnimationFrame(
                                    renderFrameOne
                                );

                            }


                            if (
                                renderFrameTwo !==
                                null
                            ) {

                                cancelAnimationFrame(
                                    renderFrameTwo
                                );

                            }


                            renderFrameOne =
                                requestAnimationFrame(() => {

                                    renderFrameTwo =
                                        requestAnimationFrame(() => {

                                            waitForApexCharts(
                                                renderAll
                                            );

                                        });

                                });

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Resize
                        |--------------------------------------------------------------------------
                        */

                        function refreshAfterResize() {

                            clearTimeout(
                                resizeTimeout
                            );


                            resizeTimeout =
                                setTimeout(() => {

                                    refreshAfterLivewire();

                                }, 100);

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Public API
                        |--------------------------------------------------------------------------
                        */

                        return {

                            setData,

                            refreshAfterLivewire,

                            refreshAfterResize,

                        };

                    })();

                }


                /*
                |--------------------------------------------------------------------------
                | Initial data
                |--------------------------------------------------------------------------
                */

                window.TeamDashboardCharts
                    .setData(
                        initialData
                    );


                /*
                |--------------------------------------------------------------------------
                | Livewire update event
                |--------------------------------------------------------------------------
                */

                if (
                    !window.__teamDashboardFilterListener
                ) {

                    window.__teamDashboardFilterListener =
                        true;


                    window.addEventListener(
                        'dashboard-updated',
                        event => {

                            const data =
                                event.detail?.data ??
                                null;


                            if (!data) {
                                return;
                            }


                            window.TeamDashboardCharts
                                .setData(
                                    data
                                );


                            window.TeamDashboardCharts
                                .refreshAfterLivewire();

                        }
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Theme observer
                |--------------------------------------------------------------------------
                */

                if (
                    !window.__teamDashboardThemeObserver
                ) {

                    window.__teamDashboardThemeObserver =
                        new MutationObserver(
                            () => {

                                clearTimeout(
                                    window.__teamDashboardThemeTimeout
                                );


                                window.__teamDashboardThemeTimeout =
                                    setTimeout(() => {

                                        window.TeamDashboardCharts
                                            ?.refreshAfterLivewire();

                                    }, 50);

                            }
                        );


                    window.__teamDashboardThemeObserver
                        .observe(
                            document.documentElement,
                            {
                                attributes:
                                    true,

                                attributeFilter: [
                                    'class',
                                    'data-theme',
                                ],
                            }
                        );

                }


                /*
                |--------------------------------------------------------------------------
                | Resize listener
                |--------------------------------------------------------------------------
                */

                if (
                    !window.__teamDashboardChartsResizeListener
                ) {

                    window.__teamDashboardChartsResizeListener =
                        true;


                    window.addEventListener(
                        'resize',
                        () => {

                            window.TeamDashboardCharts
                                ?.refreshAfterResize();

                        }
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Initial render
                |--------------------------------------------------------------------------
                */

                window.TeamDashboardCharts
                    .refreshAfterLivewire();

            })();
        </script>

    @endscript


    {{-- =========================================================
         ALPINE DATE PICKER
    ========================================================== --}}

 @script
<script>
    if (!window.__teamDashboardDatePickerRegistered) {
        Alpine.data('dashboardDatePicker', (initial = {}) => ({
            /*
            |--------------------------------------------------------------------------
            | State
            |--------------------------------------------------------------------------
            */

            showCustom: Boolean(initial.showCustom),

            startDate: initial.startDate ?? '',

            endDate: initial.endDate ?? '',

            currentFilter: initial.filter ?? 'max',

            lastNonCustomFilter:
                initial.filter && initial.filter !== 'custom'
                    ? initial.filter
                    : null,

            dateError: false,

            requestError: false,

            isApplying: false,

            isLoadingFilter: false,


            /*
            |--------------------------------------------------------------------------
            | Initialize
            |--------------------------------------------------------------------------
            */

            init() {
                this.$watch('startDate', () => {
                    this.validateDates();
                });

                this.$watch('endDate', () => {
                    this.validateDates();
                });

                this.validateDates();

                /*
                 * Synchronize after Alpine has initialized the DOM.
                 * This also restores the correct dropdown state after
                 * Livewire updates the component.
                 */

                this.$nextTick(() => {
                    this.syncSelectValue(this.currentFilter);
                });
            },


            /*
            |--------------------------------------------------------------------------
            | Ensure Reusable Custom Range Option Exists
            |--------------------------------------------------------------------------
            |
            | When custom-active is selected, the dropdown still displays
            | "Custom Range". The visible "custom" option remains selectable,
            | allowing the user to open the picker repeatedly.
            |
            | Create the hidden option dynamically so the Blade option list
            | does not need an additional option.
            |
            */

            ensureCustomActiveOption() {
                const select = document.getElementById(
                    'dashboard-filter'
                );

                if (!select) {
                    return null;
                }

                let option = select.querySelector(
                    'option[value="custom-active"]'
                );

                if (!option) {
                    option = document.createElement('option');

                    option.value = 'custom-active';

                    option.textContent = 'Custom Range';

                    option.hidden = true;

                    option.setAttribute('aria-hidden', 'true');

                    select.appendChild(option);
                }

                return select;
            },


            /*
            |--------------------------------------------------------------------------
            | Synchronize Dropdown Value
            |--------------------------------------------------------------------------
            */

            syncSelectValue(filter) {
                const select = this.ensureCustomActiveOption();

                if (!select) {
                    return;
                }

                /*
                 * Use the hidden option when custom reporting is active.
                 * Its label is still "Custom Range".
                 */

                select.value = filter === 'custom'
                    ? 'custom-active'
                    : filter;

                /*
                 * If the requested option does not exist, retain a valid
                 * selection rather than leaving the dropdown inconsistent.
                 */

                if (select.value === '' && select.options.length > 0) {
                    select.selectedIndex = 0;
                }
            },


            /*
            |--------------------------------------------------------------------------
            | Reporting Period Changed
            |--------------------------------------------------------------------------
            |
            | Custom Range opens the picker without triggering a request.
            | Standard ranges update the dashboard immediately.
            |
            */

            async handleFilterChange(value) {
                this.dateError = false;

                this.requestError = false;

                /*
                 * Opening Custom Range must not trigger a Livewire request.
                 */

                if (value === 'custom') {
                    if (this.isApplying || this.isLoadingFilter) {
                        this.syncSelectValue(this.currentFilter);

                        return;
                    }

                    this.showCustom = true;

                    /*
                     * Re-arm the native dropdown. The selected value becomes
                     * custom-active, so selecting the visible custom option
                     * will fire change again next time.
                     */

                    this.syncSelectValue('custom');

                    return;
                }

                /*
                 * Ignore attempts to change filters while a request is
                 * already running.
                 */

                if (this.isLoadingFilter || this.isApplying) {
                    this.syncSelectValue(this.currentFilter);

                    return;
                }

                const previousFilter = this.currentFilter;

                const previousLastNonCustomFilter =
                    this.lastNonCustomFilter;

                this.showCustom = false;

                this.isLoadingFilter = true;

                try {
                    /*
                     * Apply the standard reporting period once.
                     */

                    await this.$wire.setFilter(value);

                    this.currentFilter = value;

                    this.lastNonCustomFilter = value;

                    this.syncSelectValue(value);
                } catch (error) {
                    console.error(
                        'Failed to update the reporting period:',
                        error
                    );

                    /*
                     * Restore the previous dropdown state if the request
                     * fails.
                     */

                    this.currentFilter = previousFilter;

                    this.lastNonCustomFilter =
                        previousLastNonCustomFilter;

                    this.syncSelectValue(previousFilter);

                    this.requestError = true;
                } finally {
                    this.isLoadingFilter = false;
                }
            },


            /*
            |--------------------------------------------------------------------------
            | Close Custom Range
            |--------------------------------------------------------------------------
            */

            closeCustom() {
                if (this.isApplying) {
                    return;
                }

                this.showCustom = false;

                this.dateError = false;

                this.requestError = false;

                /*
                 * If the picker was opened from a standard range and the
                 * user cancels, restore that previously selected range.
                 */

                if (this.lastNonCustomFilter) {
                    this.currentFilter = this.lastNonCustomFilter;

                    this.syncSelectValue(
                        this.lastNonCustomFilter
                    );

                    return;
                }

                /*
                 * If a custom range was already applied, keep Custom Range
                 * selected even though the picker itself is closed.
                 */

                this.syncSelectValue(this.currentFilter);
            },


            /*
            |--------------------------------------------------------------------------
            | Dates Changed
            |--------------------------------------------------------------------------
            */

            datesChanged() {
                this.validateDates();
            },


            /*
            |--------------------------------------------------------------------------
            | Validate Calendar Date
            |--------------------------------------------------------------------------
            */

            isValidDateValue(value) {
                if (
                    typeof value !== 'string' ||
                    !/^\d{4}-\d{2}-\d{2}$/.test(value)
                ) {
                    return false;
                }

                const [year, month, day] = value
                    .split('-')
                    .map(Number);

                const date = new Date(
                    year,
                    month - 1,
                    day
                );

                return (
                    date.getFullYear() === year &&
                    date.getMonth() === month - 1 &&
                    date.getDate() === day
                );
            },


            /*
            |--------------------------------------------------------------------------
            | Parse Local Date
            |--------------------------------------------------------------------------
            */

            parseLocalDate(value) {
                if (!this.isValidDateValue(value)) {
                    return null;
                }

                const [year, month, day] = value
                    .split('-')
                    .map(Number);

                return new Date(
                    year,
                    month - 1,
                    day,
                    12,
                    0,
                    0
                );
            },


            /*
            |--------------------------------------------------------------------------
            | Validate Date Range
            |--------------------------------------------------------------------------
            */

            validateDates() {
                this.dateError = false;

                this.requestError = false;

                if (!this.startDate || !this.endDate) {
                    return false;
                }

                if (
                    !this.isValidDateValue(this.startDate) ||
                    !this.isValidDateValue(this.endDate)
                ) {
                    this.dateError = true;

                    return false;
                }

                /*
                 * YYYY-MM-DD strings can be compared chronologically.
                 */

                if (this.startDate > this.endDate) {
                    this.dateError = true;

                    return false;
                }

                return true;
            },


            /*
            |--------------------------------------------------------------------------
            | Can Apply
            |--------------------------------------------------------------------------
            */

            get canApply() {
                return Boolean(
                    this.startDate &&
                    this.endDate &&
                    this.isValidDateValue(this.startDate) &&
                    this.isValidDateValue(this.endDate) &&
                    this.startDate <= this.endDate &&
                    !this.isApplying &&
                    !this.isLoadingFilter
                );
            },


            /*
            |--------------------------------------------------------------------------
            | Formatted Range Preview
            |--------------------------------------------------------------------------
            */

            get formattedRange() {
                if (!this.startDate || !this.endDate) {
                    return 'Select your dates';
                }

                if (
                    !this.isValidDateValue(this.startDate) ||
                    !this.isValidDateValue(this.endDate) ||
                    this.startDate > this.endDate
                ) {
                    return 'Select a valid date range';
                }

                const start = this.parseLocalDate(this.startDate);

                const end = this.parseLocalDate(this.endDate);

                const options = {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                };

                return (
                    start.toLocaleDateString('en-US', options) +
                    ' – ' +
                    end.toLocaleDateString('en-US', options)
                );
            },


            /*
            |--------------------------------------------------------------------------
            | Apply Custom Range
            |--------------------------------------------------------------------------
            |
            | Submit both dates together through one Livewire action.
            | Close the picker only when the request succeeds.
            |
            */

            async applyRange() {
                if (this.isApplying || this.isLoadingFilter) {
                    return;
                }

                if (!this.validateDates()) {
                    return;
                }

                /*
                 * Snapshot the validated values for this request.
                 */

                const startDate = this.startDate;

                const endDate = this.endDate;

                this.isApplying = true;

                this.requestError = false;

                try {
                    /*
                     * The Livewire method must update both dates and the
                     * reporting filter in this single request.
                     */

                    await this.$wire.setCustomRange(
                        startDate,
                        endDate
                    );

                    /*
                     * Mark the custom filter as active.
                     */

                    this.currentFilter = 'custom';

                    /*
                     * There is no standard period to restore when closing
                     * the picker after the custom range has been applied.
                     */

                    this.lastNonCustomFilter = null;

                    /*
                     * Keep "Custom Range" displayed while re-arming the
                     * dropdown for the next selection.
                     */

                    this.syncSelectValue('custom');

                    /*
                     * Close only after Livewire completes successfully.
                     */

                    this.showCustom = false;
                } catch (error) {
                    console.error(
                        'Failed to apply the custom reporting range:',
                        error
                    );

                    /*
                     * Preserve the selected dates and keep the picker open
                     * so the user can retry.
                     */

                    this.requestError = true;
                } finally {
                    this.isApplying = false;

                    /*
                     * Ensure the dropdown is correctly synchronized even
                     * if the component was updated by Livewire.
                     */

                    this.$nextTick(() => {
                        this.syncSelectValue(this.currentFilter);
                    });
                }
            },
        }));

        window.__teamDashboardDatePickerRegistered = true;
    }
</script>
@endscript

</div>