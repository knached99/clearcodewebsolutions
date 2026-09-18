<div>
    @push('styles')
        @vite('resources/css/checkout.css')
    @endpush

    @push('scripts')
        <script src="https://js.stripe.com/v3/"></script>
        @vite('resources/js/checkout.js')
    @endpush

    <div
        class="relative min-h-screen bg-slate-950 py-12 sm:py-20 px-4 sm:px-6 lg:px-8 flex items-center justify-center font-sans text-slate-100 antialiased overflow-x-hidden">
        <!-- Ambient Mesh Lighting -->
        <div
            class="absolute -top-40 -left-40 w-96 sm:w-[600px] h-96 sm:h-[600px] bg-indigo-600/20 rounded-full blur-[140px] pointer-events-none">
        </div>
        <div
            class="absolute -bottom-40 -right-40 w-96 sm:w-[600px] h-96 sm:h-[600px] bg-purple-600/15 rounded-full blur-[140px] pointer-events-none">
        </div>

        <div class="relative w-full max-w-6xl mx-auto space-y-10">

            <!-- Page Header -->
            <header class="text-center space-y-4">
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs sm:text-sm font-semibold tracking-wider uppercase backdrop-blur-md">
                    <i class="fa-solid fa-shield-check text-indigo-400"></i> 256-Bit SSL Encrypted Checkout
                </div>
                <h1 class="text-4xl sm:text-6xl font-black text-white tracking-tight">Complete Payment</h1>
                <p class="text-slate-400 text-lg sm:text-xl">Invoice <span
                        class="font-mono text-indigo-300 font-bold">#{{ substr($invoice->id, 0, 8) }}</span></p>
            </header>

            @if ($invoice->status->value === 'paid')
                <div
                    class="bg-slate-900/90 backdrop-blur-2xl border border-slate-800 p-10 sm:p-16 rounded-3xl shadow-2xl text-center max-w-xl mx-auto">
                    <div
                        class="mx-auto flex h-24 w-24 items-center justify-center rounded-3xl bg-emerald-500/10 border border-emerald-500/20 mb-6">
                        <i class="fa-solid fa-check text-5xl text-emerald-400"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-white">Invoice Settled</h2>
                    <p class="mt-3 text-slate-400 text-lg">Thank you! This invoice has already been paid in full.</p>
                </div>
            @elseif($errorMessage)
                <div
                    class="bg-slate-900/90 backdrop-blur-2xl border border-red-500/30 p-10 rounded-3xl shadow-2xl text-center max-w-xl mx-auto space-y-4">
                    <div
                        class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-red-500/10 text-red-400">
                        <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white">Checkout Error</h2>
                    <p class="text-slate-300 text-base leading-relaxed">{{ $errorMessage }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

                    <!-- Left Column: Order Summary & Line Items -->
                    <div
                        class="lg:col-span-5 bg-slate-900/80 backdrop-blur-2xl border border-slate-800/90 p-8 sm:p-10 rounded-3xl shadow-2xl space-y-8">
                        <div class="flex items-center justify-between pb-6 border-b border-slate-800">
                            <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                                <i class="fa-solid fa-bag-shopping text-indigo-400"></i>
                                Summary
                            </h2>
                            <span
                                class="text-xs sm:text-sm font-semibold px-3 py-1 rounded-full bg-slate-800 text-slate-300 border border-slate-700/60">
                                {{ count($invoice->items) }} {{ Str::plural('item', count($invoice->items)) }}
                            </span>
                        </div>

                        <!-- Prominent Line Item Cards -->
                        <div class="space-y-4 max-h-[420px] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach ($invoice->items as $item)
                                <div
                                    class="bg-slate-950/80 border border-slate-800/90 hover:border-indigo-500/40 p-5 rounded-2xl transition-all duration-200 space-y-2">
                                    <div class="flex justify-between items-start gap-4">
                                        <p class="text-base sm:text-lg font-bold text-white leading-snug">
                                            {{ $item->description }}</p>
                                        <p class="text-lg sm:text-xl font-extrabold text-indigo-300 whitespace-nowrap">
                                            ${{ number_format($item->amountInDollars(), 2) }}</p>
                                    </div>
                                    @if ($item->is_subscription)
                                        <span
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold bg-indigo-500/15 text-indigo-300 px-3 py-1 rounded-lg border border-indigo-500/20">
                                            <i class="fa-solid fa-arrows-rotate text-[10px]"></i> Billed Monthly (12 Mo.
                                            Min)
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Grand Total Card -->
                        <div
                            class="bg-gradient-to-br from-indigo-950/60 via-slate-900 to-slate-950 border border-indigo-500/40 p-6 rounded-2xl space-y-3 shadow-inner">
                            <div class="flex justify-between items-center text-slate-400 text-base font-medium">
                                <span>Subtotal</span>
                                <span>${{ number_format($invoice->totalAmountInDollars(), 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t border-slate-800">
                                <span class="text-lg font-bold text-white">Total Due</span>
                                <span
                                    class="text-3xl sm:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 via-indigo-200 to-purple-300">
                                    ${{ number_format($invoice->totalAmountInDollars(), 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Security Badges -->
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div
                                class="flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl bg-slate-950/60 border border-slate-800 text-slate-400 text-xs sm:text-sm font-medium">
                                <i class="fa-solid fa-lock text-emerald-400"></i> PCI Level 1
                            </div>
                            <div
                                class="flex items-center justify-center gap-2.5 py-3 px-4 rounded-xl bg-slate-950/60 border border-slate-800 text-slate-400 text-xs sm:text-sm font-medium">
                                <i class="fa-brands fa-stripe text-indigo-400 text-base"></i> Stripe Verified
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Card Visual & Form -->
                    <div
                        class="lg:col-span-7 bg-slate-900/90 backdrop-blur-2xl border border-slate-800/90 p-8 sm:p-10 rounded-3xl shadow-2xl space-y-10">

                        <!-- 3D Interactive Card Visual -->
                        <div class="w-full flex justify-center">
                            <div class="card-container">
                                <div class="card-3d" id="visual-card">

                                    <!-- Front of Card -->
                                    <div class="card-front p-6 sm:p-8 flex flex-col justify-between">
                                        <div class="card-gloss-overlay"></div>

                                        <div class="flex justify-between items-center z-10">
                                            <div class="flex items-center gap-3 sm:gap-4">
                                                <div
                                                    class="w-12 sm:w-14 h-8 sm:h-10 bg-gradient-to-tr from-amber-200 via-amber-400 to-amber-100 rounded-lg shadow-inner flex items-center justify-center border border-amber-300/50">
                                                    <div
                                                        class="w-8 h-6 border border-amber-600/50 rounded grid grid-cols-2 gap-0.5 p-0.5">
                                                        <div class="border-r border-b border-amber-600/50"></div>
                                                        <div class="border-b border-amber-600/50"></div>
                                                        <div class="border-r border-amber-600/50"></div>
                                                        <div></div>
                                                    </div>
                                                </div>
                                                <i class="fa-solid fa-wifi text-slate-300/80 text-xl rotate-90"></i>
                                            </div>
                                            <!-- Dynamic Brand Logo Container -->
                                            <div id="card-brand-logo" class="text-3xl sm:text-5xl text-white/90">
                                                <i class="fa-brands fa-cc-stripe"></i>
                                            </div>
                                        </div>

                                        <div class="z-10 space-y-4 sm:space-y-6">
                                            <div class="text-xl sm:text-3xl tracking-[0.2em] font-mono font-bold text-white drop-shadow-lg"
                                                id="display-number">
                                                •••• •••• •••• ••••
                                            </div>

                                            <div class="flex justify-between items-end">
                                                <div class="max-w-[70%]">
                                                    <div
                                                        class="text-[10px] sm:text-xs uppercase tracking-widest text-indigo-200/80 font-bold mb-1">
                                                        Cardholder Name</div>
                                                    <div class="uppercase tracking-wider font-bold text-sm sm:text-lg truncate text-white drop-shadow-md"
                                                        id="display-name">
                                                        YOUR NAME
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div
                                                        class="text-[10px] sm:text-xs uppercase tracking-widest text-indigo-200/80 font-bold mb-1">
                                                        Expires</div>
                                                    <div class="font-mono text-sm sm:text-lg font-bold text-white drop-shadow-md"
                                                        id="display-expiry">MM/YY</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Back of Card -->
                                    <div class="card-back flex flex-col justify-between">
                                        <div class="card-gloss-overlay"></div>
                                        <div class="w-full h-12 sm:h-14 bg-slate-950 mt-6 sm:mt-8 shadow-inner"></div>

                                        <div class="px-6 sm:px-8 mb-6 sm:mb-8 z-10">
                                            <div
                                                class="text-[10px] uppercase tracking-widest text-indigo-200/80 font-bold mb-1.5 text-right">
                                                Security Code</div>
                                            <div
                                                class="bg-slate-100 rounded-xl px-4 py-2 text-right text-slate-900 font-mono italic flex justify-between items-center h-11 sm:h-12 shadow-inner">
                                                <div
                                                    class="h-full w-2/3 bg-slate-200 rounded flex items-center px-2 opacity-50">
                                                    <div class="w-full border-b border-slate-400 border-dashed"></div>
                                                </div>
                                                <span id="display-cvv"
                                                    class="text-base sm:text-lg font-extrabold tracking-widest text-slate-900">•••</span>
                                            </div>
                                            <div class="mt-2 text-[11px] text-center text-indigo-200/70">CVC / CVV
                                                Security Code</div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Stripe Form Elements -->
                        <form id="payment-form" data-secret="{{ $clientSecret }}"
                            data-key="{{ config('services.stripe.key') }}" class="space-y-6">

                            <div>
                                <label
                                    class="block text-xs sm:text-sm font-bold text-slate-300 uppercase tracking-wider mb-2">Name
                                    on Card</label>
                                <input type="text" id="card-name"
                                    class="stripe-element-container w-full outline-none focus:ring-2 focus:ring-indigo-500/50 text-white placeholder-slate-500 font-medium text-base sm:text-lg"
                                    placeholder="Jane Doe" required>
                            </div>

                            <div>
                                <label
                                    class="block text-xs sm:text-sm font-bold text-slate-300 uppercase tracking-wider mb-2">Card
                                    Number</label>
                                <div id="cardNumber-element" class="stripe-element-container w-full"></div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 sm:gap-6">
                                <div>
                                    <label
                                        class="block text-xs sm:text-sm font-bold text-slate-300 uppercase tracking-wider mb-2">Expiration</label>
                                    <div id="cardExpiry-element" class="stripe-element-container w-full"></div>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs sm:text-sm font-bold text-slate-300 uppercase tracking-wider mb-2">CVC</label>
                                    <div id="cardCvc-element" class="stripe-element-container w-full"></div>
                                </div>
                            </div>

                            <div id="card-errors"
                                class="text-red-400 bg-red-500/10 border border-red-500/30 p-4 rounded-2xl text-sm font-medium hidden flex items-center gap-2">
                            </div>

                            <!-- Submit Button -->
                            <button id="submit-button" type="submit"
                                class="mt-8 w-full bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-extrabold py-4 sm:py-5 px-6 rounded-2xl shadow-xl shadow-indigo-600/30 hover:shadow-indigo-600/50 active:scale-[0.99] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 flex justify-center items-center gap-3 text-lg cursor-pointer">
                                <i class="fa-solid fa-lock text-sm opacity-80"></i>
                                <span id="button-text">Pay
                                    ${{ number_format($invoice->totalAmountInDollars(), 2) }}</span>
                                <!-- Spinner is explicitly hidden by default with inline display style -->
                                <i id="button-spinner" class="fa-solid fa-circle-notch fa-spin text-xl"
                                    style="display: none;"></i>
                            </button>
                        </form>

                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
