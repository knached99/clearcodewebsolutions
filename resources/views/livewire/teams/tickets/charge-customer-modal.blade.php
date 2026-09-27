<div>
    <flux:modal
        name="charge-customer"
        wire:model="show"
        class="md:w-11/12 md:max-w-3xl"
    >
        <div
            class="p-6"
            x-data="{
                close() {
                    $wire.show = false;
                    $wire.closeModal();
                }
            }"
        >
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        Charge Customer
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        @if ($this->existingInvoice)
                            View the current billing status for this ticket.
                        @else
                            Create a secure checkout link to bill this customer.
                        @endif
                    </p>
                </div>

                <flux:button
                    variant="subtle"
                    size="sm"
                    icon="x-mark"
                    @click="close()"
                />
            </div>

            {{-- Submission Error --}}
            @if ($errors->has('submit'))
                <div
                    class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-500/10 dark:text-red-400"
                >
                    {{ $errors->first('submit') }}
                </div>
            @endif

            {{-- Existing Invoice --}}
            @if ($this->existingInvoice)

                <div class="mt-6 space-y-6">

                    {{-- Invoice Status --}}
                    <div
                        class="space-y-3 rounded-xl border border-zinc-200 bg-zinc-50 p-6 text-center dark:border-zinc-800 dark:bg-zinc-900/50"
                    >
                        @if ($this->existingInvoice->isPaid())

                            <div
                                class="mx-auto flex size-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30"
                            >
                                <flux:icon.check-circle
                                    class="size-6 text-green-600 dark:text-green-400"
                                />
                            </div>

                            <h3 class="text-lg font-medium text-zinc-900 dark:text-white">
                                Invoice Paid
                            </h3>

                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                The customer has completed payment for this ticket.
                            </p>

                        @else

                            <div
                                class="mx-auto flex size-12 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30"
                            >
                                <flux:icon.clock
                                    class="size-6 text-amber-600 dark:text-amber-400"
                                />
                            </div>

                            <h3 class="text-lg font-medium text-zinc-900 dark:text-white">
                                Pending Payment
                            </h3>

                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                An invoice was sent to the customer and is currently pending processing.
                            </p>

                        @endif
                    </div>

                    {{-- Invoice Details --}}
                    <div
                        class="space-y-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-800"
                    >
                        <h4
                            class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400"
                        >
                            Invoice Details
                        </h4>

                        <div
                            class="divide-y divide-zinc-100 space-y-2 text-sm dark:divide-zinc-800"
                        >
                            @foreach ($this->existingInvoice->items as $item)

                                <div
                                    class="flex items-center justify-between pt-2 first:pt-0"
                                >
                                    <div>
                                        <p class="font-medium text-zinc-900 dark:text-white">
                                            {{ $item->description }}
                                        </p>

                                        @if ($item->is_subscription)
                                            <span
                                                class="inline-flex items-center rounded bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300"
                                            >
                                                Recurring Monthly
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center rounded bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"
                                            >
                                                One-Time
                                            </span>
                                        @endif
                                    </div>

                                    <span class="font-semibold text-zinc-900 dark:text-white">
                                        ${{ number_format($item->amount / 100, 2) }}
                                    </span>
                                </div>

                            @endforeach
                        </div>

                        <div
                            class="flex items-center justify-between border-t border-zinc-200 pt-3 font-bold text-zinc-900 dark:border-zinc-800 dark:text-white"
                        >
                            <span>Total Charged</span>

                            <span class="text-base">
                                ${{ number_format($this->existingInvoice->total_amount / 100, 2) }}
                            </span>
                        </div>
                    </div>

                    {{-- Existing Invoice Actions --}}
                    <div class="flex justify-end gap-3 pt-2">

                        <flux:button
                            @click="close()"
                            variant="subtle"
                        >
                            Close
                        </flux:button>

                        <flux:button
                            href="{{ route('invoices.show', $this->existingInvoice) }}"
                            variant="primary"
                            class="bg-indigo-600 text-white hover:bg-indigo-700"
                        >
                            View Full Invoice
                        </flux:button>

                    </div>
                </div>

            @else

                {{-- Create Invoice --}}
                <form
                    wire:submit="submit"
                    class="mt-6 space-y-6"
                >

                    {{-- ================================================== --}}
                    {{-- BASE CHARGE --}}
                    {{-- ================================================== --}}
                    <div class="space-y-4">

                        <div>
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-white">
                                Base Charge
                            </h3>

                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                Choose whether the base charge is one-time or recurring.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                            <flux:input
                                wire:model.live.debounce.300ms="amount"
                                label="Amount ($)"
                                type="number"
                                step="0.01"
                                min="1"
                                placeholder="0.00"
                                required
                            />

                            <flux:input
                                wire:model="description"
                                label="Description"
                                placeholder="Development Services"
                                required
                            />

                        </div>

                        {{-- Base Charge Billing Type --}}
                        <div
                            class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-900/50"
                        >
                            <flux:checkbox
                                wire:model.live="isSubscription"
                                label="This base charge is a recurring monthly subscription (12 month minimum)"
                            />

                            <div
                                class="mt-2 text-xs text-zinc-500 dark:text-zinc-400"
                            >
                                @if ($this->isSubscription)
                                    The base charge will be billed monthly for a
                                    minimum of 12 months.
                                @else
                                    The base charge will be charged one time only.
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ================================================== --}}
                    {{-- MONTHLY ADDONS --}}
                    {{-- ================================================== --}}
                    <div
                        class="space-y-4 border-t border-zinc-200 pt-4 dark:border-zinc-800"
                    >

                        <div>
                            <h3 class="text-sm font-medium text-zinc-900 dark:text-white">
                                Monthly Add-ons
                            </h3>

                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                Select any optional services to include.
                                All add-ons are billed monthly with a 12-month minimum.
                            </p>
                        </div>

                        @if (empty($availableAddons))

                            <div
                                class="rounded-xl border border-dashed border-zinc-300 p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400"
                            >
                                No monthly add-ons are currently available.
                            </div>

                        @else

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                                @foreach ($availableAddons as $key => $addon)

                                    @php
                                        $isSelected = in_array(
                                            $key,
                                            $selectedAddonPriceIds,
                                            true
                                        );
                                    @endphp

                                    <label
                                        class="
                                            flex cursor-pointer flex-col justify-between
                                            rounded-xl border-2 p-4
                                            transition-all duration-150
                                            {{ $isSelected
                                                ? 'border-indigo-500 bg-indigo-50/50 dark:border-indigo-500 dark:bg-indigo-500/10'
                                                : 'border-zinc-200 bg-white hover:border-indigo-600 dark:border-zinc-800 dark:bg-zinc-900/50 dark:hover:border-indigo-600'
                                            }}
                                        "
                                    >
                                        <input
                                            type="checkbox"
                                            value="{{ $key }}"
                                            wire:model.live="selectedAddonPriceIds"
                                            class="sr-only"
                                        />

                                        <div class="flex items-start justify-between">

                                            <span
                                                class="
                                                    text-sm font-medium
                                                    {{ $isSelected
                                                        ? 'text-indigo-900 dark:text-indigo-200'
                                                        : 'text-zinc-900 dark:text-white'
                                                    }}
                                                "
                                            >
                                                {{ $addon['name'] }}
                                            </span>

                                            @if ($isSelected)
                                                <flux:icon.check-circle
                                                    class="size-5 text-indigo-500"
                                                />
                                            @endif

                                        </div>

                                        <div
                                            class="
                                                mt-2 text-lg font-bold
                                                {{ $isSelected
                                                    ? 'text-indigo-700 dark:text-indigo-300'
                                                    : 'text-zinc-700 dark:text-zinc-300'
                                                }}
                                            "
                                        >
                                            ${{ number_format($addon['price'], 2) }}

                                            <span class="text-sm font-normal opacity-70">
                                                /mo
                                            </span>
                                        </div>

                                        <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            Recurring monthly • 12-month minimum
                                        </div>
                                    </label>

                                @endforeach

                            </div>

                        @endif
                    </div>

                    {{-- ================================================== --}}
                    {{-- BILLING SUMMARY --}}
                    {{-- ================================================== --}}
                    <div
                        class="border-t border-zinc-200 pt-6 dark:border-zinc-800"
                    >
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">

                            <div class="w-full space-y-2 text-sm">

                                {{-- Due Today --}}
                                <div class="flex items-center justify-between sm:justify-start sm:gap-3">
                                    <span class="text-zinc-500 dark:text-zinc-400">
                                        Due today:
                                    </span>

                                    <strong class="text-lg text-zinc-900 dark:text-white">
                                        ${{ number_format($this->totalAmountInCents / 100, 2) }}
                                    </strong>
                                </div>

                                {{-- Base Charge Type --}}
                                <div class="flex items-center justify-between sm:justify-start sm:gap-3">
                                    <span class="text-zinc-500 dark:text-zinc-400">
                                        Base charge:
                                    </span>

                                    @if ($this->isSubscription)

                                        <span class="font-medium text-indigo-600 dark:text-indigo-400">
                                            ${{ number_format($this->baseAmountInCents / 100, 2) }}/month
                                        </span>

                                    @else

                                        <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                            ${{ number_format($this->baseAmountInCents / 100, 2) }}
                                            one-time
                                        </span>

                                    @endif
                                </div>

                                {{-- Recurring Total --}}
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">

                                    @if ($this->monthlyRecurringAmountInCents > 0)

                                        Recurring:
                                        <strong>
                                            ${{ number_format($this->monthlyRecurringAmountInCents / 100, 2) }}/month
                                        </strong>

                                        <span>
                                            (12-month minimum)
                                        </span>

                                    @else

                                        No recurring charges

                                    @endif

                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex w-full gap-3 sm:w-auto">

                                <flux:button
                                    type="button"
                                    @click="close()"
                                    variant="subtle"
                                    class="w-full sm:w-auto"
                                >
                                    Cancel
                                </flux:button>

                                <flux:button
                                    type="submit"
                                    variant="primary"
                                    class="w-full bg-indigo-600 text-white hover:bg-indigo-700 sm:w-auto"
                                    wire:loading.attr="disabled"
                                    wire:target="submit"
                                >
                                    <span
                                        wire:loading.remove
                                        wire:target="submit"
                                    >
                                        Generate & Email Link
                                    </span>

                                    <span
                                        wire:loading
                                        wire:target="submit"
                                        class="flex items-center gap-2"
                                    >
                                        <flux:icon.arrow-path class="inline-block size-4 animate-spin" />
                                        Processing...
                                    </span>
                                </flux:button>

                            </div>
                        </div>
                    </div>

                </form>

            @endif
        </div>
    </flux:modal>
</div>