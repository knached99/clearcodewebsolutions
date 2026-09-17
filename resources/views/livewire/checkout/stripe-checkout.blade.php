<div>
    @push('styles')
        @vite('resources/css/checkout.css')
    @endpush

    @push('scripts')
        <script src="https://js.stripe.com/v3/"></script>
        @vite('resources/js/checkout.js')
    @endpush

    <div class="min-h-screen bg-gray-50 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl">
            
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900">Secure Checkout</h1>
                <p class="mt-2 text-gray-600">Complete your payment for Invoice #{{ substr($invoice->id, 0, 8) }}</p>
            </div>

            @if($invoice->status->value === 'paid')
                <div class="bg-white p-8 rounded-2xl shadow text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                        <i class="fa-solid fa-check text-2xl text-green-600"></i>
                    </div>
                    <h2 class="mt-4 text-xl font-bold text-gray-900">Payment Successful!</h2>
                    <p class="mt-2 text-gray-600">Thank you for your payment. This invoice is already settled.</p>
                </div>
            @elseif($errorMessage)
                <div class="bg-white p-8 rounded-2xl shadow text-center border-t-4 border-red-500">
                    <h2 class="text-xl font-bold text-red-600">Error</h2>
                    <p class="mt-2 text-gray-600">{{ $errorMessage }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- Left Column: Summary -->
                    <div class="bg-white p-8 rounded-2xl shadow">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">Order Summary</h2>
                        
                        <div class="space-y-4 mb-6">
                            @foreach($invoice->items as $item)
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $item->description }}</p>
                                        @if($item->is_subscription)
                                            <p class="text-xs text-indigo-600">Billed Monthly</p>
                                        @endif
                                    </div>
                                    <p class="font-medium text-gray-900">${{ number_format($item->amountInDollars(), 2) }}</p>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4 flex justify-between items-center">
                            <span class="text-lg font-bold text-gray-900">Total Due Today</span>
                            <span class="text-2xl font-bold text-indigo-600">${{ number_format($invoice->totalAmountInDollars(), 2) }}</span>
                        </div>
                    </div>

                    <!-- Right Column: Payment Form -->
                    <div class="bg-white p-8 rounded-2xl shadow">
                        
                        <!-- 3D Card Visual -->
                        <div class="mb-8">
                            <div class="card-container">
                                <div class="card-3d" id="visual-card">
                                    <!-- Front of Card -->
                                    <div class="card-front p-6 flex flex-col justify-between">
                                        <div class="flex justify-between items-start">
                                            <i class="fa-solid fa-microchip text-3xl text-yellow-300/80"></i>
                                            <i class="fa-brands fa-cc-stripe text-3xl opacity-50"></i>
                                        </div>
                                        <div>
                                            <div class="text-xl tracking-widest font-mono mb-2" id="display-number">•••• •••• •••• ••••</div>
                                            <div class="flex justify-between items-end">
                                                <div class="uppercase tracking-wider font-semibold text-sm max-w-[200px] truncate" id="display-name">YOUR NAME</div>
                                                <div class="font-mono text-sm" id="display-expiry">MM/YY</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Back of Card -->
                                    <div class="card-back flex flex-col justify-between">
                                        <div class="w-full h-12 bg-gray-800 mt-6"></div>
                                        <div class="px-6 mb-6">
                                            <div class="bg-white rounded px-3 py-2 text-right text-gray-800 font-mono italic flex justify-end items-center h-10">
                                                <span id="display-cvv" class="text-sm">•••</span>
                                            </div>
                                            <div class="mt-2 text-xs text-center opacity-60">Security Code</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stripe Elements Form -->
                        <form id="payment-form" data-secret="{{ $clientSecret }}" data-key="{{ config('services.stripe.key') }}">
                            <div class="space-y-5">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Name on Card</label>
                                    <input type="text" id="card-name" class="stripe-element-container w-full outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Jane Doe" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                                    <div id="cardNumber-element" class="stripe-element-container w-full"></div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiration</label>
                                        <div id="cardExpiry-element" class="stripe-element-container w-full"></div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">CVC</label>
                                        <div id="cardCvc-element" class="stripe-element-container w-full"></div>
                                    </div>
                                </div>

                            </div>

                            <div id="card-errors" class="text-red-500 text-sm mt-4 hidden"></div>

                            <button id="submit-button" class="mt-8 w-full bg-indigo-600 border border-transparent rounded-xl shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 flex justify-center items-center">
                                <span id="button-text">Pay ${{ number_format($invoice->totalAmountInDollars(), 2) }}</span>
                                <i id="button-spinner" class="fa-solid fa-circle-notch fa-spin hidden ml-2"></i>
                            </button>
                        </form>

                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
