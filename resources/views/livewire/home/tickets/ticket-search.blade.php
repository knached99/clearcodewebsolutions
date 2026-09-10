@push('styles')
<style>
    body {
        background: #000 !important;
    }

    /* =========================================================
       TICKET SEARCH PAGE — DARK MODE
       ========================================================= */

    .ticket-search-page {
        position: relative;
        isolation: isolate;
        min-height: 70vh;
        padding: 135px 24px 105px;
        overflow: hidden;
        background:
            radial-gradient(
                circle at 15% 15%,
                rgba(178, 71, 178, 0.14),
                transparent 32%
            ),
            radial-gradient(
                circle at 85% 80%,
                rgba(178, 71, 178, 0.10),
                transparent 34%
            ),
            #09080b;
    }

    .ticket-search-page::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            linear-gradient(
                90deg,
                rgba(255, 255, 255, 0.018) 1px,
                transparent 1px
            ),
            linear-gradient(
                rgba(255, 255, 255, 0.018) 1px,
                transparent 1px
            );
        background-size: 70px 70px;
        mask-image: linear-gradient(
            to bottom,
            rgba(0, 0, 0, 0.7),
            transparent 85%
        );
    }

    .ticket-search-page::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        pointer-events: none;
        background:
            radial-gradient(
                circle at center,
                transparent 30%,
                rgba(0, 0, 0, 0.45) 100%
            );
    }

    .ticket-search-inner {
        width: 100%;
        max-width: 560px;
        margin: 0 auto;
    }

    .ticket-search-panel {
        position: relative;
        padding: 38px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        background:
            linear-gradient(
                145deg,
                rgba(255, 255, 255, 0.055),
                rgba(255, 255, 255, 0.025)
            );
        box-shadow:
            0 30px 80px rgba(0, 0, 0, 0.45),
            inset 0 1px 0 rgba(255, 255, 255, 0.035);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
    }

    .ticket-search-header {
        margin-bottom: 32px;
        text-align: center;
    }

    .ticket-search-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 58px;
        height: 58px;
        margin: 0 auto 20px;
        border: 1px solid rgba(178, 71, 178, 0.20);
        border-radius: 16px;
        background: rgba(178, 71, 178, 0.08);
        color: #c56ac5;
        box-shadow:
            0 12px 30px rgba(178, 71, 178, 0.08),
            inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }

    .ticket-search-icon svg {
        width: 27px;
        height: 27px;
    }

    .ticket-search-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 11px;
        color: #c56ac5;
        font-size: 11px;
        font-weight: 750;
        line-height: 1;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .ticket-search-eyebrow::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #c56ac5;
        box-shadow: 0 0 0 4px rgba(197, 106, 197, 0.08);
    }

    .ticket-search-header h1 {
        margin: 0;
        color: #f5eef6;
        font-size: clamp(27px, 5vw, 34px);
        font-weight: 800;
        line-height: 1.12;
        letter-spacing: -0.035em;
    }

    .ticket-search-header p {
        max-width: 460px;
        margin: 14px auto 0;
        color: rgba(255, 255, 255, 0.50);
        font-size: 14px;
        font-weight: 400;
        line-height: 1.65;
    }

    /* =========================================================
       EMAIL LOOKUP
       ========================================================= */

    .ticket-email-option {
        margin-top: 2px;
    }

    .ticket-email-heading {
        margin-bottom: 13px;
    }

    .ticket-email-title {
        margin: 0;
        color: #eee8f0;
        font-size: 15px;
        font-weight: 750;
        line-height: 1.4;
        letter-spacing: -0.015em;
    }

    /* =========================================================
       FORMS
       ========================================================= */

    .ticket-search-form {
        display: flex;
        flex-direction: column;
        gap: 21px;
    }

    .ticket-form-group {
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .ticket-form-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: #e8e0e9;
        font-size: 13px;
        font-weight: 650;
        line-height: 1.4;
    }

    .ticket-form-hint {
        color: rgba(255, 255, 255, 0.32);
        font-size: 11px;
        font-weight: 450;
    }

    /* =========================================================
       EMAIL INPUT
       ========================================================= */

    .ticket-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        height: 60px;
    }

    .ticket-input {
        display: block;
        width: 100%;
        height: 60px;
        margin: 0;
        padding: 0 18px 0 58px;
        border: 1px solid rgba(255, 255, 255, 0.10);
        border-radius: 12px;
        outline: none;
        background: rgba(255, 255, 255, 0.045);
        color: #f4eef5;
        font-family: inherit;
        font-size: 15px;
        font-weight: 500;
        line-height: normal;
        box-sizing: border-box;
        transition:
            border-color 180ms ease,
            background 180ms ease,
            box-shadow 180ms ease;
    }

    .ticket-input::placeholder {
        color: rgba(255, 255, 255, 0.38);
        opacity: 1;
    }

    .ticket-input:hover {
        border-color: rgba(255, 255, 255, 0.15);
        background: rgba(255, 255, 255, 0.055);
    }

    .ticket-input:focus {
        border-color: rgba(197, 106, 197, 0.55);
        background: rgba(255, 255, 255, 0.06);
        box-shadow:
            0 0 0 3px rgba(197, 106, 197, 0.10),
            0 10px 30px rgba(0, 0, 0, 0.18);
    }

    /*
     * The icon is positioned against the exact center of the
     * input's content area. It does not use the input line-height
     * for positioning, which prevents it from dropping below
     * or sitting above the placeholder text.
     */
    .ticket-input-icon {
        position: absolute;
        top: 50%;
        left: 18px;
        z-index: 2;
        display: block;
        width: 23px;
        height: 23px;
        margin: 0;
        padding: 0;
        color: #8d8290;
        pointer-events: none;
        transform: translateY(-50%);
        transform-origin: center center;
        transition: color 180ms ease;
    }

    .ticket-input-wrap:focus-within .ticket-input-icon {
        color: #c56ac5;
    }

    .ticket-input-error {
        border-color: rgba(224, 92, 111, 0.55);
    }

    .ticket-input-error:focus {
        border-color: rgba(224, 92, 111, 0.70);
        box-shadow:
            0 0 0 3px rgba(224, 92, 111, 0.10),
            0 10px 30px rgba(0, 0, 0, 0.18);
    }

    .ticket-error {
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 0;
        color: #e8909d;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.45;
    }

    .ticket-error svg {
        flex: 0 0 auto;
    }

    /* =========================================================
       BUTTON CONTENT / LOADING
       ========================================================= */

    .ticket-button-content {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
    }

    .ticket-loading-state {
        display: none;
        align-items: center;
        justify-content: center;
        gap: 9px;
    }

    .ticket-loading-state[wire\:loading] {
        display: none;
    }

    .ticket-loading-state[wire\:loading].flex {
        display: flex;
    }

    /* =========================================================
       PRIMARY BUTTON
       ========================================================= */

    .ticket-submit-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 54px;
        padding: 0 20px;
        border: 1px solid rgba(255, 255, 255, 0.10);
        border-radius: 11px;
        outline: none;
        background: linear-gradient(
            135deg,
            #b247b2,
            #8f3e91
        );
        color: #ffffff;
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        box-shadow:
            0 12px 28px rgba(178, 71, 178, 0.20),
            inset 0 1px 0 rgba(255, 255, 255, 0.12);
        transition:
            transform 180ms ease,
            box-shadow 180ms ease,
            filter 180ms ease;
    }

    .ticket-submit-btn:hover {
        filter: brightness(1.08);
        transform: translateY(-1px);
        box-shadow:
            0 16px 34px rgba(178, 71, 178, 0.27),
            inset 0 1px 0 rgba(255, 255, 255, 0.14);
    }

    .ticket-submit-btn:active {
        transform: translateY(0);
    }

    .ticket-submit-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
        filter: none;
    }

    .ticket-submit-btn svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
    }

    /* =========================================================
       EMAIL VERIFICATION
       ========================================================= */

    .ticket-verification-notice {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        margin-bottom: 24px;
        padding: 15px 16px;
        border: 1px solid rgba(197, 106, 197, 0.16);
        border-radius: 12px;
        background: rgba(197, 106, 197, 0.055);
    }

    .ticket-verification-notice-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        border-radius: 9px;
        background: rgba(197, 106, 197, 0.10);
        color: #c56ac5;
    }

    .ticket-verification-notice-icon svg {
        width: 18px;
        height: 18px;
    }

    .ticket-verification-notice strong {
        display: block;
        margin-bottom: 3px;
        color: #eee6ef;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
    }

    .ticket-verification-notice p {
        margin: 0;
        color: rgba(255, 255, 255, 0.45);
        font-size: 12px;
        font-weight: 400;
        line-height: 1.55;
    }

    /* =========================================================
       VERIFICATION FORM
       ========================================================= */

    .ticket-verification-form {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .ticket-code-label {
        display: block;
        margin-bottom: 9px;
        color: #e8e0e9;
        font-size: 13px;
        font-weight: 650;
    }

    .ticket-code-input {
        display: block;
        width: 100%;
        height: 60px;
        padding: 0 18px;
        border: 1px solid rgba(255, 255, 255, 0.10);
        border-radius: 12px;
        outline: none;
        background: rgba(255, 255, 255, 0.045);
        color: #f4eef5;
        font-family: inherit;
        font-size: 22px;
        font-weight: 700;
        line-height: normal;
        letter-spacing: 0.22em;
        text-align: center;
        box-sizing: border-box;
        transition:
            border-color 180ms ease,
            background 180ms ease,
            box-shadow 180ms ease;
    }

    .ticket-code-input::placeholder {
        color: rgba(255, 255, 255, 0.24);
        letter-spacing: 0.18em;
    }

    .ticket-code-input:focus {
        border-color: rgba(197, 106, 197, 0.55);
        background: rgba(255, 255, 255, 0.06);
        box-shadow:
            0 0 0 3px rgba(197, 106, 197, 0.10),
            0 10px 30px rgba(0, 0, 0, 0.18);
    }

    .ticket-change-email {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        margin-top: 2px;
        padding: 0;
        border: 0;
        outline: none;
        background: transparent;
        color: rgba(255, 255, 255, 0.42);
        font-family: inherit;
        font-size: 12px;
        font-weight: 550;
        cursor: pointer;
        transition: color 180ms ease;
    }

    .ticket-change-email:hover {
        color: #c56ac5;
    }

    /* =========================================================
       TICKET SELECTION
       ========================================================= */

    .ticket-selection-header {
        margin-bottom: 20px;
    }

    .ticket-selection-header h2 {
        margin: 0;
        color: #f0e9f1;
        font-size: 20px;
        font-weight: 750;
        line-height: 1.25;
        letter-spacing: -0.02em;
    }

    .ticket-selection-header p {
        margin: 8px 0 0;
        color: rgba(255, 255, 255, 0.45);
        font-size: 12px;
        line-height: 1.55;
    }

    .ticket-selection-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .ticket-selection-item {
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 68px;
        padding: 13px 15px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        outline: none;
        background: rgba(255, 255, 255, 0.035);
        color: inherit;
        text-align: left;
        cursor: pointer;
        transition:
            border-color 180ms ease,
            background 180ms ease,
            transform 180ms ease;
    }

    .ticket-selection-item:hover {
        border-color: rgba(197, 106, 197, 0.30);
        background: rgba(197, 106, 197, 0.055);
        transform: translateY(-1px);
    }

    .ticket-selection-item:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
    }

    .ticket-selection-copy {
        min-width: 0;
        flex: 1;
    }

    .ticket-selection-number {
        margin-bottom: 3px;
        color: rgba(255, 255, 255, 0.34);
        font-size: 10px;
        font-weight: 650;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .ticket-selection-subject {
        overflow: hidden;
        color: #eee7ef;
        font-size: 13px;
        font-weight: 650;
        line-height: 1.4;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ticket-selection-arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        margin-left: 12px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.045);
        color: rgba(255, 255, 255, 0.42);
        transition:
            background 180ms ease,
            color 180ms ease,
            transform 180ms ease;
    }

    .ticket-selection-item:hover .ticket-selection-arrow {
        background: rgba(197, 106, 197, 0.10);
        color: #c56ac5;
        transform: translateX(2px);
    }

    .ticket-selection-arrow svg {
        width: 16px;
        height: 16px;
    }

    /* =========================================================
       SECURITY NOTICE
       ========================================================= */

    .ticket-security {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin-top: 27px;
        padding-top: 22px;
        border-top: 1px solid rgba(255, 255, 255, 0.065);
    }

    .ticket-security-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 31px;
        height: 31px;
        flex: 0 0 31px;
        border-radius: 9px;
        background: rgba(255, 255, 255, 0.045);
        color: rgba(255, 255, 255, 0.38);
    }

    .ticket-security-icon svg {
        width: 16px;
        height: 16px;
    }

    .ticket-security-copy {
        min-width: 0;
        flex: 1;
    }

    .ticket-security-copy strong {
        display: block;
        margin-bottom: 3px;
        color: rgba(255, 255, 255, 0.58);
        font-size: 11px;
        font-weight: 650;
        line-height: 1.4;
    }

    .ticket-security-copy p {
        margin: 0;
        color: rgba(255, 255, 255, 0.30);
        font-size: 10px;
        font-weight: 400;
        line-height: 1.55;
    }

    /* =========================================================
       SPINNER
       ========================================================= */

    .ticket-spinner {
        width: 17px;
        height: 17px;
        flex: 0 0 17px;
        border: 2px solid rgba(255, 255, 255, 0.28);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: ticket-spin 700ms linear infinite;
    }

    .ticket-spinner-dark {
        border-color: rgba(255, 255, 255, 0.12);
        border-top-color: rgba(255, 255, 255, 0.60);
    }

    @keyframes ticket-spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* =========================================================
       MOBILE
       ========================================================= */

    @media (max-width: 640px) {
        .ticket-search-page {
            min-height: 70vh;
            padding: 105px 16px 75px;
        }

        .ticket-search-panel {
            padding: 28px 20px;
            border-radius: 17px;
        }

        .ticket-search-header {
            margin-bottom: 28px;
        }

        .ticket-search-icon {
            width: 52px;
            height: 52px;
            margin-bottom: 17px;
            border-radius: 14px;
        }

        .ticket-search-icon svg {
            width: 24px;
            height: 24px;
        }

        .ticket-search-header h1 {
            font-size: 28px;
        }

        .ticket-search-header p {
            margin-top: 12px;
            font-size: 13px;
        }

        .ticket-input-wrap {
            height: 58px;
        }

        .ticket-input {
            height: 58px;
            padding-left: 57px;
        }

        .ticket-input-icon {
            left: 17px;
            width: 22px;
            height: 22px;
        }

        .ticket-code-input {
            height: 58px;
        }
    }

    /* =========================================================
       REDUCED MOTION
       ========================================================= */

    @media (prefers-reduced-motion: reduce) {
        .ticket-input,
        .ticket-input-icon,
        .ticket-submit-btn,
        .ticket-selection-item,
        .ticket-selection-arrow,
        .ticket-change-email {
            transition: none;
        }

        .ticket-spinner {
            animation: none;
        }
    }

    /* =========================================================
       HONEYPOT
       ========================================================= */

    .ticket-search-honeypot {
        position: absolute !important;
        left: -9999px !important;
        width: 1px !important;
        height: 1px !important;
        overflow: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    /* =========================================================
       RECAPTCHA
       ========================================================= */

    .grecaptcha-badge {
        visibility: hidden !important;
    }

    .ticket-search-recaptcha {
        display: block;
        margin: 8px 0 0;
        color: rgba(255, 255, 255, 0.28);
        font-size: 9px;
        font-weight: 400;
        line-height: 1.5;
        text-align: left;
    }

    .ticket-search-recaptcha a {
        color: rgba(255, 255, 255, 0.38);
        text-decoration: underline;
        text-decoration-color: rgba(255, 255, 255, 0.18);
        text-underline-offset: 2px;
    }

    .ticket-search-recaptcha a:hover {
        color: rgba(255, 255, 255, 0.58);
    }
</style>
@endpush

<div class="ticket-search-root">
    <x-home.navbar />

    <main>
        <section class="ticket-search-page">
            <div class="ticket-search-inner">
                <div class="ticket-search-panel">

                    <header class="ticket-search-header">
                        <div class="ticket-search-icon" aria-hidden="true">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v13a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 18.5v-13Z"
                                />
                                <path d="M8 8h8" />
                                <path d="M8 12h8" />
                                <path d="M8 16h5" />
                            </svg>
                        </div>

                        <div class="ticket-search-eyebrow">
                            Support tickets
                        </div>

                        <h1>
                            Find your support tickets
                        </h1>

                        <p>
                            Enter the email address you used when contacting us.
                            We'll send a secure verification code so you can access
                            your support tickets.
                        </p>
                    </header>

                    @if (!$emailVerificationSent && !$showTicketSelection)
                        <div class="ticket-email-option">
                            <div class="ticket-email-heading">
                                <h3 class="ticket-email-title">
                                    Find your tickets by email
                                </h3>
                            </div>

                            <form
                                wire:submit.prevent="prepareSendVerificationCode"
                                class="ticket-search-form"
                            >
                                <div class="ticket-form-group">
                                    @honeypot

                                    <label
                                        for="ticket-email"
                                        class="ticket-form-label"
                                    >
                                        <span>Email address</span>
                                    </label>

                                    <div class="ticket-input-wrap">
                                        <svg
                                            class="ticket-input-icon"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            aria-hidden="true"
                                        >
                                            <rect
                                                x="3"
                                                y="5"
                                                width="18"
                                                height="14"
                                                rx="2"
                                            />
                                            <path d="m3 7 9 6 9-6" />
                                        </svg>

                                        <input
                                            id="ticket-email"
                                            type="email"
                                            wire:model="email"
                                            class="ticket-input @error('email') ticket-input-error @enderror"
                                            placeholder="you@example.com"
                                            autocomplete="email"
                                            @error('email')
                                                aria-invalid="true"
                                            @enderror
                                        />
                                    </div>

                                    @error('email')
                                        <p class="ticket-error">
                                            <svg
                                                width="14"
                                                height="14"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                aria-hidden="true"
                                            >
                                                <circle cx="12" cy="12" r="9" />
                                                <path d="M12 8v4" />
                                                <path d="M12 16h.01" />
                                            </svg>

                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                @error('form')
                                    <p class="ticket-error">
                                        <svg
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            aria-hidden="true"
                                        >
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="M12 8v4" />
                                            <path d="M12 16h.01" />
                                        </svg>

                                        {{ $message }}
                                    </p>
                                @enderror

                                <button
                                    type="submit"
                                    class="ticket-submit-btn"
                                    wire:loading.attr="disabled"
                                    wire:target="prepareSendVerificationCode"
                                >
                                    <span
                                        class="ticket-button-content"
                                        wire:loading.remove
                                        wire:target="prepareSendVerificationCode"
                                    >
                                        <span>
                                            Send verification code
                                        </span>

                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            aria-hidden="true"
                                        >
                                            <path d="M5 12h13" />
                                            <path d="m13 6 6 6-6 6" />
                                        </svg>
                                    </span>

                                    <span
                                        class="ticket-loading-state"
                                        wire:loading.flex
                                        wire:target="prepareSendVerificationCode"
                                    >
                                        <span class="ticket-spinner"></span>
                                        <span>Sending code...</span>
                                    </span>
                                </button>
                            </form>
                        </div>
                    @endif

                    @if ($emailVerificationSent && !$showTicketSelection)
                        <div class="ticket-verification-notice">
                            <div class="ticket-verification-notice-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <rect
                                        x="3"
                                        y="5"
                                        width="18"
                                        height="14"
                                        rx="2"
                                    />
                                    <path d="m3 7 9 6 9-6" />
                                </svg>
                            </div>

                            <div>
                                <strong>
                                    Check your email
                                </strong>

                                <p>
                                    We've sent a verification code to
                                    {{ $email }}.
                                    Enter the code below to continue.
                                </p>
                            </div>
                        </div>

                        <form
                            wire:submit.prevent="prepareVerifyEmail"
                            class="ticket-verification-form"
                        >
                            <div>
                                <label
                                    for="ticket-code"
                                    class="ticket-code-label"
                                >
                                    Verification code
                                </label>

                                <input
                                    id="ticket-code"
                                    type="text"
                                    wire:model="verificationCode"
                                    class="ticket-code-input @error('verificationCode') ticket-input-error @enderror"
                                    placeholder="000000"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    maxlength="6"
                                    @error('verificationCode')
                                        aria-invalid="true"
                                    @enderror
                                />

                                @error('verificationCode')
                                    <p
                                        class="ticket-error"
                                        style="margin-top: 9px;"
                                    >
                                        <svg
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            aria-hidden="true"
                                        >
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="M12 8v4" />
                                            <path d="M12 16h.01" />
                                        </svg>

                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            @error('form')
                                <p class="ticket-error">
                                    <svg
                                        width="14"
                                        height="14"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        aria-hidden="true"
                                    >
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 8v4" />
                                        <path d="M12 16h.01" />
                                    </svg>

                                    {{ $message }}
                                </p>
                            @enderror

                            <button
                                type="submit"
                                class="ticket-submit-btn"
                                wire:loading.attr="disabled"
                                wire:target="prepareVerifyEmail"
                            >
                                <span
                                    class="ticket-button-content"
                                    wire:loading.remove
                                    wire:target="prepareVerifyEmail"
                                >
                                    <span>
                                        Verify email
                                    </span>

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        aria-hidden="true"
                                    >
                                        <path d="M5 12h13" />
                                        <path d="m13 6 6 6-6 6" />
                                    </svg>
                                </span>

                                <span
                                    class="ticket-loading-state"
                                    wire:loading.flex
                                    wire:target="prepareVerifyEmail"
                                >
                                    <span class="ticket-spinner"></span>
                                    <span>Verifying...</span>
                                </span>
                            </button>

                            <button
                                type="button"
                                class="ticket-change-email"
                                wire:click="resetEmailVerification"
                                wire:loading.attr="disabled"
                            >
                                <svg
                                    width="14"
                                    height="14"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path d="M20 12a8 8 0 0 1-14.9 4" />
                                    <path d="M4 12a8 8 0 0 1 14.9-4" />
                                    <path d="M5 16v-4H9" />
                                    <path d="M19 8v4h-4" />
                                </svg>

                                Use a different email address
                            </button>
                        </form>
                    @endif

                    @if ($showTicketSelection)
                        <div class="ticket-selection-header">
                            <h2>
                                Select a ticket
                            </h2>

                            <p>
                                We found multiple tickets associated with
                                {{ $email }}. Select one to continue.
                            </p>
                        </div>

                        <div class="ticket-selection-list">
                            @foreach ($tickets as $ticket)
                                <button
                                    type="button"
                                    class="ticket-selection-item"
                                    wire:click="selectTicket({{ $ticket->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="selectTicket({{ $ticket->id }})"
                                >
                                    <div class="ticket-selection-copy">
                                        <div class="ticket-selection-number">
                                            Ticket #{{ $ticket->id }}
                                        </div>

                                        <div class="ticket-selection-subject">
                                            {{ $ticket->subject }}
                                        </div>
                                    </div>

                                    <span class="ticket-selection-arrow">
                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            aria-hidden="true"
                                        >
                                            <path d="M5 12h13" />
                                            <path d="m13 6 6 6-6 6" />
                                        </svg>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="ticket-security">
                        <div class="ticket-security-icon">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path
                                    d="M12 3 5 6v5c0 4.7 2.9 8.5 7 10 4.1-1.5 7-5.3 7-10V6l-7-3Z"
                                />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                        </div>

                        <div class="ticket-security-copy">
                            <strong>
                                Your information is secure
                            </strong>

                            <p>
                                We use email verification to make sure only
                                you can access your support tickets.
                            </p>

                            <p class="ticket-search-recaptcha">
                                This site is protected by reCAPTCHA and the Google
                                <a href="https://policies.google.com/privacy">
                                    Privacy Policy
                                </a>
                                and
                                <a href="https://policies.google.com/terms">
                                    Terms of Service
                                </a>
                                apply.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <x-home.footer />
    <x-home.to-top />

    @once
        <script
            src="https://www.google.com/recaptcha/api.js?render={{ config('services.google_recaptcha.site_key') }}"
            async
            defer
        ></script>
    @endonce

    @script
    <script>
        $wire.on('execute-ticket-send-recaptcha', async () => {
            try {
                if (typeof grecaptcha === 'undefined') {
                    $wire.addError(
                        'form',
                        'Security verification is still loading. Please try again.'
                    );

                    return;
                }

                await new Promise((resolve) => {
                    grecaptcha.ready(resolve);
                });

                const token = await grecaptcha.execute(
                    '{{ config('services.google_recaptcha.site_key') }}',
                    {
                        action: 'ticket_verification_send'
                    }
                );

                await $wire.call(
                    'sendVerificationCode',
                    token
                );
            } catch (error) {
                console.error(
                    'Ticket send reCAPTCHA error:',
                    error
                );

                $wire.addError(
                    'form',
                    'Security verification failed. Please try again.'
                );
            }
        });

        $wire.on('execute-ticket-verify-recaptcha', async () => {
            try {
                if (typeof grecaptcha === 'undefined') {
                    $wire.addError(
                        'form',
                        'Security verification is still loading. Please try again.'
                    );

                    return;
                }

                await new Promise((resolve) => {
                    grecaptcha.ready(resolve);
                });

                const token = await grecaptcha.execute(
                    '{{ config('services.google_recaptcha.site_key') }}',
                    {
                        action: 'ticket_verification_verify'
                    }
                );

                await $wire.call(
                    'verifyEmail',
                    token
                );
            } catch (error) {
                console.error(
                    'Ticket verification reCAPTCHA error:',
                    error
                );

                $wire.addError(
                    'form',
                    'Security verification failed. Please try again.'
                );
            }
        });
    </script>
    @endscript
</div>