<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <flux:button icon="chevron-left" variant="subtle" onclick="history.back()">Back</flux:button>
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
                    Invoice #{{ substr($invoice->id, 0, 8) }}
                </h1>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                    Created on {{ $invoice->created_at->format('M d, Y \a\t g:i A') }}
                </p>
            </div>
        </div>

        <div>
            @if ($invoice->isPaid())
                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-green-50 dark:bg-green-500/10 px-3 py-1 text-sm font-semibold text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
                    <flux:icon.check-circle class="size-4" /> Paid
                </span>
            @else
                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 dark:bg-amber-500/10 px-3 py-1 text-sm font-semibold text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                    <flux:icon.clock class="size-4" /> Pending Payment
                </span>
            @endif
        </div>
    </div>

    <div
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 sm:p-8 space-y-8 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-zinc-200 dark:border-zinc-800">
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-2">
                    Customer Details</h3>
                <p class="text-base font-semibold text-zinc-900 dark:text-white">{{ $invoice->customer->name ?? 'N/A' }}
                </p>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $invoice->customer->email ?? 'N/A' }}</p>
            </div>

            <div class="sm:text-right">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-2">
                    Associated Ticket</h3>
                @if ($invoice->ticket)
                    <p class="text-base font-semibold text-zinc-900 dark:text-white">
                        #{{ $invoice->ticket->ticket_number }}
                    </p>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $invoice->ticket->subject ?? 'Support Ticket' }}</p>
                @else
                    <p class="text-sm text-zinc-500">No ticket associated</p>
                @endif
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white mb-4">Line Items</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400">
                        <tr>
                            <th class="pb-3 font-medium">Description</th>
                            <th class="pb-3 font-medium text-center">Billing Type</th>
                            <th class="pb-3 font-medium text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60 text-zinc-900 dark:text-zinc-100">
                        @foreach ($invoice->items as $item)
                            <tr>
                                <td class="py-3.5 font-medium">{{ $item->description }}</td>
                                <td class="py-3.5 text-center">
                                    @if ($item->is_subscription)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300">
                                            Monthly Recurring
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                            One-time
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 text-right font-semibold">
                                    ${{ number_format($item->amount / 100, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div
            class="pt-6 border-t border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
            <div class="space-y-1">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Payment Structure:
                    <strong class="text-zinc-700 dark:text-zinc-300">
                        {{ $invoice->isSubscription() ? '12 Month Minimum Subscription' : 'One-Time Charge' }}
                    </strong>
                </p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Monthly Recurring Total:
                    <strong class="text-zinc-700 dark:text-zinc-300">
                        ${{ number_format($invoice->monthlyRecurringAmountInDollars(), 2) }}/mo
                    </strong>
                </p>
            </div>

            <div
                class="text-right w-full sm:w-auto p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-200/60 dark:border-zinc-700/60">
                <span
                    class="text-xs text-zinc-500 dark:text-zinc-400 block uppercase tracking-wider font-semibold">Total
                    Due Today</span>
                <span class="text-2xl font-bold text-zinc-900 dark:text-white">
                    ${{ number_format($invoice->totalAmountInDollars(), 2) }}
                </span>
            </div>
        </div>

        @if (!$invoice->isPaid())
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-end">
                <flux:button href="{{ route('checkout.show', $invoice->checkout_token) }}" target="_blank"
                    variant="primary" class="bg-indigo-600 hover:bg-indigo-700 text-white">
                    Open Public Checkout Link
                </flux:button>
            </div>
        @endif
    </div>
</div>
