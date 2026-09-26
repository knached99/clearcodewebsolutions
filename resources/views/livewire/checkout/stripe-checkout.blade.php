<div class="cc-checkout">
    @assets
        <script src="https://js.stripe.com/v3/" defer></script>
    @endassets

    <style>
    html,
    body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100%;
        min-height: 100%;
        background: #0d0a11 !important;
        color: #f5eef8 !important;
    }

    body {
        min-height: 100vh;
    }

    #root,
    #__next,
    main {
        min-height: 100vh;
        background: #0d0a11 !important;
    }

    .cc-checkout {
        background: #0d0a11;
        min-height: 100vh;
        border-radius: 24px;
        width: 100%;
        max-width: 1100px;
        margin: 0 auto;
        padding: 76px 20px 56px;
        color: #f5eef8;
        font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        box-sizing: border-box;
    }

    .cc-checkout * {
        box-sizing: border-box;
    }

    .cc-checkout__header {
        margin-top: 0;
        margin-bottom: 24px;
        padding: 14px 2px 0;
        border-top: 1px solid #2a2230;
    }

    .cc-checkout__title {
        margin: 0 0 6px;
        font-size: 29px;
        line-height: 1.15;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .cc-checkout__subtitle {
        margin: 0;
        max-width: 620px;
        color: #a99bad;
        font-size: 14px;
        line-height: 1.6;
    }

    .cc-checkout__layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(300px, 360px);
        gap: 22px;
        align-items: start;
    }

    .cc-checkout__card {
        min-width: 0;
        background: #17121c;
        border: 1px solid #2b2332;
        border-radius: 20px;
        padding: 22px;
        box-shadow: 0 14px 36px rgba(0, 0, 0, 0.28);
    }

    .cc-checkout__section {
        margin-bottom: 22px;
    }

    .cc-checkout__section:last-child {
        margin-bottom: 0;
    }

    .cc-checkout__section-title {
        margin: 0 0 13px;
        font-size: 17px;
        line-height: 1.3;
        font-weight: 700;
    }

    .cc-checkout__payment-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 12px;
    }

    .cc-checkout__payment-heading .cc-checkout__section-title {
        margin-bottom: 3px;
    }

    .cc-checkout__payment-hint {
        margin: 0;
        color: #978b9c;
        font-size: 12px;
        line-height: 1.45;
    }

    .cc-checkout__payment-badge {
        flex: 0 0 auto;
        padding: 5px 9px;
        border: 1px solid #382c3f;
        border-radius: 999px;
        background: #201826;
        color: #c88cc9;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
    }

    .cc-checkout__billing-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 14px;
    }

    .cc-checkout__field {
        margin-bottom: 0;
    }

    .cc-checkout__field:last-child {
        margin-bottom: 0;
    }

    .cc-checkout__agreement {
        padding: 18px;
        border: 1px solid #2e2437;
        border-radius: 18px;
        background: rgba(23, 18, 28, 0.9);
    }

    .cc-checkout__agreement h2 {
        margin: 0 0 12px;
        font-size: 20px;
        line-height: 1.3;
    }

    .cc-checkout__agreement-copy {
        margin: 0 0 16px;
        color: #b7aabc;
        font-size: 14px;
        line-height: 1.7;
    }

    .cc-checkout__agreement-document {
        margin-bottom: 16px;
        padding: 16px;
        border: 1px solid #352b39;
        border-radius: 12px;
        background: rgba(12, 9, 16, 0.6);
        color: #e7dff1;
        font-size: 14px;
        line-height: 1.7;
    }

    .cc-checkout__signature-canvas-wrap {
        position: relative;
        margin-top: 12px;
        border: 1px solid #3a3042;
        border-radius: 12px;
        background: #120e16;
        overflow: hidden;
    }

    .cc-checkout__signature-canvas {
        display: block;
        width: 100%;
        height: 180px;
        cursor: crosshair;
        background: #120e16;
    }

    .cc-checkout__signature-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 14px;
    }

    .cc-checkout__secondary-btn,
    .cc-checkout__primary-btn {
        min-height: 42px;
        padding: 0 16px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: opacity 0.15s ease, transform 0.15s ease;
    }

    .cc-checkout__secondary-btn {
        border: 1px solid #3a3042;
        background: transparent;
        color: #f5eef8;
    }

    .cc-checkout__primary-btn {
        border: 0;
        background: linear-gradient(135deg, #b247b2, #7c3aed);
        color: #fff;
    }

    .cc-checkout__primary-btn:hover,
    .cc-checkout__secondary-btn:hover {
        transform: translateY(-1px);
    }

    .cc-checkout__label {
        display: block;
        margin-bottom: 6px;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
    }

    .cc-checkout__input {
        display: block;
        width: 100%;
        min-height: 46px;
        padding: 10px 13px;
        border: 1px solid #3a3042;
        border-radius: 12px;
        background: #17121c;
        color: #f5eef8;
        font-size: 15px;
        line-height: 1.4;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .cc-checkout__input::placeholder {
        color: #716575;
    }

    .cc-checkout__input:focus {
        border-color: #b247b2;
        box-shadow: 0 0 0 3px rgba(178, 71, 178, 0.20);
    }

    .cc-checkout__error {
        margin-top: 7px;
        color: #ff8b8b;
        font-size: 13px;
        line-height: 1.45;
    }

    .cc-checkout__status {
        margin-bottom: 11px;
        padding: 9px 11px;
        border: 1px solid #332a3a;
        border-radius: 12px;
        background: #211a27;
        color: #a99bad;
        font-size: 13px;
        line-height: 1.5;
    }

    .cc-checkout__status--error {
        background: #211a27;
        border-color: #332a3a;
        color: #a99bad;
    }

    .cc-checkout__status--success {
        background: #16261c;
        border-color: #2e5a3b;
        color: #8bd19b;
    }

    .cc-checkout__status--pending {
        background: #2a2418;
        border-color: #5a4a2b;
        color: #e2c477;
    }

    .cc-checkout__stripe-box {
        width: 100%;
        padding: 12px;
        background: #120e16;
        border: 1px solid #302738;
        border-radius: 16px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
    }

    .cc-checkout__stripe-loading {
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #a99bad;
        font-size: 14px;
        text-align: center;
    }

    .cc-checkout__stripe-form {
        width: 100%;
    }

    #payment-element {
        width: 100%;
        min-height: 125px;
    }

    .cc-checkout__pay {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        min-height: 50px;
        margin-top: 14px;
        padding: 13px 20px;
        border: 0;
        border-radius: 14px;
        background: #b247b2;
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.2;
        cursor: pointer;
        transition: opacity 0.15s ease, transform 0.15s ease;
    }

    .cc-checkout__pay:hover:not(:disabled) {
        transform: translateY(-1px);
    }

    .cc-checkout__pay:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
    }

    .cc-checkout__pay-label,
    .cc-checkout__pay-loading {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .cc-checkout__secure {
        margin-top: 10px;
        color: #a99bad;
        text-align: center;
        font-size: 12px;
        line-height: 1.5;
    }

    .cc-checkout__items {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .cc-checkout__item {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 11px 0;
        border-bottom: 1px solid #2b2332;
    }

    .cc-checkout__item:first-child {
        padding-top: 0;
    }

    .cc-checkout__item:last-child {
        border-bottom: 0;
    }

    .cc-checkout__item-name {
        min-width: 0;
        font-size: 14px;
        line-height: 1.5;
    }

    .cc-checkout__item-quantity {
        margin-top: 3px;
        color: #a99bad;
        font-size: 12px;
        line-height: 1.4;
    }

    .cc-checkout__item-price {
        white-space: nowrap;
        font-size: 14px;
        font-weight: 600;
    }

    .cc-checkout__total {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #2b2332;
        font-size: 20px;
        line-height: 1.3;
        font-weight: 700;
    }

    [hidden] {
        display: none !important;
    }

    .cc-checkout__order-card {
        position: sticky;
        top: 24px;
    }

    @media (max-width: 850px) {
        .cc-checkout {
            padding: 64px 16px 44px;
        }

        .cc-checkout__header {
            margin-top: 30px;
            margin-bottom: 18px;
        }

        .cc-checkout__layout {
            grid-template-columns: 1fr;
        }

        .cc-checkout__order-card {
            position: static;
        }

        .cc-checkout__payment-heading {
            align-items: center;
        }
    }

    @media (max-width: 620px) {
        .cc-checkout__billing-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .cc-checkout__card {
            padding: 18px;
        }

        .cc-checkout__title {
            font-size: 26px;
        }

        .cc-checkout__stripe-box {
            padding: 10px;
        }
    }

    .cc-checkout__success {
        position: relative;
        overflow: hidden;
        width: 100%;
        max-width: 720px;
        margin: 40px auto 0;
        padding: 56px 42px 48px;
        background:
            radial-gradient(
                circle at 50% 0%,
                rgba(178, 71, 178, 0.14),
                transparent 42%
            ),
            #17121c;
        border: 1px solid #2f2637;
        border-radius: 24px;
        box-shadow:
            0 24px 60px rgba(0, 0, 0, 0.35),
            inset 0 1px 0 rgba(255, 255, 255, 0.03);
        text-align: center;
    }

    .cc-checkout__success::before {
        content: "";
        position: absolute;
        top: -90px;
        left: 50%;
        width: 260px;
        height: 260px;
        transform: translateX(-50%);
        background: rgba(178, 71, 178, 0.08);
        filter: blur(60px);
        pointer-events: none;
    }

    .cc-checkout__success-icon {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 76px;
        height: 76px;
        margin: 0 auto 26px;
        border: 1px solid rgba(178, 71, 178, 0.35);
        border-radius: 50%;
        background:
            linear-gradient(
                145deg,
                rgba(178, 71, 178, 0.18),
                rgba(178, 71, 178, 0.06)
            );
        color: #d477d4;
        box-shadow:
            0 0 0 8px rgba(178, 71, 178, 0.05),
            0 12px 30px rgba(178, 71, 178, 0.12);
    }

    .cc-checkout__success-icon svg {
        width: 38px;
        height: 38px;
    }

    .cc-checkout__success-content {
        position: relative;
        z-index: 1;
    }

    .cc-checkout__success-eyebrow {
        display: inline-block;
        margin-bottom: 10px;
        color: #c88cc9;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.4;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .cc-checkout__success-title {
        margin: 0;
        color: #f8f1fa;
        font-size: 34px;
        line-height: 1.15;
        font-weight: 750;
        letter-spacing: -0.03em;
    }

    .cc-checkout__success-message {
        max-width: 560px;
        margin: 16px auto 0;
        color: #b7aabc;
        font-size: 15px;
        line-height: 1.7;
    }

    .cc-checkout__success-message strong {
        color: #f5eef8;
        font-weight: 700;
    }

    .cc-checkout__success-email {
        display: flex;
        align-items: center;
        gap: 13px;
        max-width: 500px;
        margin: 28px auto 0;
        padding: 15px 17px;
        border: 1px solid #34293a;
        border-radius: 15px;
        background: rgba(13, 10, 17, 0.55);
        text-align: left;
    }

    .cc-checkout__success-email > svg {
        flex: 0 0 auto;
        width: 22px;
        height: 22px;
        color: #c88cc9;
    }

    .cc-checkout__success-email > div {
        min-width: 0;
    }

    .cc-checkout__success-email-label {
        display: block;
        margin-bottom: 3px;
        color: #827587;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .cc-checkout__success-email strong {
        display: block;
        overflow: hidden;
        color: #eee5f0;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.45;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cc-checkout__success-note {
        max-width: 520px;
        margin: 20px auto 0;
        color: #8f8294;
        font-size: 12px;
        line-height: 1.65;
    }

    @media (max-width: 620px) {
        .cc-checkout__success {
            margin-top: 24px;
            padding: 42px 22px 34px;
            border-radius: 20px;
        }

        .cc-checkout__success-icon {
            width: 68px;
            height: 68px;
            margin-bottom: 22px;
        }

        .cc-checkout__success-icon svg {
            width: 34px;
            height: 34px;
        }

        .cc-checkout__success-title {
            font-size: 28px;
        }

        .cc-checkout__success-message {
            font-size: 14px;
        }

        .cc-checkout__success-email {
            padding: 14px;
        }
    }
    </style>

    @if ($checkoutSuccessful)
        <div class="cc-checkout__success">
            <div class="cc-checkout__success-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path
                        d="M5 12.5L9.2 16.5L19 7.5"
                        stroke="currentColor"
                        stroke-width="2.4"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>

            <div class="cc-checkout__success-content">
                <span class="cc-checkout__success-eyebrow">
                    Payment confirmed
                </span>

                <h2 class="cc-checkout__success-title">
                    Checkout successful
                </h2>

                <p class="cc-checkout__success-message">
                    Thank you! Your payment of
                    <strong>${{ $formattedTotal }}</strong>
                    has been received successfully.
                </p>

                <div class="cc-checkout__success-email">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path
                            d="M4 6.5h16v11H4z"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linejoin="round"
                        />
                        <path
                            d="m5 8 7 5 7-5"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                    <div>
                        <span class="cc-checkout__success-email-label">
                            Invoice sent to
                        </span>

                        <strong>
                            {{ $billingEmail }}
                        </strong>
                    </div>
                </div>

                <p class="cc-checkout__success-note">
                    Please check your inbox for your invoice and payment
                    confirmation. If you don't see it within a few minutes,
                    check your spam or junk folder.
                </p>
            </div>
        </div>
    @else
        <div class="cc-checkout__header">
            <h1 class="cc-checkout__title">Secure Checkout</h1>
            <p class="cc-checkout__subtitle">
                Choose how you want to pay. Your payment details are securely processed by Stripe.
            </p>
        </div>

        <div class="cc-checkout__layout">
            <div class="cc-checkout__card">
                <div class="cc-checkout__section">
                    <h2 class="cc-checkout__section-title">Billing Information</h2>

                    <div class="cc-checkout__billing-grid">
                        <div class="cc-checkout__field">
                            <label for="billing-name" class="cc-checkout__label">
                                Billing Name
                            </label>
                            <input
                                id="billing-name"
                                type="text"
                                wire:model="billingName"
                                autocomplete="name"
                                placeholder="John Smith"
                                class="cc-checkout__input"
                            >
                            @error('billingName')
                                <div class="cc-checkout__error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="cc-checkout__field">
                            <label for="billing-email" class="cc-checkout__label">
                                Email Address
                            </label>
                            <input
                                id="billing-email"
                                type="email"
                                wire:model="billingEmail"
                                autocomplete="email"
                                placeholder="you@example.com"
                                class="cc-checkout__input"
                            >
                            @error('billingEmail')
                                <div class="cc-checkout__error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @if ($signatureRequired && ! $hasSignedAgreement)
                    <div class="cc-checkout__section">
                        <div class="cc-checkout__agreement">
                            <h2>Service agreement</h2>
                            <p class="cc-checkout__agreement-copy">
                                This recurring service agreement confirms the monthly billing terms and the customer’s acceptance of the recurring charges before payment is submitted.
                            </p>

                            <div class="cc-checkout__agreement-document">
                                By signing below, the customer acknowledges the recurring monthly charges for the selected services and confirms their understanding that billing will continue automatically for the minimum term stated in the invoice.
                            </div>

                            <div class="cc-checkout__signature-canvas-wrap">
                                <canvas id="agreement-signature-pad" class="cc-checkout__signature-canvas" width="760" height="180"></canvas>
                            </div>

                            <div class="cc-checkout__signature-actions">
                                <button type="button" class="cc-checkout__secondary-btn" data-clear-signature>
                                    Clear
                                </button>
                                <button type="button" class="cc-checkout__primary-btn" data-signature-submit>
                                    Approve &amp; continue
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="cc-checkout__section">
                    <div class="cc-checkout__payment-heading">
                        <div>
                            <h2 class="cc-checkout__section-title">Payment Method</h2>
                            <p class="cc-checkout__payment-hint">Choose a method, then enter your payment details.</p>
                        </div>
                        <span class="cc-checkout__payment-badge">Secure</span>
                    </div>

                    <div
                        data-stripe-status
                        class="cc-checkout__status @if($checkoutError) cc-checkout__status--error @elseif($paymentStatus === 'succeeded') cc-checkout__status--success @elseif($paymentStatus === 'processing') cc-checkout__status--pending @endif"
                    >
                        @if ($checkoutError)
                            {{ $checkoutError }}
                        @elseif ($paymentStatus === 'succeeded')
                            Payment completed successfully.
                        @elseif ($paymentStatus === 'processing')
                            Your payment is being processed. You do not need to submit the payment again.
                        @elseif ($clientSecret)
                            Select a payment method below.
                        @else
                            Starting secure payment form...
                        @endif
                    </div>

                    @if ($clientSecret && $paymentStatus !== 'succeeded' && $paymentStatus !== 'processing')
                        <div class="cc-checkout__stripe-box">
                            <div class="cc-checkout__stripe-loading" data-stripe-loading>
                                Loading secure payment form...
                            </div>

                            <div
                                class="cc-checkout__stripe-form"
                                data-stripe-payment-element
                                wire:ignore
                                hidden
                            >
                                <div id="payment-element"></div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="cc-checkout__pay"
                            data-stripe-pay
                            disabled
                        >
                            <span class="cc-checkout__pay-label" data-stripe-pay-label>
                                Pay ${{ $formattedTotal }}
                            </span>
                            <span class="cc-checkout__pay-loading" data-stripe-pay-loading hidden>
                                Processing...
                            </span>
                        </button>

                        <div class="cc-checkout__secure">
                            Your card or bank details are entered directly into Stripe's secure payment form.
                        </div>
                    @endif
                </div>
            </div>

            <div class="cc-checkout__card cc-checkout__order-card">
                <div class="cc-checkout__section">
                    <h2 class="cc-checkout__section-title">Order Summary</h2>

                    <ul class="cc-checkout__items">
                        @forelse($invoice->items as $item)
                            <li class="cc-checkout__item">
                                <div class="cc-checkout__item-name">
                                    <div class="cc-checkout__item-title">
                                        {{ $item->name ?? $item->description ?? 'Item' }}

                                        @if(($item->is_subscription ?? false) && ! empty($item->stripe_price_id))
                                            <span class="cc-checkout__addon-badge">
                                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path
                                                        d="M17 7.5A5 5 0 1 0 18.5 17"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                        stroke-linecap="round"
                                                    />
                                                    <path
                                                        d="M18.5 13v4h-4"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    />
                                                </svg>

                                                <span>Recurring</span>
                                            </span>
                                        @endif
                                    </div>

                                    @if(isset($item->quantity))
                                        <div class="cc-checkout__item-quantity">
                                            Qty: {{ $item->quantity }}
                                        </div>
                                    @endif
                                </div>

                                <div class="cc-checkout__item-price">
                                    ${{ $this->formatItemAmount($item) }}
                                </div>
                            </li>
                        @empty
                            <li class="cc-checkout__item">
                                <div class="cc-checkout__item-name">
                                    No invoice items found.
                                </div>
                            </li>
                        @endforelse
                    </ul>

                    <div class="cc-checkout__total">
                        <span>Total</span>
                        <span>${{ $formattedTotal }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if ($signatureRequired && ! $hasSignedAgreement)
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const canvas = document.getElementById('agreement-signature-pad');
                    const clearButton = document.querySelector('[data-clear-signature]');
                    const submitButton = document.querySelector('[data-signature-submit]');

                    if (!canvas || !clearButton || !submitButton) {
                        return;
                    }

                    const context = canvas.getContext('2d');
                    let drawing = false;

                    const resizeCanvas = () => {
                        const ratio = window.devicePixelRatio || 1;
                        const rect = canvas.getBoundingClientRect();
                        canvas.width = rect.width * ratio;
                        canvas.height = rect.height * ratio;
                        context.setTransform(ratio, 0, 0, ratio, 0, 0);
                        context.lineWidth = 2.5;
                        context.lineCap = 'round';
                        context.lineJoin = 'round';
                        context.strokeStyle = '#f5eef8';
                    };

                    const clearCanvas = () => {
                        context.clearRect(0, 0, canvas.width, canvas.height);
                        context.fillStyle = '#120e16';
                        context.fillRect(0, 0, canvas.width, canvas.height);
                    };

                    const startDrawing = (event) => {
                        drawing = true;
                        const rect = canvas.getBoundingClientRect();
                        const x = (event.clientX - rect.left) * (canvas.width / rect.width);
                        const y = (event.clientY - rect.top) * (canvas.height / rect.height);
                        context.beginPath();
                        context.moveTo(x, y);
                    };

                    const draw = (event) => {
                        if (!drawing) {
                            return;
                        }

                        const rect = canvas.getBoundingClientRect();
                        const x = (event.clientX - rect.left) * (canvas.width / rect.width);
                        const y = (event.clientY - rect.top) * (canvas.height / rect.height);
                        context.lineTo(x, y);
                        context.stroke();
                    };

                    const stopDrawing = () => {
                        drawing = false;
                    };

                    resizeCanvas();
                    clearCanvas();

                    canvas.addEventListener('pointerdown', startDrawing);
                    canvas.addEventListener('pointermove', draw);
                    canvas.addEventListener('pointerup', stopDrawing);
                    canvas.addEventListener('pointerleave', stopDrawing);
                    clearButton.addEventListener('click', clearCanvas);

                    submitButton.addEventListener('click', function () {
                        const blank = context.getImageData(0, 0, canvas.width, canvas.height).data
                            .every((pixel) => pixel === 0 || pixel === 255 || pixel === 18);

                        if (blank) {
                            window.alert('Please sign the agreement before continuing.');
                            return;
                        }

                        const componentId = document.querySelector('[wire\\:id]')?.getAttribute('wire:id');
                        const signatureData = canvas.toDataURL('image/png');

                        if (!componentId) {
                            window.alert('The checkout component is unavailable. Please refresh the page.');
                            return;
                        }

                        window.Livewire.find(componentId).call('saveSignature', signatureData);
                    });
                });
            </script>
        @endif

        @if ($clientSecret && $paymentStatus !== 'succeeded' && $paymentStatus !== 'processing')
            <script>
                (() => {
                    let wireRoot = document.querySelector('[wire\\:id]');
                    const getRoot = () => document.querySelector('[wire\\:id]') || document.body;
                    const root = getRoot();

                    let paymentElementContainer = null;
                    let paymentElementMount = null;
                    let loadingEl = null;
                    let statusEl = null;
                    let payButton = null;
                    let payLabel = null;
                    let payLoading = null;
                    let billingNameInput = null;
                    let billingEmailInput = null;

                    const refreshDomRefs = () => {
                        wireRoot = document.querySelector('[wire\\:id]') || wireRoot || getRoot();

                        paymentElementContainer = document.querySelector('[data-stripe-payment-element]');
                        paymentElementMount = document.querySelector('#payment-element');
                        loadingEl = document.querySelector('[data-stripe-loading]');
                        statusEl = document.querySelector('[data-stripe-status]');
                        payButton = document.querySelector('[data-stripe-pay]');
                        payLabel = document.querySelector('[data-stripe-pay-label]');
                        payLoading = document.querySelector('[data-stripe-pay-loading]');
                        billingNameInput = document.querySelector('#billing-name');
                        billingEmailInput = document.querySelector('#billing-email');
                    };

                    const waitForLivewire = async () => {
                        const maxAttempts = 40;

                        for (let attempt = 0; attempt < maxAttempts; attempt++) {
                            if (typeof window.Livewire !== 'undefined' && typeof window.Livewire.find === 'function') {
                                return true;
                            }

                            await new Promise((resolve) => setTimeout(resolve, 250));
                        }

                        return false;
                    };

                    const getComponent = () => {
                        if (typeof window.Livewire === 'undefined' || typeof window.Livewire.find !== 'function') {
                            return null;
                        }

                        const componentId = wireRoot?.getAttribute('wire:id');

                        if (!componentId) {
                            return null;
                        }

                        return window.Livewire.find(componentId);
                    };

                    const component = getComponent();
                    const publishableKey = @js(config('services.stripe.key'));
                    const clientSecret = @js($clientSecret);
                    const invoiceTotal = @js($totalInCents);
                    const initialPaymentIntentId = @js($paymentIntentId);
                    const initialPaymentStatus = @js($paymentStatus);

                    refreshDomRefs();

                    let stripe = null;
                    let elements = null;
                    let paymentElement = null;
                    let ready = false;
                    let loading = false;
                    const componentKey = `cc-checkout-${wireRoot ? wireRoot.getAttribute('wire:id') || 'global' : 'global'}`;
                    const globalCheckoutState = window.__ccCheckoutState ??= {};
                    const componentState = globalCheckoutState[componentKey] ??= {
                        initialized: false,
                        mountTarget: null,
                        buttonBound: false,
                    };

                    const setComponentValue = (name, value) => {
                        const liveComponent = getComponent();

                        if (!liveComponent) {
                            return;
                        }

                        if (typeof liveComponent.set === 'function') {
                            liveComponent.set(name, value);
                            return;
                        }

                        if (typeof liveComponent.$set === 'function') {
                            liveComponent.$set(name, value);
                        }
                    };

                    const callComponentMethod = async (method, ...args) => {
                        const liveComponent = getComponent();

                        if (!liveComponent) {
                            throw new Error('Checkout component is unavailable.');
                        }

                        if (typeof liveComponent.call === 'function') {
                            return await liveComponent.call(method, ...args);
                        }

                        if (typeof liveComponent[method] === 'function') {
                            return await liveComponent[method](...args);
                        }

                        throw new Error('Checkout component method is unavailable.');
                    };

                    const setStatus = (message, type = 'normal') => {
                        if (!statusEl) {
                            return;
                        }

                        statusEl.textContent = message;
                        statusEl.classList.remove(
                            'cc-checkout__status--error',
                            'cc-checkout__status--success',
                            'cc-checkout__status--pending'
                        );

                        if (type === 'success') {
                            statusEl.classList.add('cc-checkout__status--success');
                        } else if (type === 'pending') {
                            statusEl.classList.add('cc-checkout__status--pending');
                        }
                    };

                    const setButtonLoading = (isLoading) => {
                        loading = isLoading;

                        if (!payButton || !payLabel || !payLoading) {
                            return;
                        }

                        payButton.disabled = isLoading || !ready;
                        payLabel.hidden = isLoading;
                        payLoading.hidden = !isLoading;
                    };

                    const setReady = (isReady) => {
                        ready = isReady;

                        if (payButton) {
                            payButton.disabled = loading || !ready;
                        }
                    };

                    const fail = () => {
                        const userMessage = 'The secure payment form is temporarily unavailable. Please refresh the page and try again.';

                        setReady(false);
                        setStatus(userMessage, 'error');

                        if (loadingEl) {
                            loadingEl.hidden = false;
                            loadingEl.textContent = userMessage;
                        }

                        if (paymentElementContainer) {
                            paymentElementContainer.hidden = true;
                        }
                    };

                    const waitForStripe = async () => {
                        const maxAttempts = 40;

                        for (let attempt = 0; attempt < maxAttempts; attempt++) {
                            if (typeof window.Stripe === 'function') {
                                return true;
                            }

                            await new Promise((resolve) => setTimeout(resolve, 250));
                        }

                        return false;
                    };

                    const initializeStripe = async () => {
                        if (!(await waitForLivewire())) {
                            fail();
                            return;
                        }

                        refreshDomRefs();

                        if (!clientSecret) {
                            fail();
                            return;
                        }

                        if (!publishableKey) {
                            fail();
                            return;
                        }

                        if (!paymentElementContainer || !paymentElementMount || !payButton) {
                            setTimeout(initializeStripe, 250);
                            return;
                        }

                        if (componentState.initialized && componentState.mountTarget === paymentElementMount) {
                            return;
                        }

                        try {
                            if (!(await waitForStripe())) {
                                fail();
                                return;
                            }

                            setStatus('Creating secure payment form...');

                            stripe = window.Stripe(publishableKey);

                            elements = stripe.elements({
                                clientSecret,
                                appearance: {
                                    theme: 'night',
                                    variables: {
                                        colorPrimary: '#c05cc4',
                                        colorBackground: '#17121c',
                                        colorText: '#f5eef8',
                                        colorTextSecondary: '#a99bad',
                                        colorTextPlaceholder: '#7f7384',
                                        colorDanger: '#a99bad',
                                        borderRadius: '12px',
                                        fontFamily: 'Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                                    },
                                },
                            });

                            paymentElement = elements.create('payment', {
                                layout: {
                                    type: 'tabs',
                                },
                                paymentMethodOrder: [
                                    'card',
                                    'us_bank_account',
                                    'afterpay_clearpay',
                                    'klarna',
                                ],
                                defaultValues: {
                                    billingDetails: {
                                        name: billingNameInput?.value?.trim() || '',
                                        email: billingEmailInput?.value?.trim() || '',
                                    },
                                },
                                fields: {
                                    billingDetails: {
                                        name: 'always',
                                        email: 'auto',
                                        address: 'if_required',
                                    },
                                },
                                wallets: {
                                    applePay: 'auto',
                                    googlePay: 'auto',
                                    link: 'auto',
                                },
                            });

                            paymentElement.on('loaderstart', () => {
                                setStatus('Loading secure payment options...');
                            });

                            paymentElement.on('ready', () => {
                                setReady(true);
                                setStatus('Select a payment method and complete the payment details.');

                                if (loadingEl) {
                                    loadingEl.hidden = true;
                                }

                                paymentElementContainer.hidden = false;
                            });

                            paymentElement.on('change', (event) => {
                                if (event.error?.message) {
                                    setStatus(event.error.message, 'error');
                                    return;
                                }

                                if (ready && !loading) {
                                    setStatus('Payment form ready.');
                                }
                            });

                            paymentElement.on('loaderror', () => {
                                fail();
                            });

                            if (paymentElementMount.dataset.ccCheckoutMounted === 'true') {
                                return;
                            }

                            paymentElement.mount(paymentElementMount);
                            paymentElementMount.dataset.ccCheckoutMounted = 'true';
                            componentState.initialized = true;
                            componentState.mountTarget = paymentElementMount;
                        } catch (error) {
                            fail();
                        }
                    };

                    const submitPayment = async () => {
                        if (loading || !ready || !stripe || !elements || !paymentElement) {
                            return;
                        }

                        const name = billingNameInput?.value?.trim() || '';
                        const email = billingEmailInput?.value?.trim() || '';

                        setButtonLoading(true);
                        setStatus('Validating your billing information...');

                        try {
                            setComponentValue('billingName', name);
                            setComponentValue('billingEmail', email);

                            await callComponentMethod('validateBilling');

                            setStatus('Confirming your payment with Stripe...');

                            const returnUrl = window.location.href;
                            const result = await stripe.confirmPayment({
                                elements,
                                confirmParams: {
                                    return_url: returnUrl,
                                },
                                redirect: 'if_required',
                            });

                            if (result.error) {
                                throw new Error(
                                    result.error.message || 'Your payment could not be completed.'
                                );
                            }

                            if (!result.paymentIntent) {
                                throw new Error('Stripe did not return a PaymentIntent after confirmation.');
                            }

                            if (typeof invoiceTotal === 'number' && result.paymentIntent.amount !== invoiceTotal) {
                                throw new Error('The confirmed Stripe amount does not match the invoice total.');
                            }

                            if (result.paymentIntent.id !== initialPaymentIntentId) {
                                throw new Error('The confirmed Stripe payment could not be matched to this checkout.');
                            }

                            const normalizedStatus = result.paymentIntent.status;
                            const finalStatuses = ['succeeded', 'processing', 'requires_capture', 'requires_confirmation'];

                            if (normalizedStatus === 'requires_action') {
                                setButtonLoading(false);
                                setReady(false);
                                setStatus('Additional verification is required by your bank or card issuer before the subscription can be activated.', 'pending');
                                return;
                            }

                            if (!finalStatuses.includes(normalizedStatus)) {
                                throw new Error(
                                    `Stripe returned an unexpected payment status: ${normalizedStatus}`
                                );
                            }

                            if (normalizedStatus === 'succeeded') {
                                const verifiedStatus = await callComponentMethod('paymentCompleted', result.paymentIntent.id);

                                if (verifiedStatus !== 'succeeded') {
                                    throw new Error('Stripe reported a payment state that could not be verified.');
                                }

                                setButtonLoading(false);
                                setReady(false);
                                setStatus('Payment completed successfully.', 'success');

                                if (payButton) {
                                    payButton.disabled = true;
                                }

                                if (paymentElementContainer) {
                                    paymentElementContainer.hidden = true;
                                }

                                if (loadingEl) {
                                    loadingEl.hidden = false;
                                    loadingEl.textContent = 'Payment completed successfully.';
                                }

                                return;
                            }

                            if (normalizedStatus === 'processing' || normalizedStatus === 'requires_capture' || normalizedStatus === 'requires_confirmation') {
                                await callComponentMethod('paymentCompleted', result.paymentIntent.id);

                                setButtonLoading(false);
                                setReady(false);
                                setStatus(
                                    'Your payment was submitted and is still processing. You do not need to submit it again.',
                                    'pending'
                                );

                                if (payButton) {
                                    payButton.disabled = true;
                                }

                                return;
                            }
                        } catch (error) {
                            const message = error?.message || 'Your payment could not be completed.';
                            setStatus(message, 'error');
                            setButtonLoading(false);
                            setReady(Boolean(paymentElement));
                        }
                    };

                    const attachPayButtonHandler = () => {
                        refreshDomRefs();

                        if (!payButton) {
                            setTimeout(attachPayButtonHandler, 250);
                            return;
                        }

                        if (payButton.dataset.ccCheckoutBound === 'true') {
                            return;
                        }

                        payButton.addEventListener('click', submitPayment);
                        payButton.dataset.ccCheckoutBound = 'true';
                        componentState.buttonBound = true;
                    };

                    if (initialPaymentStatus === 'succeeded') {
                        setReady(false);
                        return;
                    }

                    attachPayButtonHandler();
                    initializeStripe();
                })();
            </script>
        @endif
    @endif
</div>

