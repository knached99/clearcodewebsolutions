<div>
    <flux:modal name="charge-customer" wire:model="show" class="md:w-11/12 md:max-w-3xl">
        <div class="p-6" x-data="{
            close() {
                $wire.show = false;
                $wire.closeModal();
            }
        }">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Charge Customer</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        @if ($this->existingInvoice)
                            View the current billing status for this ticket.
                        @else
                            Create a secure checkout link to bill this customer.
                        @endif
                    </p>
                </div>
                <flux:button variant="subtle" size="sm" icon="x-mark" @click="close()" />
            </div>

            @if ($errors->has('submit'))
                <div class="mt-4 p-3 bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 rounded-lg text-sm border border-red-200 dark:border-red-900">
                    {{ $errors->first('submit') }}
                </div>
            @endif

            @if ($this->existingInvoice)
                <div class="mt-6 space-y-6">
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 p-6 text-center space-y-3">
                        @if ($this->existingInvoice->isPaid())
                            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                <flux:icon.check-circle class="size-6 text-green-600 dark:text-green-400" />
                            </div>
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Invoice Paid</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                The customer has completed payment for this ticket.
                            </p>
                        @else
                            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                                <flux:icon.clock class="size-6 text-amber-600 dark:text-amber-400" />
                            </div>
                            <h3 class="text-lg font-medium text-zinc-900 dark:text-white">Pending Payment</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                An invoice was sent to the customer and is currently pending processing.
                            </p>
                        @endif
                    </div>

                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-800 p-4 space-y-3">
                        <h4 class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Invoice Details
                        </h4>

                        <div class="space-y-2 divide-y divide-zinc-100 dark:divide-zinc-800 text-sm">
                            @foreach ($this->existingInvoice->items as $item)
                                <div class="flex items-center justify-between pt-2 first:pt-0">
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-white">{{ $item->description }}</p>
                                        @if ($item->is_subscription)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">
                                                Recurring Monthly
                                            </span>
                                        @endif
                                    </div>
                                    <span class="font-semibold text-zinc-900 dark:text-white">
                                        ${{ number_format($item->amount / 100, 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-3 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between font-bold text-zinc-900 dark:text-white">
                            <span>Total Charged</span>
                            <span class="text-base">${{ number_format($this->existingInvoice->total_amount / 100, 2) }}</span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <flux:button @click="close()" variant="subtle">Close</flux:button>
                        <flux:button href="{{ route('invoices.show', $this->existingInvoice) }}" variant="primary"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white">
                            View Full Invoice
                        </flux:button>
                    </div>
                </div>
            @else
                <form wire:submit="submit" class="mt-6 space-y-6">
                    <div class="space-y-4">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-white">Base Charge</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:input wire:model.live.debounce.300ms="amount" label="Amount ($)" type="number" step="0.01"
                                min="1" placeholder="0.00" required />

                            <flux:input wire:model="description" label="Description" placeholder="Development Services"
                                required />
                        </div>

                        <flux:checkbox wire:model.live="isSubscription"
                            label="This is a recurring monthly subscription (12 month minimum)" />
                    </div>

                    <div class="space-y-4 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                        <h3 class="text-sm font-medium text-zinc-900 dark:text-white">Monthly Add-ons</h3>
                        <p class="text-xs text-zinc-500">Select any optional services to include.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach ($availableAddons as $key => $addon)
                                @php
                                    $isSelected = in_array($key, $selectedAddonPriceIds);
                                @endphp
                                <label
                                    class="cursor-pointer rounded-xl border-2 p-4 transition-all duration-150 flex flex-col justify-between 
                                    {{ $isSelected
                                        ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10 dark:border-indigo-500'
                                        : 'border-zinc-200 bg-white hover:border-indigo-600 dark:border-zinc-800 dark:bg-zinc-900/50 dark:hover:border-indigo-600' }}">
                                    <input 
                                        type="checkbox" 
                                        value="{{ $key }}" 
                                        wire:model.live="selectedAddonPriceIds" 
                                        class="sr-only" 
                                    />
                                    <div class="flex justify-between items-start">
                                        <span class="text-sm font-medium {{ $isSelected ? 'text-indigo-900 dark:text-indigo-200' : 'text-zinc-900 dark:text-white' }}">
                                            {{ $addon['name'] }}
                                        </span>
                                        @if ($isSelected)
                                            <flux:icon.check-circle class="size-5 text-indigo-500" />
                                        @endif
                                    </div>
                                    <div class="mt-2 text-lg font-bold {{ $isSelected ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-700 dark:text-zinc-300' }}">
                                        ${{ number_format($addon['price'], 2) }}<span class="text-sm font-normal opacity-70">/mo</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm space-y-1">
                            <div>
                                <span class="text-zinc-500">Due today: </span>
                                <strong class="text-lg text-zinc-900 dark:text-white">
                                    ${{ number_format($this->totalAmountInCents / 100, 2) }}
                                </strong>
                            </div>
                            <div class="text-xs text-zinc-500">
                                @if($this->monthlyRecurringAmountInCents > 0)
                                    Recurring: ${{ number_format($this->monthlyRecurringAmountInCents / 100, 2) }}/month
                                    @if($this->isSubscription)
                                        (12-month minimum commitment)
                                    @endif
                                @else
                                    No recurring charges
                                @endif
                            </div>
                        </div>

                        <div class="flex gap-3 w-full sm:w-auto">
                            <flux:button type="button" @click="close()" variant="subtle" class="w-full sm:w-auto">Cancel</flux:button>
                            <flux:button type="submit" variant="primary"
                                class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white"
                                wire:loading.attr="disabled" wire:target="submit">
                                <span wire:loading.remove wire:target="submit">Generate & Email Link</span>
                                <span wire:loading wire:target="submit" class="flex items-center gap-2">
                                    <flux:icon.arrow-path class="size-4 animate-spin inline-block" /> Processing...
                                </span>
                            </flux:button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </flux:modal>
</div>