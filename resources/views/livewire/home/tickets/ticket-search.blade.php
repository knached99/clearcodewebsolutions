@push('styles')
<style>
    .ticket-search-page {
        min-height: 70vh;
        display: flex;
        justify-content: center;
        padding: 150px 24px 110px;
        box-sizing: border-box;
    }

    .ticket-search-inner {
        width: 100%;
        max-width: 560px;
    }

    .ticket-search-header {
        text-align: center;
        margin-bottom: 42px;
    }

    .ticket-search-icon {
        width: 54px;
        height: 54px;
        margin: 0 auto 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        border: 1px solid rgba(178, 71, 178, .18);
        border-radius: 15px;
        background: rgba(178, 71, 178, .07);
        color: #B247B2;
    }

    .ticket-search-icon svg {
        width: 25px;
        height: 25px;
    }

    .ticket-search-eyebrow {
        display: block;
        margin-bottom: 10px;
        color: #B247B2;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .16em;
        line-height: 1.4;
    }

    .ticket-search-header h1 {
        margin: 0;
        color: #18181b;
        font-size: clamp(30px, 4vw, 42px);
        font-weight: 700;
        letter-spacing: -.04em;
        line-height: 1.08;
    }

    .ticket-search-header p {
        max-width: 480px;
        margin: 15px auto 0;
        color: #71717a;
        font-size: 15px;
        line-height: 1.7;
    }

    .ticket-search-form {
        display: flex;
        flex-direction: column;
        gap: 23px;
        width: 100%;
    }

    .ticket-field {
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .ticket-label-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .ticket-field-label {
        color: #27272a;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.4;
    }

    .ticket-private-label {
        padding: 4px 7px;
        border: 1px solid #e4e4e7;
        border-radius: 6px;
        background: #fafafa;
        color: #71717a;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .12em;
        line-height: 1.2;
    }

    .ticket-input-wrap {
        position: relative;
        width: 100%;
    }

    .ticket-input-icon {
        position: absolute;
        top: 50%;
        left: 16px;
        width: 19px;
        height: 19px;
        color: #a1a1aa;
        transform: translateY(-50%);
        pointer-events: none;
        transition: color .15s ease;
        z-index: 1;
    }

    .ticket-input-wrap:focus-within .ticket-input-icon {
        color: #B247B2;
    }

    .ticket-input-wrap input {
        display: block;
        box-sizing: border-box;
        width: 100%;
        height: 56px;
        margin: 0;
        padding: 0 16px 0 48px;
        border: 1px solid #dedee2;
        border-radius: 12px;
        outline: none;
        background: #fff;
        color: #18181b;
        font-family: inherit;
        font-size: 14px;
        line-height: 1;
        appearance: none;
        -webkit-appearance: none;
        transition:
            border-color .15s ease,
            box-shadow .15s ease,
            background .15s ease;
    }

    .ticket-input-wrap input:hover {
        border-color: #c7c7cc;
    }

    .ticket-input-wrap input:focus {
        border-color: #B247B2;
        box-shadow: 0 0 0 3px rgba(178, 71, 178, .10);
    }

    .ticket-input-wrap input::placeholder {
        color: #a1a1aa;
        opacity: 1;
    }

    /* Validation */

    .ticket-input-wrap input[aria-invalid="true"] {
        border-color: #dc2626;
        background: #fffafa;
    }

    .ticket-input-wrap input[aria-invalid="true"]:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, .10);
    }

    .ticket-input-wrap:has(input[aria-invalid="true"]) .ticket-input-icon {
        color: #dc2626;
    }

    .ticket-field-error {
        margin: 0;
        color: #dc2626;
        font-size: 12px;
        line-height: 1.5;
    }

    /* Main button */

    .ticket-search-button {
        display: flex;
        width: 100%;
        height: 56px;
        align-items: center;
        justify-content: center;
        margin-top: 4px;
        padding: 0 20px;
        border: 0;
        border-radius: 12px;
        background: #18181b;
        color: #fff;
        cursor: pointer;
        font-family: inherit;
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        transition:
            background .15s ease,
            transform .15s ease,
            box-shadow .15s ease,
            opacity .15s ease;
    }

    .ticket-search-button:hover {
        background: #27272a;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, .10);
    }

    .ticket-search-button:focus-visible {
        outline: 3px solid rgba(178, 71, 178, .20);
        outline-offset: 3px;
    }

    .ticket-search-button:disabled {
        cursor: wait;
        opacity: .65;
        transform: none;
        box-shadow: none;
    }

    .ticket-button-content {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
    }

    .ticket-button-content svg {
        width: 17px;
        height: 17px;
        flex: 0 0 auto;
    }

    .ticket-spinner {
        animation: ticket-spin .8s linear infinite;
    }

    @keyframes ticket-spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* -------------------------------------------------------------
       OR divider
       ------------------------------------------------------------- */

    .ticket-or-divider {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 32px 0;
        color: #a1a1aa;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .12em;
    }

    .ticket-or-divider::before,
    .ticket-or-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e4e4e7;
    }

    /* -------------------------------------------------------------
       Email section
       ------------------------------------------------------------- */

    .ticket-email-section {
        width: 100%;
    }

    .ticket-email-description {
        margin: 0 0 18px;
        color: #71717a;
        font-size: 13px;
        line-height: 1.6;
    }

    .ticket-email-button {
        display: flex;
        width: 100%;
        height: 52px;
        align-items: center;
        justify-content: center;
        margin-top: 4px;
        padding: 0 20px;
        border: 1px solid #dedee2;
        border-radius: 12px;
        background: #fff;
        color: #18181b;
        cursor: pointer;
        font-family: inherit;
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        transition:
            border-color .15s ease,
            background .15s ease,
            transform .15s ease,
            box-shadow .15s ease,
            opacity .15s ease;
    }

    .ticket-email-button:hover {
        border-color: #B247B2;
        background: #fdf8fd;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, .06);
    }

    .ticket-email-button:focus-visible {
        outline: 3px solid rgba(178, 71, 178, .20);
        outline-offset: 3px;
    }

    .ticket-email-button:disabled {
        cursor: wait;
        opacity: .65;
        transform: none;
        box-shadow: none;
    }

    /* -------------------------------------------------------------
       Verification notice
       ------------------------------------------------------------- */

    .ticket-verification-notice {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 24px;
        padding: 15px 16px;
        border: 1px solid rgba(178, 71, 178, .16);
        border-radius: 12px;
        background: rgba(178, 71, 178, .05);
    }

    .ticket-verification-notice-icon {
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: rgba(178, 71, 178, .10);
        color: #B247B2;
    }

    .ticket-verification-notice-icon svg {
        width: 16px;
        height: 16px;
    }

    .ticket-verification-notice-content {
        min-width: 0;
    }

    .ticket-verification-notice strong,
    .ticket-verification-notice span {
        display: block;
    }

    .ticket-verification-notice strong {
        color: #3f3f46;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.4;
    }

    .ticket-verification-notice span {
        margin-top: 3px;
        color: #71717a;
        font-size: 11px;
        line-height: 1.5;
    }

    /* -------------------------------------------------------------
       Verification code
       ------------------------------------------------------------- */

    .ticket-code-input {
        letter-spacing: .25em !important;
        font-weight: 600 !important;
        text-align: center;
        padding-left: 48px !important;
    }

    /* -------------------------------------------------------------
       Change email
       ------------------------------------------------------------- */

    .ticket-change-email {
        display: flex;
        justify-content: center;
        margin-top: 18px;
    }

    .ticket-change-email button {
        padding: 0;
        border: 0;
        background: none;
        color: #71717a;
        cursor: pointer;
        font-family: inherit;
        font-size: 12px;
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .ticket-change-email button:hover {
        color: #B247B2;
    }

    /* -------------------------------------------------------------
       Ticket selection
       ------------------------------------------------------------- */

    .ticket-ticket-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .ticket-selection-title {
        margin: 0 0 18px;
        color: #27272a;
        font-size: 20px;
        font-weight: 600;
        line-height: 1.3;
    }

    .ticket-selection-description {
        margin: -8px 0 20px;
        color: #71717a;
        font-size: 13px;
        line-height: 1.6;
    }

    .ticket-selection-item {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-sizing: border-box;
        padding: 16px;
        border: 1px solid #dedee2;
        border-radius: 12px;
        background: #fff;
        cursor: pointer;
        text-align: left;
        font-family: inherit;
        transition:
            border-color .15s ease,
            background .15s ease,
            transform .15s ease,
            box-shadow .15s ease;
    }

    .ticket-selection-item:hover {
        border-color: #B247B2;
        background: #fdf8fd;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, .06);
    }

    .ticket-selection-item:focus-visible {
        outline: 3px solid rgba(178, 71, 178, .20);
        outline-offset: 3px;
    }

    .ticket-selection-item:disabled {
        cursor: wait;
        opacity: .65;
    }

    .ticket-selection-main {
        min-width: 0;
    }

    .ticket-selection-number {
        display: block;
        color: #18181b;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
    }

    .ticket-selection-subject {
        display: block;
        overflow: hidden;
        margin-top: 4px;
        color: #71717a;
        font-size: 12px;
        line-height: 1.5;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ticket-selection-arrow {
        flex: 0 0 auto;
        color: #a1a1aa;
    }

    .ticket-selection-arrow svg {
        width: 18px;
        height: 18px;
    }

    /* -------------------------------------------------------------
       Security notice
       ------------------------------------------------------------- */

    .ticket-security {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-top: 30px;
        padding-top: 22px;
        border-top: 1px solid #e4e4e7;
    }

    .ticket-security-icon {
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #f0fdf4;
        color: #16a34a;
    }

    .ticket-security-icon svg {
        width: 17px;
        height: 17px;
    }

    .ticket-security-content {
        min-width: 0;
    }

    .ticket-security strong,
    .ticket-security span {
        display: block;
    }

    .ticket-security strong {
        color: #3f3f46;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.4;
    }

    .ticket-security span {
        margin-top: 3px;
        color: #a1a1aa;
        font-size: 11px;
        line-height: 1.5;
    }

    /* -------------------------------------------------------------
       Livewire root
       ------------------------------------------------------------- */

    .ticket-search-root {
        width: 100%;
        min-width: 0;
    }

    /* -------------------------------------------------------------
       Mobile
       ------------------------------------------------------------- */

    @media (max-width: 640px) {
        .ticket-search-page {
            min-height: auto;
            padding: 110px 20px 80px;
        }

        .ticket-search-header {
            margin-bottom: 36px;
        }

        .ticket-search-header h1 {
            font-size: 30px;
        }

        .ticket-search-header p {
            font-size: 14px;
        }

        .ticket-search-form {
            gap: 20px;
        }

        .ticket-security {
            margin-top: 26px;
            padding-top: 20px;
        }

        .ticket-selection-item {
            padding: 14px;
        }
    }
</style>
@endpush

<div class="ticket-search-root">

    <x-home.navbar />

    <main>
        <section class="ticket-search-page">
            <div class="ticket-search-inner">

                <header class="ticket-search-header">

                    <div class="ticket-search-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M3 7.5A2.5 2.5 0 0 1 5.5 5h13A2.5 2.5 0 0 1 21 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 16.5v-9Z"/>
                            <path d="M3 8h18"/>
                            <path d="M7 12h.01"/>
                            <path d="M10 12h4"/>
                        </svg>
                    </div>

                    <span class="ticket-search-eyebrow">
                        MY TICKETS
                    </span>

                    <h1>
                        Find your support ticket
                    </h1>

                    <p>
                        Access your support conversation using your
                        ticket number, or verify
                        your email address.
                    </p>

                </header>


                {{-- =====================================================
                     TICKET NUMBER
                     ===================================================== --}}

                @if (! $emailVerificationSent && ! $showTicketSelection)

                    <form
                        wire:submit="search"
                        class="ticket-search-form"
                    >

                        {{-- Ticket Number --}}
                        <div class="ticket-field">

                            <div class="ticket-label-row">
                                <label
                                    for="ticket-number"
                                    class="ticket-field-label"
                                >
                                    Ticket number
                                </label>

                                <span class="ticket-private-label">
                                    PRIVATE
                                </span>
                            </div>

                            <div class="ticket-input-wrap">

                                <svg
                                    class="ticket-input-icon"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-9Z"/>
                                    <path d="M8 9h8"/>
                                    <path d="M8 13h5"/>
                                </svg>

                                <input
                                    id="ticket-number"
                                    type="text"
                                    wire:model="ticketNumber"
                                    placeholder="e.g. TCKT-AB12CD34"
                                    autocomplete="off"
                                    @error('ticketNumber')
                                        aria-invalid="true"
                                    @enderror
                                >

                            </div>

                            @error('ticketNumber')
                                <p class="ticket-field-error">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        {{-- Submit --}}
                        <button
                            type="submit"
                            class="ticket-search-button"
                            wire:loading.attr="disabled"
                            wire:target="search"
                        >

                            <span
                                wire:loading.remove
                                wire:target="search"
                                class="ticket-button-content"
                            >
                                <span>Find my ticket</span>

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M5 12h14"/>
                                    <path d="m13 6 6 6-6 6"/>
                                </svg>
                            </span>

                            <span
                                wire:loading
                                wire:target="search"
                                class="ticket-button-content"
                            >
                                <svg
                                    class="ticket-spinner"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    aria-hidden="true"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="9"
                                        opacity=".3"
                                    />

                                    <path d="M21 12a9 9 0 0 1-9 9"/>
                                </svg>

                                <span>Verifying securely...</span>
                            </span>

                        </button>

                    </form>


                    {{-- OR --}}
                    <div class="ticket-or-divider">
                        OR
                    </div>


                    {{-- EMAIL --}}
                    <div class="ticket-email-section">

                        <p class="ticket-email-description">
                            Don't have your ticket number? Verify your email address instead.
                        </p>

                        <form wire:submit="sendVerificationCode">

                            <div class="ticket-field">

                                <div class="ticket-label-row">
                                    <label
                                        for="ticket-email"
                                        class="ticket-field-label"
                                    >
                                        Email address
                                    </label>
                                </div>

                                <div class="ticket-input-wrap">

                                    <svg
                                        class="ticket-input-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <rect
                                            x="3"
                                            y="5"
                                            width="18"
                                            height="14"
                                            rx="2"
                                        />

                                        <path d="m3 7 9 6 9-6"/>
                                    </svg>

                                    <input
                                        id="ticket-email"
                                        type="email"
                                        wire:model="email"
                                        placeholder="you@example.com"
                                        autocomplete="email"
                                        @error('email')
                                            aria-invalid="true"
                                        @enderror
                                    >

                                </div>

                                @error('email')
                                    <p class="ticket-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            <button
                                type="submit"
                                class="ticket-email-button"
                                wire:loading.attr="disabled"
                                wire:target="sendVerificationCode"
                            >

                                <span
                                    wire:loading.remove
                                    wire:target="sendVerificationCode"
                                    class="ticket-button-content"
                                >
                                    <span>Email me a verification code</span>

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M4 4h16v16H4z"/>
                                        <path d="m4 5 8 7 8-7"/>
                                    </svg>
                                </span>

                                <span
                                    wire:loading
                                    wire:target="sendVerificationCode"
                                    class="ticket-button-content"
                                >
                                    <svg
                                        class="ticket-spinner"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="9"
                                            opacity=".3"
                                        />

                                        <path d="M21 12a9 9 0 0 1-9 9"/>
                                    </svg>

                                    <span>Sending code...</span>
                                </span>

                            </button>

                        </form>

                    </div>

                @endif


                {{-- =====================================================
                     EMAIL VERIFICATION CODE
                     ===================================================== --}}

                @if ($emailVerificationSent && ! $showTicketSelection)

                    <div class="ticket-email-section">

                        <div class="ticket-verification-notice">

                            <div class="ticket-verification-notice-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M4 4h16v16H4z"/>
                                    <path d="m4 5 8 7 8-7"/>
                                </svg>
                            </div>

                            <div class="ticket-verification-notice-content">

                                <strong>
                                    Check your email
                                </strong>

                                <span>
                                    If a ticket is associated with this
                                    email address, we've sent a 6-digit
                                    verification code.
                                </span>

                            </div>

                        </div>


                        <form wire:submit="verifyEmail">

                            <div class="ticket-field">

                                <div class="ticket-label-row">

                                    <label
                                        for="verification-code"
                                        class="ticket-field-label"
                                    >
                                        Verification code
                                    </label>

                                </div>

                                <div class="ticket-input-wrap">

                                    <svg
                                        class="ticket-input-icon"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <rect
                                            x="3"
                                            y="5"
                                            width="18"
                                            height="14"
                                            rx="2"
                                        />

                                        <path d="M7 9h.01"/>
                                        <path d="M10 9h.01"/>
                                        <path d="M13 9h.01"/>
                                        <path d="M16 9h.01"/>

                                        <path d="M7 13h10"/>
                                    </svg>

                                    <input
                                        id="verification-code"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="6"
                                        wire:model="verificationCode"
                                        placeholder="000000"
                                        autocomplete="one-time-code"
                                        class="ticket-code-input"
                                        @error('verificationCode')
                                            aria-invalid="true"
                                        @enderror
                                    >

                                </div>

                                @error('verificationCode')
                                    <p class="ticket-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            <button
                                type="submit"
                                class="ticket-search-button"
                                wire:loading.attr="disabled"
                                wire:target="verifyEmail"
                            >

                                <span
                                    wire:loading.remove
                                    wire:target="verifyEmail"
                                    class="ticket-button-content"
                                >
                                    <span>Verify and continue</span>

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="m5 12 4 4L19 6"/>
                                    </svg>
                                </span>

                                <span
                                    wire:loading
                                    wire:target="verifyEmail"
                                    class="ticket-button-content"
                                >
                                    <svg
                                        class="ticket-spinner"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="9"
                                            opacity=".3"
                                        />

                                        <path d="M21 12a9 9 0 0 1-9 9"/>
                                    </svg>

                                    <span>Verifying securely...</span>
                                </span>

                            </button>

                        </form>


                        <div class="ticket-change-email">

                            <button
                                type="button"
                                wire:click="changeEmail"
                            >
                                Use a different email address
                            </button>

                        </div>

                    </div>

                @endif


                {{-- =====================================================
                     MULTIPLE TICKETS
                     ===================================================== --}}

                @if ($showTicketSelection)

                    <div class="ticket-email-section">

                        <h2 class="ticket-selection-title">
                            Select your ticket
                        </h2>

                        <p class="ticket-selection-description">
                            We found multiple support tickets associated
                            with this email address. Select the ticket
                            you'd like to access.
                        </p>


                        <div class="ticket-ticket-list">

                            @foreach (
                                \App\Models\Ticket::query()
                                    ->whereIn('id', $verifiedTicketIds)
                                    ->whereRaw('LOWER(email) = ?', [strtolower($email)])
                                    ->latest('last_activity_at')
                                    ->get()
                                as $ticket
                            )

                                <button
                                    type="button"
                                    class="ticket-selection-item"
                                    wire:click="selectTicket({{ $ticket->id }})"
                                    wire:loading.attr="disabled"
                                    wire:key="ticket-selection-{{ $ticket->id }}"
                                >

                                    <span class="ticket-selection-main">

                                        <span class="ticket-selection-number">
                                            {{ $ticket->ticket_number }}
                                        </span>

                                        <span class="ticket-selection-subject">
                                            {{ $ticket->subject }}
                                        </span>

                                    </span>

                                    <span class="ticket-selection-arrow">

                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true"
                                        >
                                            <path d="M5 12h14"/>
                                            <path d="m13 6 6 6-6 6"/>
                                        </svg>

                                    </span>

                                </button>

                            @endforeach

                        </div>


                        <div class="ticket-change-email">

                            <button
                                type="button"
                                wire:click="changeEmail"
                            >
                                Use a different email address
                            </button>

                        </div>

                    </div>

                @endif


                {{-- =====================================================
                     SECURITY NOTICE
                     ===================================================== --}}

                <div class="ticket-security">

                    <div class="ticket-security-icon">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M12 3 20 6v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3Z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>

                    </div>

                    <div class="ticket-security-content">

                        <strong>
                            Your information is secure
                        </strong>

                        <span>
                            Your private access key is never displayed
                            or stored in your browser.
                        </span>

                    </div>

                </div>

            </div>
        </section>
    </main>

    <x-home.footer />

    <x-home.to-top />

</div>