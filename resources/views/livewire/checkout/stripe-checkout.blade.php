@assets
    <script src="https://js.stripe.com/v3/"></script>
@endassets

<div class="cc-checkout">

    <style>
        html,
        body {
            background: oklch(13% 0.028 261.692) !important;
        }

        .cc-checkout {
            width: 100%;
            max-width: 980px;
            margin: 0 auto;
            color: #f5f2f7;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        .cc-checkout__shell {
            background: #0b090d;
            border: 1px solid #27212b;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 18px 60px rgba(0, 0, 0, .45);
        }

        .cc-checkout__header {
            padding: 28px 30px 24px;
            border-bottom: 1px solid #27212b;
            background:
                radial-gradient(
                    circle at top right,
                    rgba(208, 139, 212, .10),
                    transparent 35%
                ),
                #100c12;
        }

        .cc-checkout__eyebrow {
            margin: 0 0 8px;
            color: #b9adb9;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .cc-checkout__title {
            margin: 0;
            color: #ffffff;
            font-size: 28px;
            line-height: 1.15;
            font-weight: 800;
        }

        .cc-checkout__subtitle {
            margin: 9px 0 0;
            color: #aaa0ad;
            font-size: 14px;
            line-height: 1.6;
        }

        .cc-checkout__body {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(320px, .85fr);
            gap: 24px;
            padding: 26px;
            background: #0b090d;
        }

        .cc-checkout__main,
        .cc-checkout__sidebar {
            min-width: 0;
        }

        .cc-checkout__section {
            margin-bottom: 22px;
            padding: 20px;
            border: 1px solid #29222d;
            border-radius: 14px;
            background: #131017;
        }

        .cc-checkout__section:last-child {
            margin-bottom: 0;
        }

        .cc-checkout__section-title {
            margin: 0 0 5px;
            color: #fff;
            font-size: 17px;
            line-height: 1.3;
            font-weight: 800;
        }

        .cc-checkout__section-text {
            margin: 0 0 18px;
            color: #9f969f;
            font-size: 13px;
            line-height: 1.55;
        }

        .cc-checkout__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .cc-checkout__field {
            min-width: 0;
        }

        .cc-checkout__field--full {
            grid-column: 1 / -1;
        }

        .cc-checkout__label {
            display: block;
            margin-bottom: 7px;
            color: #d8d0da;
            font-size: 12px;
            font-weight: 700;
        }

        .cc-checkout__input {
            width: 100%;
            height: 46px;
            padding: 0 13px;
            border: 1px solid #3b313f;
            border-radius: 10px;
            outline: none;
            background: #0d0a0f;
            color: #fff;
            font-size: 14px;
            transition:
                border-color .15s ease,
                box-shadow .15s ease;
        }

        .cc-checkout__input:focus {
            border-color: #9b5aa0;
            box-shadow: 0 0 0 3px rgba(155, 90, 160, .12);
        }

        .cc-checkout__input::placeholder {
            color: #6f6871;
        }

        .cc-checkout__items {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .cc-checkout__item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 13px 14px;
            border: 1px solid #2c2530;
            border-radius: 11px;
            background: #0e0b10;
        }

        .cc-checkout__item-info {
            min-width: 0;
        }

        .cc-checkout__item-name {
            margin: 0;
            color: #f7f4f8;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.4;
        }

        .cc-checkout__item-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 6px;
        }

        .cc-checkout__item-description {
            margin: 5px 0 0;
            color: #8f8791;
            font-size: 11px;
            line-height: 1.4;
        }

        .cc-checkout__item-price {
            flex: 0 0 auto;
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            white-space: nowrap;
        }

        .cc-checkout__addon-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px;
            border: 1px solid #4a3150;
            border-radius: 999px;
            background: #211629;
            color: #d08bd4;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: .03em;
            white-space: nowrap;
        }

        .cc-checkout__addon-badge svg {
            width: 13px;
            height: 13px;
            flex: 0 0 13px;
        }

        .cc-checkout__summary {
            display: flex;
            flex-direction: column;
            gap: 11px;
        }

        .cc-checkout__summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            color: #aaa1ad;
            font-size: 13px;
        }

        .cc-checkout__summary-row strong {
            color: #fff;
            font-weight: 700;
        }

        .cc-checkout__summary-total {
            margin-top: 6px;
            padding-top: 15px;
            border-top: 1px solid #302733;
        }

        .cc-checkout__summary-total span:first-child {
            color: #fff;
            font-size: 15px;
            font-weight: 800;
        }

        .cc-checkout__summary-total strong {
            color: #fff;
            font-size: 23px;
            font-weight: 900;
        }

        .cc-checkout__agreement {
            position: relative;
        }

        .cc-checkout__agreement-copy {
            margin-bottom: 14px;
            padding: 14px;
            border: 1px solid #342937;
            border-radius: 11px;
            background: #0e0b10;
            color: #aaa1ab;
            font-size: 12px;
            line-height: 1.6;
        }

        .cc-checkout__signature {
            overflow: hidden;
            border: 1px solid #3b313f;
            border-radius: 11px;
            background: #0d0a0f;
        }

        .cc-checkout__signature canvas {
            display: block;
            width: 100%;
            height: 180px;
            touch-action: none;
            cursor: crosshair;
            background: #0d0a0f;
        }

        .cc-checkout__signature-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 11px;
            border-top: 1px solid #2f2833;
            background: #151116;
        }

        .cc-checkout__signature-hint {
            color: #756d79;
            font-size: 11px;
        }

        .cc-checkout__button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 0 15px;
            border: 0;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition:
                opacity .15s ease,
                transform .15s ease,
                background .15s ease;
        }

        .cc-checkout__button:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        .cc-checkout__button:disabled {
            opacity: .45;
            cursor: not-allowed;
            transform: none;
        }

        .cc-checkout__button--primary {
            background: #b56ab9;
            color: #fff;
        }

        .cc-checkout__button--primary:hover:not(:disabled) {
            background: #c277c5;
        }

        .cc-checkout__button--secondary {
            background: #2a222d;
            color: #ddd4df;
        }

        .cc-checkout__button--secondary:hover:not(:disabled) {
            background: #342a37;
        }

        .cc-checkout__button--pay {
            width: 100%;
            margin-top: 14px;
            min-height: 52px;
            font-size: 14px;
        }

        /*
        |--------------------------------------------------------------------------
        | PAYMENT SLIDER
        |--------------------------------------------------------------------------
        */

        .cc-payment-slider {
            width: 100%;
        }

        .cc-payment-slider__tabs {
            display: flex;
            align-items: stretch;
            gap: 7px;

            margin-bottom: 14px;

            padding: 5px;

            border: 1px solid #29222d;
            border-radius: 13px;

            background: #0e0b10;

            overflow-x: auto;
            scrollbar-width: none;
        }

        .cc-payment-slider__tabs::-webkit-scrollbar {
            display: none;
        }

        .cc-payment-slider__tab {
            display: flex;
            align-items: center;
            justify-content: center;

            flex: 1 1 0;

            min-width: 115px;

            gap: 8px;

            padding: 10px 11px;

            border: 1px solid transparent;
            border-radius: 9px;

            background: transparent;

            color: #7f7581;

            cursor: pointer;

            text-align: left;

            transition:
                color .35s cubic-bezier(.22,1,.36,1),
                background .35s cubic-bezier(.22,1,.36,1),
                border-color .35s cubic-bezier(.22,1,.36,1),
                box-shadow .35s cubic-bezier(.22,1,.36,1),
                transform .35s cubic-bezier(.22,1,.36,1);
        }

        .cc-payment-slider__tab:hover {
            color: #d5cdd8;
        }

        .cc-payment-slider__tab.is-active {
            color: #fff;

            border-color: #3b2f3f;

            background:
                linear-gradient(
                    135deg,
                    rgba(208,139,212,.11),
                    rgba(208,139,212,.025)
                );

            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.035),
                0 7px 18px rgba(0,0,0,.16);
        }

        .cc-payment-slider__tab:active {
            transform: translateY(1px);
        }

        .cc-payment-slider__tab-icon {
            display: grid;
            place-items: center;

            width: 28px;
            height: 28px;

            flex: 0 0 28px;

            border: 1px solid rgba(255,255,255,.07);
            border-radius: 8px;

            background: rgba(255,255,255,.022);

            color: #8f8391;

            transition:
                color .35s ease,
                border-color .35s ease,
                background .35s ease;
        }

        .cc-payment-slider__tab.is-active
        .cc-payment-slider__tab-icon {
            color: #d08bd4;

            border-color:
                rgba(208,139,212,.20);

            background:
                rgba(208,139,212,.08);
        }

        .cc-payment-slider__tab-icon svg {
            width: 14px;
            height: 14px;
        }

        .cc-payment-slider__tab-copy {
            display: flex;
            flex-direction: column;

            gap: 2px;

            min-width: 0;
        }

        .cc-payment-slider__tab-copy strong {
            color: inherit;

            font-size: 11px;
            font-weight: 800;

            line-height: 1.15;
        }

        .cc-payment-slider__tab-copy span {
            color: #6f6771;

            font-size: 8px;
            line-height: 1.2;

            white-space: nowrap;
        }

        .cc-payment-slider__viewport {
            width: 100%;

            overflow: hidden;

            border-radius: 11px;
        }

        .cc-payment-slider__track {
            display: flex;

            width: 300%;

            transform:
                translate3d(0, 0, 0);

            transition:
                transform 650ms cubic-bezier(.22,1,.36,1);

            will-change: transform;
        }

        .cc-payment-slider__track.is-wallet {
            transform:
                translate3d(-33.333333%, 0, 0);
        }



        .cc-payment-slider__panel {
            width: 33.333333%;
            min-width: 33.333333%;

            box-sizing: border-box;
        }

        .cc-payment-slider__panel-inner {
            width: 100%;
            box-sizing: border-box;
        }


        /*
        |--------------------------------------------------------------------------
        | STRIPE CARD PANEL
        |--------------------------------------------------------------------------
        */

        .cc-checkout__stripe-box {
            min-height: 120px;
        }

        .cc-checkout__stripe-loading {
            display: flex;
            align-items: center;
            justify-content: center;

            min-height: 96px;

            color: #8f8792;

            font-size: 13px;
        }

        .cc-checkout__stripe-loading[hidden],
        .cc-checkout__stripe-payment[hidden] {
            display: none !important;
        }

        .cc-checkout__stripe-payment {
            min-height: 100px;
        }

        #payment-element {
            width: 100%;
        }


        /*
        |--------------------------------------------------------------------------
        | WALLET PANEL
        |--------------------------------------------------------------------------
        */

        .cc-payment-wallets {
            min-height: 150px;

            padding: 17px;

            border: 1px solid #29222d;
            border-radius: 11px;

            background:
                radial-gradient(
                    circle at 85% 15%,
                    rgba(208,139,212,.065),
                    transparent 40%
                ),
                #0e0b10;

            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.025);
        }

        .cc-payment-wallets__heading {
            display: flex;
            flex-direction: column;

            gap: 3px;

            margin-bottom: 15px;
        }

        .cc-payment-wallets__heading strong {
            color: #f5f2f7;

            font-size: 14px;
            font-weight: 800;
        }

        .cc-payment-wallets__heading span {
            color: #807783;

            font-size: 11px;
            line-height: 1.45;
        }

        #express-checkout-element {
            width: 100%;
        }

        .cc-payment-wallets__loading {
            display: flex;
            align-items: center;
            justify-content: center;

            min-height: 90px;

            color: #827985;

            font-size: 12px;
        }

        .cc-payment-wallets__loading[hidden] {
            display: none !important;
        }

        .cc-payment-wallets__unavailable {
            margin-top: 12px;

            padding: 11px 12px;

            border: 1px solid #302833;
            border-radius: 9px;

            background:
                rgba(255,255,255,.016);

            color: #817782;

            font-size: 11px;
            line-height: 1.5;
        }

        .cc-payment-wallets__unavailable[hidden] {
            display: none !important;
        }


        /*
        |--------------------------------------------------------------------------
        | ERRORS / STATUS
        |--------------------------------------------------------------------------
        */

        .cc-checkout__error {
            margin-top: 12px;

            padding: 12px 13px;

            border: 1px solid #5a3038;
            border-radius: 10px;

            background: #241216;

            color: #f1aeb8;

            font-size: 12px;
            line-height: 1.5;
        }

        .cc-checkout__error[hidden] {
            display: none !important;
        }

        .cc-checkout__status {
            margin-top: 12px;

            color: #918992;

            font-size: 11px;

            text-align: center;
        }


        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        .cc-checkout__success {
            padding: 42px 26px;

            background: #0b090d;

            text-align: center;
        }

        .cc-checkout__success-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            width: 54px;
            height: 54px;

            margin-bottom: 15px;

            border-radius: 50%;

            background: #17251a;

            color: #8fd48f;
        }

        .cc-checkout__success h2 {
            margin: 0;

            color: #fff;

            font-size: 25px;
        }

        .cc-checkout__success p {
            max-width: 560px;

            margin: 10px auto 0;

            color: #a59da8;

            font-size: 13px;

            line-height: 1.6;
        }

        .cc-checkout__small {
            color: #77707a;

            font-size: 10px;

            line-height: 1.5;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 820px) {

            .cc-checkout__body {
                grid-template-columns: 1fr;
            }

        }

        @media (max-width: 600px) {

            .cc-checkout__header {
                padding: 22px 18px 20px;
            }

            .cc-checkout__body {
                padding: 16px;
            }

            .cc-checkout__section {
                padding: 16px;
            }

            .cc-checkout__grid {
                grid-template-columns: 1fr;
            }

            .cc-checkout__field--full {
                grid-column: auto;
            }

            .cc-checkout__title {
                font-size: 23px;
            }

            .cc-checkout__item {
                flex-direction: column;
            }

            .cc-checkout__item-price {
                align-self: flex-end;
            }

            .cc-payment-slider__tab {
                min-width: 105px;
                padding: 9px 9px;
            }

            .cc-payment-slider__tab-copy span {
                display: none;
            }

            .cc-payment-slider__tab-copy strong {
                font-size: 10px;
            }

            .cc-payment-slider__tab-icon {
                width: 26px;
                height: 26px;
                flex-basis: 26px;
            }

        }
    </style>

    <div class="cc-checkout__shell">

        @if ($checkoutSuccessful)

            <div class="cc-checkout__success">

                <div class="cc-checkout__success-icon">
                    <svg
                        width="25"
                        height="25"
                        viewBox="0 0 24 24"
                        fill="none"
                        aria-hidden="true"
                    >
                        <path
                            d="m6 12 4 4 8-8"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>

                <h2>Payment successful</h2>

                <p>
                    Your payment for this invoice has been confirmed.
                    Your subscription, when applicable, will continue
                    according to the recurring items on the invoice.
                </p>

            </div>

        @else

            <div class="cc-checkout__header">

                <p class="cc-checkout__eyebrow">
                    Secure checkout
                </p>

                <h1 class="cc-checkout__title">
                    Complete your payment
                </h1>

                <p class="cc-checkout__subtitle">
                    Invoice #{{ $invoice->id }}
                </p>

            </div>


            <div class="cc-checkout__body">

                <main class="cc-checkout__main">

                    {{-- BILLING --}}

                    <section class="cc-checkout__section">

                        <h2 class="cc-checkout__section-title">
                            Billing information
                        </h2>

                        <p class="cc-checkout__section-text">
                            Enter the billing details that should be
                            associated with this payment.
                        </p>

                        <div class="cc-checkout__grid">

                            <div class="cc-checkout__field">

                                <label
                                    class="cc-checkout__label"
                                    for="billing-name"
                                >
                                    Full name
                                </label>

                                <input
                                    id="billing-name"
                                    type="text"
                                    class="cc-checkout__input"
                                    wire:model.live="billingName"
                                    autocomplete="name"
                                    placeholder="Your full name"
                                >

                            </div>


                            <div class="cc-checkout__field">

                                <label
                                    class="cc-checkout__label"
                                    for="billing-email"
                                >
                                    Email address
                                </label>

                                <input
                                    id="billing-email"
                                    type="email"
                                    class="cc-checkout__input"
                                    wire:model.live="billingEmail"
                                    autocomplete="email"
                                    placeholder="you@example.com"
                                >

                            </div>

                        </div>

                    </section>


                    {{-- AGREEMENT SIGNATURE --}}

                    @if ($signatureRequired && !$hasSignedAgreement)

                        <section
                            class="cc-checkout__section cc-checkout__agreement"
                            wire:ignore
                        >

                            <h2 class="cc-checkout__section-title">
                                Agreement signature
                            </h2>

                            <p class="cc-checkout__section-text">
                                This invoice includes recurring services.
                                Please sign the agreement below before
                                continuing to payment.
                            </p>

                            <div class="cc-checkout__agreement-copy">
                                By signing below, you acknowledge the
                                recurring services included in this invoice
                                and authorize payment according to the
                                displayed billing terms.
                            </div>

                            <div class="cc-checkout__signature">

                                <canvas
                                    id="agreement-signature-pad"
                                    width="760"
                                    height="180"
                                    aria-label="Agreement signature pad"
                                ></canvas>

                                <div
                                    class="cc-checkout__signature-toolbar"
                                >

                                    <span class="cc-checkout__signature-hint">
                                        Draw your signature above.
                                    </span>

                                    <button
                                        type="button"
                                        class="cc-checkout__button cc-checkout__button--secondary"
                                        data-clear-signature
                                    >
                                        Clear
                                    </button>

                                </div>

                            </div>

                            <button
                                type="button"
                                class="cc-checkout__button cc-checkout__button--primary"
                                data-signature-submit
                                style="width:100%; margin-top:12px;"
                            >
                                Sign agreement and continue
                            </button>

                        </section>

                    @endif


                    {{-- PAYMENT --}}

                    @if (!$signatureRequired || $hasSignedAgreement)

                        <section class="cc-checkout__section">

                            <h2 class="cc-checkout__section-title">
                                Payment
                            </h2>

                            <p class="cc-checkout__section-text">
                                Choose how you would like to pay.
                            </p>


                            <div
                                class="cc-payment-slider"
                                data-payment-slider
                            >

                                {{-- PAYMENT METHOD TABS --}}

                                <div class="cc-payment-slider__tabs">

                                    {{-- CARD --}}

                                    <button
                                        type="button"
                                        class="cc-payment-slider__tab is-active"
                                        data-payment-tab="card"
                                    >

                                        <span class="cc-payment-slider__tab-icon">

                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                aria-hidden="true"
                                            >
                                                <rect
                                                    x="3"
                                                    y="5"
                                                    width="18"
                                                    height="14"
                                                    rx="2"
                                                    stroke="currentColor"
                                                    stroke-width="1.6"
                                                />

                                                <path
                                                    d="M3 10h18"
                                                    stroke="currentColor"
                                                    stroke-width="1.6"
                                                />
                                            </svg>

                                        </span>

                                        <span class="cc-payment-slider__tab-copy">

                                            <strong>Card</strong>

                                            <span>
                                                Credit or debit
                                            </span>

                                        </span>

                                    </button>


                                    {{-- WALLETS --}}

                                    <button
                                        type="button"
                                        class="cc-payment-slider__tab"
                                        data-payment-tab="wallet"
                                    >

                                        <span class="cc-payment-slider__tab-icon">

                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                aria-hidden="true"
                                            >
                                                <rect
                                                    x="3"
                                                    y="5"
                                                    width="18"
                                                    height="14"
                                                    rx="3"
                                                    stroke="currentColor"
                                                    stroke-width="1.6"
                                                />

                                                <path
                                                    d="M16 12h3"
                                                    stroke="currentColor"
                                                    stroke-width="1.6"
                                                    stroke-linecap="round"
                                                />

                                                <circle
                                                    cx="16"
                                                    cy="12"
                                                    r=".9"
                                                    fill="currentColor"
                                                />
                                            </svg>

                                        </span>

                                        <span class="cc-payment-slider__tab-copy">

                                            <strong>Wallets</strong>

                                            <span>
                                                Apple / Google / PayPal
                                            </span>

                                        </span>

                                    </button>


                                {{-- SLIDER VIEWPORT --}}

                                <div class="cc-payment-slider__viewport">

                                    <div
                                        class="cc-payment-slider__track"
                                        data-payment-track
                                    >

                                        {{-- =====================================================
                                             CARD PANEL
                                        ====================================================== --}}

                                        <div class="cc-payment-slider__panel">

                                            <div
                                                class="cc-payment-slider__panel-inner"
                                            >

                                                <div class="cc-checkout__stripe-box">

                                                    @if (
                                                        $clientSecret
                                                        && $paymentStatus !== 'succeeded'
                                                        && $paymentStatus !== 'processing'
                                                    )

                                                        <div
                                                            data-stripe-loading
                                                            class="cc-checkout__stripe-loading"
                                                        >
                                                            Loading secure
                                                            payment form...
                                                        </div>

                                                        <div
                                                            data-stripe-payment-element
                                                            class="cc-checkout__stripe-payment"
                                                            wire:ignore
                                                            hidden
                                                        >
                                                            <div id="payment-element"></div>
                                                        </div>

                                                    @else

                                                        @if (
                                                            $paymentStatus === 'processing'
                                                        )

                                                            <div
                                                                class="cc-checkout__stripe-loading"
                                                            >
                                                                Payment is
                                                                processing...
                                                            </div>

                                                        @elseif ($paymentStatus !== 'succeeded')

                                                            <div
                                                                class="cc-checkout__stripe-loading"
                                                            >
                                                                Loading secure
                                                                payment form...
                                                            </div>

                                                        @endif

                                                    @endif

                                                </div>


                                                @if (
                                                    $clientSecret
                                                    && $paymentStatus !== 'succeeded'
                                                    && $paymentStatus !== 'processing'
                                                )

                                                    <button
                                                        type="button"
                                                        class="cc-checkout__button cc-checkout__button--primary cc-checkout__button--pay"
                                                        data-stripe-pay
                                                        disabled
                                                    >
                                                        Pay {{ $formattedTotal }}
                                                    </button>

                                                    <div
                                                        class="cc-checkout__error"
                                                        data-stripe-error
                                                        hidden
                                                    ></div>

                                                    <div
                                                        class="cc-checkout__status"
                                                        data-stripe-status
                                                    >
                                                        Secure Stripe payment
                                                        form is initializing...
                                                    </div>

                                                @endif

                                            </div>

                                        </div>


                                        {{-- =====================================================
                                             WALLET PANEL
                                        ====================================================== --}}

                                        <div class="cc-payment-slider__panel">

                                            <div
                                                class="cc-payment-slider__panel-inner"
                                            >

                                                <div class="cc-payment-wallets">

                                                    <div class="cc-payment-wallets__heading">

                                                        <strong>
                                                            Express checkout
                                                        </strong>

                                                        <span>
                                                            Pay instantly with
                                                            an eligible wallet.
                                                        </span>

                                                    </div>


                                                    <div
                                                        class="cc-payment-wallets__loading"
                                                        data-wallet-loading
                                                    >
                                                        Checking available
                                                        payment wallets...
                                                    </div>


                                                    <div
                                                        id="express-checkout-element"
                                                        wire:ignore
                                                    ></div>


                                                    <div
                                                        class="cc-payment-wallets__unavailable"
                                                        data-wallets-unavailable
                                                        hidden
                                                    >
                                                        Apple Pay, Google Pay,
                                                        and PayPal are not
                                                        currently available
                                                        for this browser,
                                                        device, or Stripe
                                                        account configuration.
                                                    </div>

                                                </div>

                                            </div>

                                        </div>


                    
                                    </div>

                                </div>

                            </div>

                        </section>

                    @endif


                    @if ($checkoutError)

                        <div class="cc-checkout__error">
                            {{ $checkoutError }}
                        </div>

                    @endif

                </main>


                {{-- ORDER SUMMARY --}}

                <aside class="cc-checkout__sidebar">

                    <section class="cc-checkout__section">

                        <h2 class="cc-checkout__section-title">
                            Order summary
                        </h2>

                        <p class="cc-checkout__section-text">
                            Invoice items
                        </p>


                        <div class="cc-checkout__items">

                            @foreach ($invoice->items as $item)

                                @php

                                    $itemDescription =
                                        $item->description
                                        ?? $item->name
                                        ?? $item->title
                                        ?? 'Invoice item';

                                    $itemTotal =
                                        $item->total
                                        ?? $item->amount
                                        ?? $item->unit_amount
                                        ?? $item->price
                                        ?? 0;

                                    $quantity = max(
                                        1,
                                        (int) ($item->quantity ?? 1)
                                    );

                                    $displayTotal =
                                        $item->total !== null
                                        && $item->total !== ''
                                            ? (float) $item->total
                                            : ((float) $itemTotal * $quantity);

                                    $displayTotalFormatted =
                                        number_format(
                                            $displayTotal / 100,
                                            2
                                        );

                                @endphp


                                <div
                                    class="cc-checkout__item"
                                    wire:key="checkout-item-{{ $item->id }}"
                                >

                                    <div class="cc-checkout__item-info">

                                        <p class="cc-checkout__item-name">
                                            {{ $itemDescription }}
                                        </p>

                                        <div class="cc-checkout__item-meta">

                                            @if ($item->is_subscription)

                                                <span
                                                    class="cc-checkout__addon-badge"
                                                >

                                                    <svg
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        aria-hidden="true"
                                                    >
                                                        <path
                                                            d="M20 7h-9a5 5 0 0 0-5 5v1"
                                                            stroke="currentColor"
                                                            stroke-width="1.8"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                        />

                                                        <path
                                                            d="m7 10-3 3 3 3"
                                                            stroke="currentColor"
                                                            stroke-width="1.8"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                        />

                                                        <path
                                                            d="M4 17h9a5 5 0 0 0 5-5v-1"
                                                            stroke="currentColor"
                                                            stroke-width="1.8"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                        />

                                                        <path
                                                            d="m17 14 3-3-3-3"
                                                            stroke="currentColor"
                                                            stroke-width="1.8"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                        />
                                                    </svg>

                                                    <span>
                                                        Recurring
                                                    </span>

                                                </span>

                                            @else

                                                <span
                                                    class="cc-checkout__small"
                                                >
                                                    One-time
                                                </span>

                                            @endif


                                            @if ($quantity > 1)

                                                <span
                                                    class="cc-checkout__small"
                                                >
                                                    Qty {{ $quantity }}
                                                </span>

                                            @endif

                                        </div>


                                        @if (
                                            !empty($item->description)
                                            && !empty($item->name)
                                        )

                                            <p
                                                class="cc-checkout__item-description"
                                            >
                                                {{ $item->description }}
                                            </p>

                                        @endif

                                    </div>


                                    <div class="cc-checkout__item-price">
                                        ${{ $displayTotalFormatted }}
                                    </div>

                                </div>

                            @endforeach

                        </div>


                        <div
                            class="cc-checkout__summary"
                            style="margin-top:18px;"
                        >

                            <div
                                class="cc-checkout__summary-row cc-checkout__summary-total"
                            >

                                <span>
                                    Total
                                </span>

                                <strong>
                                    ${{ $formattedTotal }}
                                </strong>

                            </div>

                        </div>

                    </section>

                </aside>

            </div>

        @endif

    </div>

</div>


@script

<script>
(() => {
    'use strict';

    const root = $wire.$el;

    if (!root) {
        return;
    }

    const publishableKey =
        @js(config('services.stripe.key'));

    let stripe = null;
    let elements = null;

    let paymentElement = null;
    let expressCheckoutElement = null;

    let paymentElementReady = false;
    let expressCheckoutReady = false;

    let initializingStripe = false;

    let stripePaymentId =
        @js($paymentIntentId);

    let currentClientSecret =
        @js($clientSecret);

    const initialPaymentStatus =
        @js($paymentStatus);


    /*
    |--------------------------------------------------------------------------
    | DOM helpers
    |--------------------------------------------------------------------------
    */

    const waitForDom = () => {
        return new Promise((resolve) => {
            requestAnimationFrame(() => {
                requestAnimationFrame(resolve);
            });
        });
    };


    const billingNameInput = () => {
        return root.querySelector('#billing-name');
    };


    const billingEmailInput = () => {
        return root.querySelector('#billing-email');
    };


    const stripeLoading = () => {
        return root.querySelector('[data-stripe-loading]');
    };


    const stripePaymentContainer = () => {
        return root.querySelector('[data-stripe-payment-element]');
    };


    const payButton = () => {
        return root.querySelector('[data-stripe-pay]');
    };


    const stripeError = () => {
        return root.querySelector('[data-stripe-error]');
    };


    const stripeStatus = () => {
        return root.querySelector('[data-stripe-status]');
    };


    const walletLoading = () => {
        return root.querySelector('[data-wallet-loading]');
    };


    const walletsUnavailable = () => {
        return root.querySelector(
            '[data-wallets-unavailable]'
        );
    };


    /*
    |--------------------------------------------------------------------------
    | Payment slider
    |--------------------------------------------------------------------------
    */

    const paymentTrack = () => {
        return root.querySelector(
            '[data-payment-track]'
        );
    };


    const paymentTabs = () => {
        return root.querySelectorAll(
            '[data-payment-tab]'
        );
    };


    const setPaymentSlide = (method) => {
        const track = paymentTrack();

        if (!track) {
            return;
        }

        track.classList.toggle(
            'is-wallet',
            method === 'wallet'
        );

        paymentTabs().forEach((tab) => {
            tab.classList.toggle(
                'is-active',
                tab.dataset.paymentTab === method
            );
        });
    };


    paymentTabs().forEach((tab) => {
        tab.addEventListener(
            'click',
            () => {
                setPaymentSlide(
                    tab.dataset.paymentTab
                );
            }
        );
    });


    /*
    |--------------------------------------------------------------------------
    | Stripe UI helpers
    |--------------------------------------------------------------------------
    */

    const showStripeError = (message) => {

        const el = stripeError();

        if (!el) {
            return;
        }

        el.textContent =
            message
            || 'Unable to initialize the payment form.';

        el.hidden = false;
    };


    const clearStripeError = () => {

        const el = stripeError();

        if (!el) {
            return;
        }

        el.textContent = '';
        el.hidden = true;
    };


    const setStripeStatus = (message) => {

        const el = stripeStatus();

        if (el) {
            el.textContent = message || '';
        }
    };


    const setPaymentButtonState = (
        disabled,
        text = null
    ) => {

        const button = payButton();

        if (!button) {
            return;
        }

        button.disabled = disabled;

        if (text !== null) {
            button.textContent = text;
        }
    };


    /*
    |--------------------------------------------------------------------------
    | Destroy Stripe Elements
    |--------------------------------------------------------------------------
    */

    const destroyStripeElement = () => {

        paymentElementReady = false;
        expressCheckoutReady = false;

        if (paymentElement) {

            try {
                paymentElement.unmount();
            } catch (e) {
                // Ignore unmount errors during Livewire DOM transitions.
            }

            paymentElement = null;
        }

        if (expressCheckoutElement) {

            try {
                expressCheckoutElement.unmount();
            } catch (e) {
                // Ignore unmount errors during Livewire DOM transitions.
            }

            expressCheckoutElement = null;
        }

        elements = null;
    };


    /*
    |--------------------------------------------------------------------------
    | Initialize Stripe
    |--------------------------------------------------------------------------
    */

    const initializeStripe = async (
        clientSecret
    ) => {

        if (!clientSecret) {
            return;
        }


        if (!publishableKey) {

            showStripeError(
                'Stripe publishable key is missing.'
            );

            setStripeStatus(
                'Stripe configuration error.'
            );

            return;
        }


        if (
            initializingStripe
            && currentClientSecret === clientSecret
            && paymentElement
        ) {
            return;
        }


        if (
            currentClientSecret === clientSecret
            && paymentElementReady
            && paymentElement
        ) {
            return;
        }


        initializingStripe = true;

        currentClientSecret = clientSecret;

        clearStripeError();

        setPaymentButtonState(
            true,
            'Loading secure payment form...'
        );

        setStripeStatus(
            'Loading secure payment form...'
        );


        try {

            await waitForDom();


            const paymentMount =
                root.querySelector(
                    '#payment-element'
                );

            const expressMount =
                root.querySelector(
                    '#express-checkout-element'
                );

            const paymentContainer =
                stripePaymentContainer();

            const loading =
                stripeLoading();

            const walletLoader =
                walletLoading();


            if (!paymentMount) {
                throw new Error(
                    'Stripe payment container is not available in the DOM.'
                );
            }


            if (!window.Stripe) {
                throw new Error(
                    'Stripe.js failed to load.'
                );
            }


            stripe =
                stripe
                || window.Stripe(
                    publishableKey
                );


            if (!stripe) {
                throw new Error(
                    'Unable to create Stripe instance.'
                );
            }


            destroyStripeElement();


            const billingName =
                billingNameInput()?.value?.trim()
                || '';

            const billingEmail =
                billingEmailInput()?.value?.trim()
                || '';


            /*
            |--------------------------------------------------------------------------
            | Stripe Elements group
            |--------------------------------------------------------------------------
            */

            elements = stripe.elements({
                clientSecret,

                appearance: {
                    theme: 'night',

                    variables: {
                        colorPrimary: '#b56ab9',
                        colorBackground: '#0d0a0f',
                        colorText: '#f5f2f7',
                        colorTextSecondary: '#a69da9',
                        colorDanger: '#e58d9c',
                        borderRadius: '10px',

                        fontFamily:
                            'Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                    },
                },
            });


            /*
            |--------------------------------------------------------------------------
            | Payment Element
            |--------------------------------------------------------------------------
            */

            paymentElement =
                elements.create(
                    'payment',
                    {
                        layout: {
                            type: 'tabs',
                        },

                        defaultValues: {
                            billingDetails: {
                                name: billingName,
                                email: billingEmail,
                            },
                        },

                        fields: {
                            billingDetails: {
                                name: 'always',
                                email: 'auto',
                                address: 'if_required',
                            },
                        },

                        /*
                         * Wallets are handled in Express Checkout
                         * so they don't also appear inside Payment Element.
                         */
                        wallets: {
                            applePay: 'never',
                            googlePay: 'never',
                            link: 'never',
                        },
                    }
                );


            paymentElement.on(
                'loaderstart',
                () => {

                    setStripeStatus(
                        'Loading Stripe payment form...'
                    );
                }
            );


            paymentElement.on(
                'ready',
                () => {

                    paymentElementReady = true;

                    if (loading) {
                        loading.hidden = true;
                    }

                    if (paymentContainer) {
                        paymentContainer.hidden = false;
                    }

                    setPaymentButtonState(
                        false,
                        `Pay ${@js('$' . $formattedTotal)}`
                    );

                    setStripeStatus(
                        'Your payment information is ready.'
                    );
                }
            );


            paymentElement.on(
                'change',
                (event) => {

                    if (event?.error?.message) {

                        showStripeError(
                            event.error.message
                        );

                    } else {

                        clearStripeError();

                    }
                }
            );


            paymentElement.on(
                'loaderror',
                (event) => {

                    const message =
                        event?.error?.message
                        || 'Stripe could not load the payment form.';

                    showStripeError(message);

                    setPaymentButtonState(
                        true,
                        'Payment form unavailable'
                    );

                    setStripeStatus(
                        'Stripe payment form failed to load.'
                    );
                }
            );


            paymentElement.mount(
                paymentMount
            );


            /*
            |--------------------------------------------------------------------------
            | Express Checkout
            |--------------------------------------------------------------------------
            |
            | Express Checkout supports eligible:
            |
            | - Apple Pay
            | - Google Pay
            | - PayPal
            |
            | Stripe decides which are actually available based on
            | account configuration, device, browser, domain, currency,
            | and customer eligibility.
            |--------------------------------------------------------------------------
            */

            if (expressMount) {

                expressCheckoutElement =
                    elements.create(
                        'expressCheckout',
                        {
                            buttonHeight: 50,

                            layout: {
                                maxColumns: 1,
                                maxRows: 3,
                            },

                            buttonType: {
                                applePay: 'buy',
                                googlePay: 'buy',
                                paypal: 'buynow',
                            },

                            buttonTheme: {
                                applePay: 'black',
                                googlePay: 'black',
                                paypal: 'black',
                            },
                        }
                    );


                expressCheckoutElement.on(
                    'ready',
                    ({
                        availablePaymentMethods
                    }) => {

                        if (walletLoader) {
                            walletLoader.hidden = true;
                        }


                        const walletAvailable =
                            availablePaymentMethods
                            && (
                                availablePaymentMethods.applePay
                                || availablePaymentMethods.googlePay
                                || availablePaymentMethods.paypal
                            );


                        expressCheckoutReady =
                            Boolean(
                                walletAvailable
                            );


                        const unavailable =
                            walletsUnavailable();


                        if (unavailable) {
                            unavailable.hidden =
                                expressCheckoutReady;
                        }


                        if (!expressCheckoutReady) {

                            setStripeStatus(
                                'No supported express wallet is currently available.'
                            );
                        }
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | Express Checkout click
                |--------------------------------------------------------------------------
                |
                | This is deliberately handled through the same PaymentIntent
                | flow. The event gives Stripe what it needs to authorize
                | the selected wallet, and confirmPayment finalizes it.
                |--------------------------------------------------------------------------
                */

                expressCheckoutElement.on(
                    'confirm',
                    async (event) => {

                        clearStripeError();

                        setStripeStatus(
                            'Confirming wallet payment securely...'
                        );


                        try {

                            const billingName =
                                billingNameInput()?.value?.trim()
                                || '';

                            const billingEmail =
                                billingEmailInput()?.value?.trim()
                                || '';


                            $wire.billingName =
                                billingName;

                            $wire.billingEmail =
                                billingEmail;


                            /*
                            |--------------------------------------------------------------------------
                            | Keep your existing billing validation logic.
                            |--------------------------------------------------------------------------
                            */

                            await $wire.validateBilling();


                            /*
                            |--------------------------------------------------------------------------
                            | Confirm the PaymentIntent using the same
                            | Elements instance / client secret.
                            |--------------------------------------------------------------------------
                            */

                            const result =
                                await stripe.confirmPayment({
                                    elements,

                                    clientSecret,

                                    confirmParams: {
                                        return_url:
                                            window.location.href,
                                    },

                                    redirect: 'if_required',
                                });


                            if (result?.error) {

                                showStripeError(
                                    result.error.message
                                    || 'Stripe could not confirm this payment.'
                                );

                                setStripeStatus(
                                    'Wallet payment confirmation failed.'
                                );

                                return;
                            }


                            const paymentIntent =
                                result?.paymentIntent;


                            if (!paymentIntent) {

                                throw new Error(
                                    'Stripe did not return a PaymentIntent after wallet confirmation.'
                                );
                            }


                            stripePaymentId =
                                paymentIntent.id;


                            const backendPaymentIntentId =
                                @js($paymentIntentId);


                            if (
                                backendPaymentIntentId
                                && paymentIntent.id
                                    !== backendPaymentIntentId
                            ) {

                                throw new Error(
                                    'The confirmed payment does not match the invoice payment.'
                                );
                            }


                            const expectedAmount =
                                Number(
                                    @js($totalInCents)
                                );


                            if (
                                Number.isFinite(
                                    expectedAmount
                                )
                                && expectedAmount > 0
                                && Number(
                                    paymentIntent.amount
                                ) !== expectedAmount
                            ) {

                                throw new Error(
                                    'The confirmed payment amount does not match the invoice total.'
                                );
                            }


                            if (
                                paymentIntent.currency
                                !== 'usd'
                            ) {

                                throw new Error(
                                    'The confirmed payment currency does not match the invoice.'
                                );
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Payment result
                            |--------------------------------------------------------------------------
                            */

                            if (
                                paymentIntent.status
                                === 'succeeded'
                            ) {

                                setStripeStatus(
                                    'Payment confirmed.'
                                );


                                await $wire.paymentCompleted(
                                    paymentIntent.id
                                );

                                return;
                            }


                            if (
                                paymentIntent.status
                                === 'processing'
                            ) {

                                setStripeStatus(
                                    'Payment is processing.'
                                );


                                await $wire.paymentCompleted(
                                    paymentIntent.id
                                );

                                return;
                            }


                            if (
                                paymentIntent.status
                                === 'requires_action'
                            ) {

                                setStripeStatus(
                                    'Additional authentication is required to complete the payment.'
                                );

                                return;
                            }


                            throw new Error(
                                `Stripe returned payment status: ${paymentIntent.status}`
                            );

                        } catch (error) {

                            console.error(
                                'Express Checkout payment failed:',
                                error
                            );


                            showStripeError(
                                error?.message
                                || 'Unable to complete the wallet payment.'
                            );


                            setStripeStatus(
                                'Wallet payment could not be completed.'
                            );
                        }
                    }
                );


                expressCheckoutElement.mount(
                    expressMount
                );
            }

        } catch (error) {

            console.error(
                'Stripe initialization failed:',
                error
            );


            paymentElementReady = false;
            expressCheckoutReady = false;

            paymentElement = null;
            expressCheckoutElement = null;
            elements = null;


            showStripeError(
                error?.message
                || 'Unable to initialize Stripe payment form.'
            );


            setPaymentButtonState(
                true,
                'Payment form unavailable'
            );


            setStripeStatus(
                'Stripe initialization failed.'
            );

        } finally {

            initializingStripe = false;
        }
    };


    /*
    |--------------------------------------------------------------------------
    | Existing card payment submission
    |--------------------------------------------------------------------------
    */

    const submitPayment = async () => {

        if (
            !stripe
            || !elements
            || !paymentElement
            || !paymentElementReady
        ) {

            showStripeError(
                'The secure payment form is not ready yet.'
            );

            return;
        }


        const button = payButton();

        if (button?.disabled) {
            return;
        }


        clearStripeError();


        setPaymentButtonState(
            true,
            'Processing payment...'
        );


        setStripeStatus(
            'Validating your payment details...'
        );


        try {

            const billingName =
                billingNameInput()?.value?.trim()
                || '';

            const billingEmail =
                billingEmailInput()?.value?.trim()
                || '';


            $wire.billingName =
                billingName;

            $wire.billingEmail =
                billingEmail;


            /*
            |--------------------------------------------------------------------------
            | Existing Livewire validation
            |--------------------------------------------------------------------------
            */

            await $wire.validateBilling();


            /*
            |--------------------------------------------------------------------------
            | Stripe Payment Element submission
            |--------------------------------------------------------------------------
            */

            const submitResult =
                await elements.submit();


            if (submitResult?.error) {

                showStripeError(
                    submitResult.error.message
                    || 'Please correct the payment details and try again.'
                );


                setPaymentButtonState(
                    false,
                    `Pay ${@js('$' . $formattedTotal)}`
                );


                setStripeStatus(
                    'Please review the payment details.'
                );

                return;
            }


            setStripeStatus(
                'Confirming payment securely with Stripe...'
            );


            const result =
                await stripe.confirmPayment({
                    elements,

                    clientSecret,

                    confirmParams: {
                        return_url:
                            window.location.href,
                    },

                    redirect: 'if_required',
                });


            if (result?.error) {

                showStripeError(
                    result.error.message
                    || 'Stripe could not confirm this payment.'
                );


                setPaymentButtonState(
                    false,
                    `Pay ${@js('$' . $formattedTotal)}`
                );


                setStripeStatus(
                    'Payment confirmation failed.'
                );

                return;
            }


            const paymentIntent =
                result?.paymentIntent;


            if (!paymentIntent) {

                throw new Error(
                    'Stripe did not return a PaymentIntent after confirmation.'
                );
            }


            stripePaymentId =
                paymentIntent.id;


            const backendPaymentIntentId =
                @js($paymentIntentId);


            if (
                backendPaymentIntentId
                && paymentIntent.id
                    !== backendPaymentIntentId
            ) {

                throw new Error(
                    'The confirmed payment does not match the invoice payment.'
                );
            }


            const expectedAmount =
                Number(
                    @js($totalInCents)
                );


            if (
                Number.isFinite(expectedAmount)
                && expectedAmount > 0
                && Number(paymentIntent.amount)
                    !== expectedAmount
            ) {

                throw new Error(
                    'The confirmed payment amount does not match the invoice total.'
                );
            }


            if (
                paymentIntent.currency
                !== 'usd'
            ) {

                throw new Error(
                    'The confirmed payment currency does not match the invoice.'
                );
            }


            if (
                paymentIntent.status
                === 'succeeded'
            ) {

                setStripeStatus(
                    'Payment confirmed.'
                );


                await $wire.paymentCompleted(
                    paymentIntent.id
                );


                setPaymentButtonState(
                    true,
                    'Payment complete'
                );


                return;
            }


            if (
                paymentIntent.status
                === 'processing'
            ) {

                setStripeStatus(
                    'Payment is processing.'
                );


                await $wire.paymentCompleted(
                    paymentIntent.id
                );


                setPaymentButtonState(
                    true,
                    'Payment processing'
                );


                return;
            }


            if (
                paymentIntent.status
                === 'requires_action'
            ) {

                setStripeStatus(
                    'Additional authentication is required to complete the payment.'
                );


                setPaymentButtonState(
                    false,
                    `Pay ${@js('$' . $formattedTotal)}`
                );


                return;
            }


            throw new Error(
                `Stripe returned payment status: ${paymentIntent.status}`
            );

        } catch (error) {

            console.error(
                'Payment submission failed:',
                error
            );


            const message =
                error?.message
                || 'Unable to complete the payment.';


            showStripeError(message);


            setPaymentButtonState(
                false,
                `Pay ${@js('$' . $formattedTotal)}`
            );


            setStripeStatus(
                'Payment could not be completed.'
            );
        }
    };


    /*
    |--------------------------------------------------------------------------
    | Card payment button
    |--------------------------------------------------------------------------
    |
    | Event delegation survives Livewire DOM morphs.
    |--------------------------------------------------------------------------
    */

    root.addEventListener(
        'click',
        (event) => {

            const button =
                event.target.closest(
                    '[data-stripe-pay]'
                );


            if (
                !button
                || !root.contains(button)
            ) {
                return;
            }


            event.preventDefault();

            submitPayment();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Signature pad
    |--------------------------------------------------------------------------
    */

    const setupSignaturePad = () => {

        const canvas =
            root.querySelector(
                '#agreement-signature-pad'
            );


        if (
            !canvas
            || canvas.dataset.initialized === '1'
        ) {
            return;
        }


        canvas.dataset.initialized = '1';


        const ctx =
            canvas.getContext('2d');


        if (!ctx) {
            return;
        }


        ctx.lineWidth = 2.1;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        ctx.strokeStyle =
            '#f5f2f7';


        let drawing = false;
        let hasInk = false;


        const pointFromEvent = (event) => {

            const rect =
                canvas.getBoundingClientRect();


            return {

                x:
                    (event.clientX - rect.left)
                    * (canvas.width / rect.width),

                y:
                    (event.clientY - rect.top)
                    * (canvas.height / rect.height),
            };
        };


        const start = (event) => {

            event.preventDefault();

            drawing = true;


            try {

                canvas.setPointerCapture(
                    event.pointerId
                );

            } catch (e) {
                // Ignore unsupported pointer capture.
            }


            const point =
                pointFromEvent(event);


            ctx.beginPath();

            ctx.moveTo(
                point.x,
                point.y
            );

            ctx.lineTo(
                point.x + 0.01,
                point.y + 0.01
            );

            ctx.stroke();


            hasInk = true;
        };


        const move = (event) => {

            if (!drawing) {
                return;
            }


            event.preventDefault();


            const point =
                pointFromEvent(event);


            ctx.lineTo(
                point.x,
                point.y
            );

            ctx.stroke();


            hasInk = true;
        };


        const stop = (event) => {

            if (!drawing) {
                return;
            }


            event.preventDefault();

            drawing = false;


            try {

                canvas.releasePointerCapture(
                    event.pointerId
                );

            } catch (e) {
                // Ignore unsupported pointer capture.
            }
        };


        canvas.addEventListener(
            'pointerdown',
            start,
            { passive: false }
        );


        canvas.addEventListener(
            'pointermove',
            move,
            { passive: false }
        );


        canvas.addEventListener(
            'pointerup',
            stop,
            { passive: false }
        );


        canvas.addEventListener(
            'pointercancel',
            stop,
            { passive: false }
        );


        canvas.addEventListener(
            'pointerleave',
            stop,
            { passive: false }
        );


        const agreement =
            canvas.closest(
                '.cc-checkout__agreement'
            );


        const clearButton =
            agreement?.querySelector(
                '[data-clear-signature]'
            );


        const submitButton =
            agreement?.querySelector(
                '[data-signature-submit]'
            );


        if (clearButton) {

            clearButton.addEventListener(
                'click',
                () => {

                    ctx.clearRect(
                        0,
                        0,
                        canvas.width,
                        canvas.height
                    );

                    hasInk = false;
                }
            );
        }


        if (submitButton) {

            submitButton.addEventListener(
                'click',
                async () => {

                    if (!hasInk) {

                        alert(
                            'Please sign the agreement before continuing.'
                        );

                        return;
                    }


                    submitButton.disabled =
                        true;

                    submitButton.textContent =
                        'Saving signature...';


                    try {

                        const signatureData =
                            canvas.toDataURL(
                                'image/png'
                            );


                        await $wire.saveSignature(
                            signatureData
                        );

                    } catch (error) {

                        console.error(
                            'Signature submission failed:',
                            error
                        );


                        submitButton.disabled =
                            false;

                        submitButton.textContent =
                            'Sign agreement and continue';


                        alert(
                            error?.message
                            || 'The signature could not be saved.'
                        );
                    }
                }
            );
        }
    };


    /*
    |--------------------------------------------------------------------------
    | Watch Livewire clientSecret
    |--------------------------------------------------------------------------
    */

    $wire.$watch(
        'clientSecret',
        async (clientSecret) => {

            if (!clientSecret) {
                return;
            }


            await waitForDom();

            await initializeStripe(
                clientSecret
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Watch paymentIntentId
    |--------------------------------------------------------------------------
    */

    $wire.$watch(
        'paymentIntentId',
        (paymentIntentId) => {

            if (paymentIntentId) {

                stripePaymentId =
                    paymentIntentId;
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------------------
    */

    (async () => {

        await waitForDom();

        setupSignaturePad();


        if (
            currentClientSecret
            && initialPaymentStatus !== 'succeeded'
            && initialPaymentStatus !== 'processing'
        ) {

            await initializeStripe(
                currentClientSecret
            );
        }

    })();

})();
</script>

@endscript