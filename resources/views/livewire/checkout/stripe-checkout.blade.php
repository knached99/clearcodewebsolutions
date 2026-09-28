@assets
    <script src="https://js.stripe.com/v3/"></script>
@endassets

<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- ============================================================
            PAGE HEADER
        ============================================================= --}}
        <div class="mb-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Secure Checkout
                    </p>

                    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">
                        Complete Your Payment
                    </h1>

                    <p class="mt-2 text-sm text-slate-600">
                        Review your order, provide your billing details, sign the agreement, and complete payment securely.
                    </p>
                </div>

                <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm">
                    <svg
                        class="h-4 w-4 text-emerald-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 11c2.21 0 4-1.79 4-4S14.21 3 12 3 8 4.79 8 7s1.79 4 4 4Z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5.5 21a6.5 6.5 0 0 1 13 0"
                        />
                    </svg>

                    Secure Checkout
                </div>
            </div>
        </div>

        {{-- ============================================================
            GLOBAL SUCCESS STATE
        ============================================================= --}}
        @if ($checkoutSuccessful)
            <div class="mb-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-6">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                        <svg
                            class="h-6 w-6 text-emerald-600"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m5 13 4 4L19 7"
                            />
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-emerald-900">
                            Payment Received
                        </h2>

                        <p class="mt-1 text-sm text-emerald-700">
                            Your payment was successfully processed.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============================================================
            CHECKOUT ERROR
        ============================================================= --}}
        @if ($checkoutError)
            <div class="mb-8 rounded-2xl border border-red-200 bg-red-50 p-5">
                <div class="flex items-start gap-3">
                    <svg
                        class="mt-0.5 h-5 w-5 shrink-0 text-red-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v4m0 4h.01M10.29 3.86l-7.82 13.5A1 1 0 0 0 3.34 19h17.32a1 1 0 0 0 .87-1.64l-7.82-13.5a1 1 0 0 0-3.42 0Z"
                        />
                    </svg>

                    <div>
                        <h3 class="font-semibold text-red-900">
                            Payment Error
                        </h3>

                        <p class="mt-1 text-sm leading-6 text-red-700">
                            {{ $checkoutError }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px]">

            {{-- ========================================================
                LEFT SIDE
            ========================================================= --}}
            <div class="space-y-8">

                {{-- ====================================================
                    BILLING INFORMATION
                ===================================================== --}}
                <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
                                <svg
                                    class="h-5 w-5 text-slate-700"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 10.5V21h18V10.5"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 3 2.5 9.5h19L12 3Z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-semibold text-slate-900">
                                    Billing Information
                                </h2>

                                <p class="mt-0.5 text-sm text-slate-500">
                                    Enter the billing information associated with this payment.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 sm:p-8">
                        <div class="grid gap-5 sm:grid-cols-2">

                            {{-- Billing Name --}}
                            <div class="sm:col-span-1">
                                <label
                                    for="billing-name"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Full Name
                                </label>

                                <input
                                    id="billing-name"
                                    type="text"
                                    wire:model.defer="billingName"
                                    autocomplete="name"
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                                    placeholder="John Doe"
                                />

                                @error('billingName')
                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Billing Email --}}
                            <div class="sm:col-span-1">
                                <label
                                    for="billing-email"
                                    class="mb-2 block text-sm font-medium text-slate-700"
                                >
                                    Email Address
                                </label>

                                <input
                                    id="billing-email"
                                    type="email"
                                    wire:model.defer="billingEmail"
                                    autocomplete="email"
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 placeholder-slate-400 outline-none transition focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                                    placeholder="john@example.com"
                                />

                                @error('billingEmail')
                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>

                        <div class="mt-5 rounded-xl bg-slate-50 p-4">
                            <div class="flex items-start gap-3">
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-slate-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 8.5 12 14l9-5.5M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"
                                    />
                                </svg>

                                <p class="text-sm leading-6 text-slate-600">
                                    Your email address will be used for your payment receipt and checkout confirmation.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ====================================================
                    SIGNATURE
                    KEEPING SIGNATURE CAPTURE SECTION
                ===================================================== --}}
                @if ($signatureRequired)
                    <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
                                    <svg
                                        class="h-5 w-5 text-slate-700"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m14 6 4 4M4 20l4.5-1 10-10a2.12 2.12 0 0 0-3-3l-10 10L4 20Z"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">
                                        Agreement & Signature
                                    </h2>

                                    <p class="mt-0.5 text-sm text-slate-500">
                                        Please review and sign before completing payment.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 sm:p-8">

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <p class="text-sm leading-6 text-slate-700">
                                    By signing below, you acknowledge that you have reviewed and agree to the applicable invoice and agreement terms.
                                </p>
                            </div>

                            {{-- =================================================
                                SIGNATURE PAD
                                PRESERVED AS PART OF THE UI
                            ================================================== --}}
                            <div class="mt-6">
                                <div class="mb-2 flex items-center justify-between">
                                    <label class="text-sm font-medium text-slate-700">
                                        Signature
                                    </label>

                                    <button
                                        type="button"
                                        id="clear-signature"
                                        class="text-sm font-medium text-slate-500 transition hover:text-slate-900"
                                    >
                                        Clear
                                    </button>
                                </div>

                                <div class="overflow-hidden rounded-2xl border border-slate-300 bg-white">
                                    <canvas
                                        id="signature-pad"
                                        class="block h-56 w-full touch-none"
                                    ></canvas>
                                </div>

                                <input
                                    type="hidden"
                                    id="signature-data"
                                    wire:model="signatureData"
                                />

                                <div class="mt-2 flex items-center justify-between gap-4">
                                    <p class="text-xs text-slate-500">
                                        Sign using your mouse, trackpad, or finger.
                                    </p>

                                    <span
                                        id="signature-status"
                                        class="text-xs font-medium text-slate-400"
                                    >
                                        Not signed
                                    </span>
                                </div>

                                @error('signatureData')
                                    <p class="mt-2 text-sm text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Existing agreement/signature state --}}
                            @if ($hasSignedAgreement)
                                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                    <div class="flex items-start gap-3">
                                        <svg
                                            class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m5 13 4 4L19 7"
                                            />
                                        </svg>

                                        <div>
                                            <p class="text-sm font-semibold text-emerald-900">
                                                Agreement Signed
                                            </p>

                                            <p class="mt-1 text-sm text-emerald-700">
                                                Your signature has been captured.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </section>
                @endif

                {{-- ====================================================
                    PAYMENT SECTION
                ===================================================== --}}
                <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
                                    <svg
                                        class="h-5 w-5 text-slate-700"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <rect
                                            width="18"
                                            height="14"
                                            x="3"
                                            y="5"
                                            rx="2"
                                            ry="2"
                                        />
                                        <path
                                            d="M3 10h18"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">
                                        Payment
                                    </h2>

                                    <p class="mt-0.5 text-sm text-slate-500">
                                        Select your preferred payment method below.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                Secure payment
                            </div>
                        </div>
                    </div>

                    <div class="p-6 sm:p-8">

                        {{-- =================================================
                            PAYMENT METHOD ORDER / UI RAIL

                            This is visual only. Stripe remains responsible
                            for secure method selection inside the Element.
                        ================================================== --}}
                        <div class="mb-6">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">
                                        Payment methods
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Available methods are shown based on your device, browser, currency, and Stripe configuration.
                                    </p>
                                </div>

                                <div class="hidden shrink-0 items-center gap-1 sm:flex">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-900"></span>
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                                </div>
                            </div>

                            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                                <div class="flex gap-3 overflow-x-auto px-3 py-3 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">

                                    <div class="min-w-[150px] rounded-xl border border-slate-300 bg-white px-4 py-3 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100">
                                                <svg
                                                    class="h-5 w-5 text-slate-700"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <rect
                                                        width="20"
                                                        height="14"
                                                        x="2"
                                                        y="5"
                                                        rx="2"
                                                    />
                                                    <path d="M2 10h20" />
                                                </svg>
                                            </div>

                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">
                                                    Card
                                                </p>

                                                <p class="text-xs text-slate-500">
                                                    Visa, Mastercard, etc.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="min-w-[150px] rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100">
                                                <svg
                                                    class="h-5 w-5 text-slate-700"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M3 10h18M5 10v8m4-8v8m6-8v8m4-8v8M3 21h18M4 7l8-4 8 4v3H4V7Z"
                                                    />
                                                </svg>
                                            </div>

                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">
                                                    Bank
                                                </p>

                                                <p class="text-xs text-slate-500">
                                                    US bank account
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="min-w-[150px] rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100">
                                                <span class="text-sm font-bold text-slate-800">
                                                    
                                                </span>
                                            </div>

                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">
                                                    Apple Pay
                                                </p>

                                                <p class="text-xs text-slate-500">
                                                    Fast checkout
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="min-w-[150px] rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100">
                                                <span class="text-sm font-bold text-slate-800">
                                                    G
                                                </span>
                                            </div>

                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">
                                                    Google Pay
                                                </p>

                                                <p class="text-xs text-slate-500">
                                                    Fast checkout
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="min-w-[150px] rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100">
                                                <span class="text-sm font-bold text-slate-800">
                                                    A
                                                </span>
                                            </div>

                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">
                                                    Affirm
                                                </p>

                                                <p class="text-xs text-slate-500">
                                                    Pay over time
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="min-w-[150px] rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100">
                                                <span class="text-sm font-bold text-slate-800">
                                                    K
                                                </span>
                                            </div>

                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">
                                                    Klarna
                                                </p>

                                                <p class="text-xs text-slate-500">
                                                    Flexible payments
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="min-w-[150px] rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100">
                                                <span class="text-sm font-bold text-slate-800">
                                                    $
                                                </span>
                                            </div>

                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">
                                                    Cash App
                                                </p>

                                                <p class="text-xs text-slate-500">
                                                    Pay with Cash App
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="min-w-[150px] rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100">
                                                <span class="text-sm font-bold text-slate-800">
                                                    a
                                                </span>
                                            </div>

                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">
                                                    Amazon Pay
                                                </p>

                                                <p class="text-xs text-slate-500">
                                                    Fast checkout
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- =================================================
                            STRIPE PAYMENT ELEMENT

                            IMPORTANT:
                            This remains the existing Payment Element.
                            The payment confirmation logic below is unchanged.
                        ================================================== --}}
                        <div class="rounded-2xl border border-slate-200 bg-white">
                            <div class="border-b border-slate-100 px-5 py-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            Choose a payment method
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Stripe securely handles your payment information.
                                        </p>
                                    </div>

                                    <svg
                                        class="h-5 w-5 text-slate-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 15v2m-6 0h12a1 1 0 0 0 1-1v-5a7 7 0 1 0-14 0v5a1 1 0 0 0 1 1Z"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <div class="p-5">
                                <div
                                    id="payment-element"
                                    class="min-h-[240px]"
                                ></div>

                                <div
                                    id="payment-element-loading"
                                    class="flex min-h-[240px] items-center justify-center"
                                >
                                    <div class="text-center">
                                        <div class="mx-auto h-8 w-8 animate-spin rounded-full border-4 border-slate-200 border-t-slate-800"></div>

                                        <p class="mt-3 text-sm font-medium text-slate-700">
                                            Loading payment methods…
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Securely connecting to Stripe.
                                        </p>
                                    </div>
                                </div>

                                <div
                                    id="payment-element-error"
                                    class="mt-4 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                                ></div>
                            </div>
                        </div>

                        {{-- =================================================
                            PAYMENT SECURITY NOTICE
                        ================================================== --}}
                        <div class="mt-5 flex items-start gap-3 rounded-xl bg-slate-50 p-4">
                            <svg
                                class="mt-0.5 h-5 w-5 shrink-0 text-slate-500"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 3 5 6v5c0 4.8 2.9 8.1 7 10 4.1-1.9 7-5.2 7-10V6l-7-3Z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m9 12 2 2 4-4"
                                />
                            </svg>

                            <p class="text-xs leading-5 text-slate-600">
                                Your payment details are securely processed by Stripe. Your full card or bank details are never stored on this website.
                            </p>
                        </div>

                        {{-- =================================================
                            SUBMIT PAYMENT
                        ================================================== --}}
                        <div class="mt-7">
                            <button
                                id="submit-payment"
                                type="button"
                                wire:loading.attr="disabled"
                                wire:target="paymentCompleted,validateBilling"
                                class="group relative flex w-full items-center justify-center gap-3 rounded-2xl bg-slate-900 px-6 py-4 text-base font-semibold text-white shadow-lg shadow-slate-900/10 transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <span
                                    id="submit-payment-spinner"
                                    class="hidden h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>

                                <span id="submit-payment-text">
                                    Pay {{ $formattedTotal }}
                                </span>

                                <svg
                                    class="h-5 w-5 transition-transform group-hover:translate-x-0.5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5 12h14M13 6l6 6-6 6"
                                    />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="text-xs leading-5 text-slate-500">
                                By clicking the payment button, you authorize the payment for the amount shown above.
                            </p>
                        </div>

                    </div>
                </section>

            </div>

            {{-- ========================================================
                RIGHT SIDE — ORDER SUMMARY
            ========================================================= --}}
            <aside class="lg:sticky lg:top-8 lg:self-start">
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Order Summary
                                </p>

                                <h2 class="mt-1 text-xl font-bold text-slate-900">
                                    Invoice #{{ $invoice->id }}
                                </h2>
                            </div>

                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100">
                                <svg
                                    class="h-5 w-5 text-slate-700"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M7 3h10a2 2 0 0 1 2 2v16l-7-3-7 3V5a2 2 0 0 1 2-2Z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">

                        {{-- Customer --}}
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                Customer
                            </p>

                            <p class="mt-1 text-sm font-semibold text-slate-900">
                                {{ $invoice->customer?->name ?? $billingName }}
                            </p>

                            @if ($invoice->customer?->email ?? $billingEmail)
                                <p class="mt-1 break-all text-sm text-slate-500">
                                    {{ $invoice->customer?->email ?? $billingEmail }}
                                </p>
                            @endif
                        </div>

                        {{-- Ticket --}}
                        @if ($invoice->ticket)
                            <div class="mt-4 rounded-2xl border border-slate-200 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                                    Ticket
                                </p>

                                <p class="mt-1 text-sm font-semibold text-slate-900">
                                    {{ $invoice->ticket->name ?? $invoice->ticket->title ?? 'Service' }}
                                </p>
                            </div>
                        @endif

                        {{-- Items --}}
                        <div class="mt-6">
                            <div class="mb-3 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-900">
                                    Items
                                </h3>

                                <span class="text-xs font-medium text-slate-400">
                                    {{ $invoice->items?->count() ?? 0 }}
                                </span>
                            </div>

                            <div class="space-y-4">
                                @forelse ($invoice->items ?? [] as $item)
                                    @php
                                        $quantity = (int) ($item->quantity ?? 1);

                                        $itemTotal =
                                            $item->total_amount
                                            ?? $item->amount
                                            ?? null;

                                        $unitAmount =
                                            $item->unit_amount
                                            ?? $item->price
                                            ?? 0;

                                        if ($itemTotal === null) {
                                            $itemTotal = ((int) $unitAmount) * max($quantity, 1);
                                        }

                                        $itemTotal = (int) $itemTotal;
                                    @endphp

                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-slate-900">
                                                {{ $item->name ?? $item->description ?? 'Item' }}
                                            </p>

                                            @if ($quantity > 1)
                                                <p class="mt-1 text-xs text-slate-500">
                                                    Qty {{ $quantity }}
                                                </p>
                                            @endif
                                        </div>

                                        <p class="shrink-0 text-sm font-semibold text-slate-900">
                                            ${{ number_format($itemTotal / 100, 2) }}
                                        </p>
                                    </div>
                                @empty
                                    <div class="rounded-xl bg-slate-50 p-4">
                                        <p class="text-sm text-slate-500">
                                            No line items available.
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="my-6 border-t border-slate-200"></div>

                        {{-- Total --}}
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-slate-500">
                                    Total Due
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    USD
                                </p>
                            </div>

                            <p class="text-3xl font-bold tracking-tight text-slate-900">
                                {{ $formattedTotal }}
                            </p>
                        </div>

                        {{-- Secure note --}}
                        <div class="mt-6 rounded-2xl border border-slate-200 p-4">
                            <div class="flex items-start gap-3">
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-slate-500"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 11V7m0 8h.01M5.5 10.5a6.5 6.5 0 1 1 13 0c0 3.2-2.3 5.4-3.9 6.6a4.2 4.2 0 0 1-4.2 0c-1.6-1.2-3.9-3.4-3.9-6.6Z"
                                    />
                                </svg>

                                <div>
                                    <p class="text-sm font-semibold text-slate-900">
                                        Secure checkout
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        Payment processing is handled securely by Stripe.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </aside>

        </div>
    </div>
</div>

@script
<script>
document.addEventListener('livewire:init', () => {

    let stripe = null;
    let elements = null;
    let paymentElement = null;

    let paymentElementReady = false;
    let paymentSubmitting = false;

    const clientSecret = @js($clientSecret);
    const billingName = @js($billingName);
    const billingEmail = @js($billingEmail);

    const paymentElementContainer = document.getElementById('payment-element');
    const paymentElementLoading = document.getElementById('payment-element-loading');
    const paymentElementError = document.getElementById('payment-element-error');
    const submitPaymentButton = document.getElementById('submit-payment');
    const submitPaymentText = document.getElementById('submit-payment-text');
    const submitPaymentSpinner = document.getElementById('submit-payment-spinner');

    function setPaymentError(message = '') {
        if (!paymentElementError) {
            return;
        }

        if (!message) {
            paymentElementError.classList.add('hidden');
            paymentElementError.textContent = '';
            return;
        }

        paymentElementError.textContent = message;
        paymentElementError.classList.remove('hidden');
    }

    function setSubmitting(isSubmitting) {
        paymentSubmitting = isSubmitting;

        if (!submitPaymentButton) {
            return;
        }

        submitPaymentButton.disabled = isSubmitting || !paymentElementReady;

        if (submitPaymentSpinner) {
            submitPaymentSpinner.classList.toggle('hidden', !isSubmitting);
        }

        if (submitPaymentText) {
            submitPaymentText.textContent = isSubmitting
                ? 'Processing Payment…'
                : @js('Pay ' . $formattedTotal);
        }
    }

    function initStripe() {
        if (!clientSecret) {
            setPaymentError('Unable to initialize secure payment. Missing payment client secret.');
            if (paymentElementLoading) {
                paymentElementLoading.classList.add('hidden');
            }
            return;
        }

        if (typeof window.Stripe !== 'function') {
            setPaymentError('Stripe could not be loaded. Please refresh the page and try again.');
            if (paymentElementLoading) {
                paymentElementLoading.classList.add('hidden');
            }
            return;
        }

        try {
            stripe = Stripe(@js(config('services.stripe.key')));

            elements = stripe.elements({
                clientSecret: clientSecret,

                appearance: {
                    theme: 'stripe',

                    variables: {
                        colorPrimary: '#0f172a',
                        colorBackground: '#ffffff',
                        colorText: '#0f172a',
                        colorTextSecondary: '#64748b',
                        colorDanger: '#dc2626',
                        fontFamily: 'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                        borderRadius: '14px',
                        spacingUnit: '4px',
                    },

                    rules: {
                        '.Input': {
                            border: '1px solid #cbd5e1',
                            boxShadow: 'none',
                        },

                        '.Input:focus': {
                            border: '1px solid #0f172a',
                            boxShadow: '0 0 0 2px rgba(15, 23, 42, 0.08)',
                        },

                        '.Label': {
                            fontWeight: '600',
                        },
                    },
                },
            });

            paymentElement = elements.create('payment', {

                /*
                 * UI ONLY:
                 * accordion prevents Stripe's collapsed/dropdown-style
                 * presentation.
                 *
                 * Payment confirmation logic remains unchanged.
                 */
                layout: {
                    type: 'accordion',
                    defaultCollapsed: false,
                    radios: true,
                },

                /*
                 * Requested order:
                 *
                 * 1. Card
                 * 2. Bank
                 * 3. Apple Pay
                 * 4. Google Pay
                 * 5. Affirm
                 * 6. Klarna
                 * 7. Cash App
                 * 8. Amazon Pay
                 *
                 * Zelle is intentionally omitted.
                 */
                paymentMethodOrder: [
                    'card',
                    'us_bank_account',
                    'apple_pay',
                    'google_pay',
                    'affirm',
                    'klarna',
                    'cashapp',
                    'amazon_pay',
                ],

                wallets: {
                    applePay: 'auto',
                    googlePay: 'auto',
                    link: 'never',
                },

                defaultValues: {
                    billingDetails: {
                        name: billingName || undefined,
                        email: billingEmail || undefined,
                    },
                },

                fields: {
                    billingDetails: {
                        name: 'never',
                        email: 'never',
                        phone: 'auto',
                        address: 'auto',
                    },
                },
            });

            paymentElement.on('ready', () => {
                paymentElementReady = true;

                if (paymentElementLoading) {
                    paymentElementLoading.classList.add('hidden');
                }

                setPaymentError('');

                if (!paymentSubmitting) {
                    submitPaymentButton.disabled = false;
                }

                window.dispatchEvent(
                    new CustomEvent('stripe-checkout-ready')
                );
            });

            paymentElement.on('change', (event) => {
                if (event?.error?.message) {
                    setPaymentError(event.error.message);
                } else {
                    setPaymentError('');
                }
            });

            paymentElement.on('loaderror', (event) => {
                paymentElementReady = false;

                if (paymentElementLoading) {
                    paymentElementLoading.classList.add('hidden');
                }

                setPaymentError(
                    event?.error?.message ||
                    'Unable to load the payment form.'
                );

                if (submitPaymentButton) {
                    submitPaymentButton.disabled = true;
                }
            });

            paymentElement.mount('#payment-element');

        } catch (error) {
            console.error(error);

            if (paymentElementLoading) {
                paymentElementLoading.classList.add('hidden');
            }

            setPaymentError(
                'Unable to initialize the secure payment form.'
            );
        }
    }

    /*
     * ================================================================
     * EXISTING PAYMENT FLOW
     * ================================================================
     *
     * This remains the same:
     *
     * 1. Validate billing
     * 2. elements.submit()
     * 3. stripe.confirmPayment()
     * 4. Verify returned PaymentIntent
     * 5. Livewire paymentCompleted()
     *
     * No signature/payment backend methods are changed here.
     */
    async function submitPayment() {

        if (paymentSubmitting) {
            return;
        }

        if (!stripe || !elements || !paymentElementReady) {
            setPaymentError(
                'The secure payment form is not ready yet. Please wait a moment and try again.'
            );
            return;
        }

        try {
            setPaymentError('');
            setSubmitting(true);

            await $wire.validateBilling();

            const submitResult = await elements.submit();

            if (submitResult?.error) {
                setPaymentError(
                    submitResult.error.message ||
                    'Please review your payment information.'
                );

                setSubmitting(false);
                return;
            }

            const result = await stripe.confirmPayment({
                elements,
                clientSecret,

                confirmParams: {
                    return_url: window.location.href,
                },

                redirect: 'if_required',
            });

            if (result.error) {
                setPaymentError(
                    result.error.message ||
                    'Your payment could not be completed.'
                );

                setSubmitting(false);
                return;
            }

            const paymentIntent = result.paymentIntent;

            if (!paymentIntent) {
                setPaymentError(
                    'Stripe did not return a payment result. Please try again.'
                );

                setSubmitting(false);
                return;
            }

            if (
                paymentIntent.status === 'succeeded' ||
                paymentIntent.status === 'processing'
            ) {
                await $wire.paymentCompleted(paymentIntent.id);
                return;
            }

            setPaymentError(
                'Your payment is not complete yet. Please review the payment status and try again.'
            );

            setSubmitting(false);

        } catch (error) {
            console.error(error);

            setPaymentError(
                error?.message ||
                'Something went wrong while processing your payment.'
            );

            setSubmitting(false);
        }
    }

    if (submitPaymentButton) {
        submitPaymentButton.addEventListener('click', submitPayment);
    }

    /*
     * ================================================================
     * SIGNATURE PAD
     * ================================================================
     *
     * Signature capture preserved.
     */
    const signatureCanvas = document.getElementById('signature-pad');
    const signatureInput = document.getElementById('signature-data');
    const clearSignatureButton = document.getElementById('clear-signature');
    const signatureStatus = document.getElementById('signature-status');

    let signatureCtx = null;
    let signatureDrawing = false;
    let signatureHasContent = false;

    function resizeSignatureCanvas() {
        if (!signatureCanvas) {
            return;
        }

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = signatureCanvas.getBoundingClientRect();

        const existingData = signatureHasContent
            ? signatureCanvas.toDataURL('image/png')
            : null;

        signatureCanvas.width = rect.width * ratio;
        signatureCanvas.height = rect.height * ratio;

        signatureCtx = signatureCanvas.getContext('2d');

        if (!signatureCtx) {
            return;
        }

        signatureCtx.scale(ratio, ratio);
        signatureCtx.lineWidth = 2;
        signatureCtx.lineCap = 'round';
        signatureCtx.lineJoin = 'round';
        signatureCtx.strokeStyle = '#0f172a';

        if (existingData) {
            const image = new Image();

            image.onload = () => {
                signatureCtx.drawImage(
                    image,
                    0,
                    0,
                    rect.width,
                    rect.height
                );
            };

            image.src = existingData;
        }
    }

    function getSignaturePosition(event) {
        const rect = signatureCanvas.getBoundingClientRect();

        if (event.touches && event.touches.length) {
            return {
                x: event.touches[0].clientX - rect.left,
                y: event.touches[0].clientY - rect.top,
            };
        }

        return {
            x: event.clientX - rect.left,
            y: event.clientY - rect.top,
        };
    }

    function startSignature(event) {
        if (!signatureCtx) {
            return;
        }

        event.preventDefault();

        signatureDrawing = true;

        const position = getSignaturePosition(event);

        signatureCtx.beginPath();
        signatureCtx.moveTo(position.x, position.y);
    }

    function drawSignature(event) {
        if (!signatureDrawing || !signatureCtx) {
            return;
        }

        event.preventDefault();

        const position = getSignaturePosition(event);

        signatureCtx.lineTo(position.x, position.y);
        signatureCtx.stroke();

        signatureHasContent = true;

        if (signatureStatus) {
            signatureStatus.textContent = 'Signature captured';
            signatureStatus.classList.remove(
                'text-slate-400'
            );

            signatureStatus.classList.add(
                'text-emerald-600'
            );
        }

        if (signatureInput) {
            signatureInput.value =
                signatureCanvas.toDataURL('image/png');

            signatureInput.dispatchEvent(
                new Event('input', {
                    bubbles: true,
                })
            );
        }

        /*
         * Keep Livewire signature data synced.
         */
        if (window.Livewire) {
            try {
                $wire.set(
                    'signatureData',
                    signatureCanvas.toDataURL('image/png')
                );
            } catch (error) {
                console.debug(
                    'Unable to sync signatureData immediately.',
                    error
                );
            }
        }
    }

    function endSignature(event) {
        if (!signatureDrawing) {
            return;
        }

        event?.preventDefault();

        signatureDrawing = false;

        if (signatureCtx) {
            signatureCtx.closePath();
        }
    }

    function clearSignature() {
        if (!signatureCanvas || !signatureCtx) {
            return;
        }

        const rect = signatureCanvas.getBoundingClientRect();

        signatureCtx.clearRect(
            0,
            0,
            rect.width,
            rect.height
        );

        signatureHasContent = false;

        if (signatureInput) {
            signatureInput.value = '';

            signatureInput.dispatchEvent(
                new Event('input', {
                    bubbles: true,
                })
            );
        }

        if (signatureStatus) {
            signatureStatus.textContent = 'Not signed';

            signatureStatus.classList.add(
                'text-slate-400'
            );

            signatureStatus.classList.remove(
                'text-emerald-600'
            );
        }

        if (window.Livewire) {
            try {
                $wire.set('signatureData', '');
            } catch (error) {
                console.debug(
                    'Unable to clear signatureData immediately.',
                    error
                );
            }
        }
    }

    if (signatureCanvas) {
        resizeSignatureCanvas();

        signatureCanvas.addEventListener(
            'mousedown',
            startSignature
        );

        signatureCanvas.addEventListener(
            'mousemove',
            drawSignature
        );

        signatureCanvas.addEventListener(
            'mouseup',
            endSignature
        );

        signatureCanvas.addEventListener(
            'mouseleave',
            endSignature
        );

        signatureCanvas.addEventListener(
            'touchstart',
            startSignature,
            { passive: false }
        );

        signatureCanvas.addEventListener(
            'touchmove',
            drawSignature,
            { passive: false }
        );

        signatureCanvas.addEventListener(
            'touchend',
            endSignature,
            { passive: false }
        );

        signatureCanvas.addEventListener(
            'touchcancel',
            endSignature,
            { passive: false }
        );

        window.addEventListener(
            'resize',
            resizeSignatureCanvas
        );
    }

    if (clearSignatureButton) {
        clearSignatureButton.addEventListener(
            'click',
            clearSignature
        );
    }

    /*
     * Initialize Stripe.
     */
    initStripe();

});
</script>
@endscript