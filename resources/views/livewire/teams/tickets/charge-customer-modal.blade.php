<div>
    <flux:modal name="charge-customer" wire:model="show" class="md:w-11/12 md:max-w-3xl">
        <div class="p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Charge Customer</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Create a secure checkout link to bill this customer.
                    </p>
                </div>
                <flux:button variant="subtle" size="sm" icon="x-mark" wire:click="closeModal" />
            </div>

            <form wire:submit="submit" class="mt-6 space-y-6">
                <!-- Base Charge -->
                <div class="space-y-4">
                    <h3 class="text-sm font-medium text-zinc-900 dark:text-white">Base Charge</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input 
                            wire:model="amount" 
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

                    <flux:checkbox 
                        wire:model="isSubscription" 
                        label="This is a recurring monthly subscription (12 month minimum)" 
                    />
                </div>

                <!-- Addons -->
                <div class="space-y-4 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <h3 class="text-sm font-medium text-zinc-900 dark:text-white">Monthly Add-ons</h3>
                    <p class="text-xs text-zinc-500">Select any optional services to include.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($availableAddons as $key => $addon)
                            @php
                                $isSelected = in_array($key, $selectedAddons);
                            @endphp
                            <div 
                                wire:click="toggleAddon('{{ $key }}')"
                                class="cursor-pointer rounded-xl border-2 p-4 transition-all duration-200 flex flex-col justify-between 
                                {{ $isSelected 
                                    ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-500/10 dark:border-indigo-500' 
                                    : 'border-zinc-200 bg-white hover:border-indigo-600 dark:border-zinc-800 dark:bg-zinc-900/50 dark:hover:border-indigo-600' }}"
                            >
                                <div class="flex justify-between items-start">
                                    <span class="text-sm font-medium {{ $isSelected ? 'text-indigo-900 dark:text-indigo-200' : 'text-zinc-900 dark:text-white' }}">
                                        {{ $addon['name'] }}
                                    </span>
                                    @if($isSelected)
                                        <flux:icon.check-circle class="size-5 text-indigo-500" />
                                    @endif
                                </div>
                                <div class="mt-2 text-lg font-bold {{ $isSelected ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    ${{ $addon['price'] }}<span class="text-sm font-normal opacity-70">/mo</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Total Preview & Actions -->
                <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm">
                        <span class="text-zinc-500">Total estimated value: </span>
                        <strong class="text-lg text-zinc-900 dark:text-white">
                            ${{ number_format($this->totalAmountInCents / 100, 2) }}
                        </strong>
                    </div>

                    <div class="flex gap-3 w-full sm:w-auto">
                        <flux:button wire:click="closeModal" variant="subtle" class="w-full sm:w-auto">Cancel</flux:button>
                        <flux:button type="submit" variant="primary" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white">
                            Generate & Email Link
                        </flux:button>
                    </div>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
