<x-layouts::app :title="__('Dashboard')">
    @assets
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @endassets

    @php
        $grossProfitDisplay = '$' . number_format($grossProfit / 100, 2);
        $netProfitDisplay = '$' . number_format($netProfit / 100, 2);
        $lossDisplay = '$' . number_format($losses / 100, 2);
        $mrrDisplay = '$' . number_format($mrr / 100, 2);
        $arrDisplay = '$' . number_format($arr / 100, 2);
        $grossMarginDisplay = number_format($grossMarginPercentage, 2) . '%';
        $highestCustomerName = $highestProfitingCustomer['name'] ?? 'No customer data';
        $highestCustomerAmount = isset($highestProfitingCustomer['amount']) ? '$' . number_format($highestProfitingCustomer['amount'] / 100, 2) : '$0.00';
        $revenueLabels = collect($monthlyRevenue)->pluck('month')->values()->all();
        $revenueValues = collect($monthlyRevenue)->pluck('value')->map(fn ($value) => (int) $value)->values()->all();
        $customerChartData = collect($customerRevenue)->map(fn ($customer) => ['name' => $customer['name'], 'data' => [(int) $customer['amount']]])->values()->all();
    @endphp

    <div class="dashboard-shell" style="padding: 1.5rem 0 2rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
            <div>
                <div style="font-size: .8rem; letter-spacing: .12em; text-transform: uppercase; opacity: .7; font-weight: 700;">Operations overview</div>
                <h1 style="margin: .3rem 0 0; font-size: clamp(2rem, 3vw, 3rem); line-height: 1.1; font-weight: 800;">{{ $team->name }} analytics</h1>
            </div>
            <div style="display:flex; gap:.75rem; flex-wrap:wrap;">
                <span class="metric-pill">{{ $paidInvoices }} paid invoices</span>
                <span class="metric-pill">{{ $activeSubscriptions }} active subscriptions</span>
            </div>
        </div>

        <div class="stats-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div class="stat-card">
                <div class="stat-card__label">Gross profit</div>
                <div class="stat-card__value success-text">{{ $grossProfitDisplay }}</div>
                <div class="stat-card__meta">Total successful revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__label">Net profit</div>
                <div class="stat-card__value primary-text">{{ $netProfitDisplay }}</div>
                <div class="stat-card__meta">After losses</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__label">MRR</div>
                <div class="stat-card__value">{{ $mrrDisplay }}</div>
                <div class="stat-card__meta">Monthly recurring revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__label">ARR</div>
                <div class="stat-card__value">{{ $arrDisplay }}</div>
                <div class="stat-card__meta">Annualized recurring revenue</div>
            </div>
            <div class="stat-card warning-card">
                <div class="stat-card__label">Losses</div>
                <div class="stat-card__value danger-text">{{ $lossDisplay }}</div>
                <div class="stat-card__meta">Failed / written-off revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__label">Gross margin</div>
                <div class="stat-card__value">{{ $grossMarginDisplay }}</div>
                <div class="stat-card__meta">Profitability rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-card__label">Highest profit customer</div>
                <div class="stat-card__value">{{ $highestCustomerName }}</div>
                <div class="stat-card__meta">{{ $highestCustomerAmount }}</div>
            </div>
        </div>

        <div class="mini-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div class="mini-card">
                <div class="mini-card__label">Average invoice</div>
                <div class="mini-card__value">${{ number_format($averageInvoiceValue / 100, 2) }}</div>
            </div>
            <div class="mini-card">
                <div class="mini-card__label">Subscription revenue</div>
                <div class="mini-card__value">${{ number_format($subscriptionRevenue / 100, 2) }}</div>
            </div>
            <div class="mini-card">
                <div class="mini-card__label">One-time revenue</div>
                <div class="mini-card__value">${{ number_format($oneTimeRevenue / 100, 2) }}</div>
            </div>
            <div class="mini-card">
                <div class="mini-card__label">Total invoices</div>
                <div class="mini-card__value">{{ $totalInvoices }}</div>
            </div>
        </div>

        <div class="chart-grid" style="display:grid; grid-template-columns: 2fr 1fr; gap:1rem; margin-bottom:1.5rem;">
            <div class="panel-card">
                <div class="panel-card__header">
                    <div>
                        <div class="panel-card__eyebrow">Revenue trend</div>
                        <h2>12 month performance</h2>
                    </div>
                </div>
                <div id="revenueChart" class="chart-container"></div>
            </div>
            <div class="panel-card">
                <div class="panel-card__header">
                    <div>
                        <div class="panel-card__eyebrow">Customer mix</div>
                        <h2>Top customers</h2>
                    </div>
                </div>
                <div id="customerChart" class="chart-container small-chart"></div>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-card__header">
                <div>
                    <div class="panel-card__eyebrow">Revenue split</div>
                    <h2>Payment type contribution</h2>
                </div>
            </div>
            <div id="donutChart" class="chart-container donut-chart"></div>
        </div>
    </div>

    <style>
        :root {
            --dashboard-bg: #f3f6fb;
            --dashboard-surface: rgba(255,255,255,0.72);
            --dashboard-border: rgba(148,163,184,0.24);
            --dashboard-text: #0f172a;
            --dashboard-muted: #475569;
            --dashboard-card-soft: rgba(255,255,255,0.7);
            --dash-shadow: 0 18px 38px rgba(15, 23, 42, 0.08);
            --dash-primary: #7c3aed;
            --dash-success: #10b981;
            --dash-warning: #f59e0b;
            --dash-danger: #ef4444;
            --dash-info: #0ea5e9;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --dashboard-bg: #07111d;
                --dashboard-surface: rgba(15, 23, 42, 0.82);
                --dashboard-border: rgba(148,163,184,0.18);
                --dashboard-text: #e2e8f0;
                --dashboard-muted: #a5b4c9;
                --dashboard-card-soft: rgba(15, 23, 42, 0.78);
                --dash-shadow: 0 16px 32px rgba(2, 6, 23, 0.6);
                --dash-primary: #a78bfa;
                --dash-success: #34d399;
                --dash-warning: #fbbf24;
                --dash-danger: #f87171;
                --dash-info: #38bdf8;
            }
        }

        .dashboard-shell {
            background: var(--dashboard-bg);
            color: var(--dashboard-text);
            min-height: 100%;
            padding-inline: clamp(0.75rem, 2vw, 1.25rem);
        }

        .metric-pill {
            border: 1px solid var(--dashboard-border);
            background: var(--dashboard-card-soft);
            backdrop-filter: blur(12px);
            color: var(--dashboard-text);
            border-radius: 999px;
            padding: .55rem .9rem;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .stat-card,
        .mini-card,
        .panel-card {
            background: var(--dashboard-surface);
            border: 1px solid var(--dashboard-border);
            box-shadow: var(--dash-shadow);
            border-radius: 22px;
            backdrop-filter: blur(12px);
        }

        .stat-card {
            padding: 1rem 1.1rem 1.2rem;
        }

        .stat-card__label,
        .mini-card__label,
        .panel-card__eyebrow {
            font-size: .72rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--dashboard-muted);
            font-weight: 700;
        }

        .stat-card__value,
        .mini-card__value {
            margin-top: .65rem;
            font-size: clamp(1.5rem, 2vw, 2.15rem);
            font-weight: 800;
            line-height: 1.2;
        }

        .stat-card__meta {
            margin-top: .55rem;
            color: var(--dashboard-muted);
            font-size: .82rem;
        }

        .success-text { color: var(--dash-success); }
        .danger-text { color: var(--dash-danger); }
        .primary-text { color: var(--dash-primary); }

        .mini-card {
            padding: 1rem 1.1rem;
        }

        .panel-card {
            padding: 1.1rem 1.1rem 0.8rem;
        }

        .panel-card__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .8rem;
        }

        .panel-card h2 {
            margin: .35rem 0 0;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .chart-container {
            width: 100%;
            min-height: 320px;
        }

        .small-chart {
            min-height: 320px;
        }

        .donut-chart {
            min-height: 280px;
        }

        @media (max-width: 980px) {
            .chart-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const labels = @json($revenueLabels);
            const values = @json($revenueValues).map((value) => Number(value) / 100);
            const donutSeries = [
                @json((float) $subscriptionRevenue / 100),
                @json((float) $oneTimeRevenue / 100),
            ];
            const donutLabels = ['Subscription revenue', 'One-time revenue'];

            const revenueOptions = {
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    background: 'transparent',
                    foreColor: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-text').trim(),
                },
                theme: { mode: themeMode },
                series: [{
                    name: 'Revenue',
                    data: values,
                }],
                xaxis: {
                    categories: labels,
                    labels: { style: { colors: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-muted').trim() } },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: {
                        formatter: (value) => '$' + Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 }),
                        style: { colors: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-muted').trim() },
                    },
                },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 0.7,
                        opacityFrom: 0.5,
                        opacityTo: 0.1,
                    },
                },
                colors: ['#8b5cf6'],
                grid: { borderColor: 'rgba(148, 163, 184, 0.18)', strokeDashArray: 3 },
                tooltip: {
                    y: {
                        formatter: (value) => '$' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                    },
                },
                markers: { size: 0 },
            };

            const customerSeries = [
                {
                    name: 'Customer revenue',
                    data: @json(collect($customerRevenue)->map(fn ($customer) => (int) $customer['amount'])->values()->all()).map((value) => Number(value) / 100),
                }
            ];

            const customerLabels = @json(collect($customerRevenue)->map(fn ($customer) => $customer['name'])->values()->all());

            const customerOptions = {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false },
                    background: 'transparent',
                    foreColor: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-text').trim(),
                },
                theme: { mode: themeMode },
                series: customerSeries,
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 6,
                        columnWidth: '45%',
                    },
                },
                colors: ['#22c55e'],
                xaxis: {
                    categories: customerLabels,
                    labels: {
                        style: { colors: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-muted').trim() },
                    },
                },
                yaxis: {
                    labels: { style: { colors: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-muted').trim() } },
                },
                grid: { borderColor: 'rgba(148, 163, 184, 0.18)', strokeDashArray: 3 },
                tooltip: {
                    y: {
                        formatter: (value) => '$' + Number(value / 100).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                    },
                },
            };

            const donutOptions = {
                chart: {
                    type: 'donut',
                    height: 260,
                    toolbar: { show: false },
                    background: 'transparent',
                },
                theme: { mode: themeMode },
                labels: donutLabels,
                series: donutSeries,
                colors: ['#8b5cf6', '#22c55e'],
                legend: {
                    position: 'bottom',
                    labels: { colors: getComputedStyle(document.documentElement).getPropertyValue('--dashboard-text').trim() },
                },
                tooltip: {
                    y: {
                        formatter: (value) => '$' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                    },
                },
            };

            new ApexCharts(document.querySelector('#revenueChart'), revenueOptions).render();
            new ApexCharts(document.querySelector('#customerChart'), customerOptions).render();
            new ApexCharts(document.querySelector('#donutChart'), donutOptions).render();
        });
    </script>
</x-layouts::app>