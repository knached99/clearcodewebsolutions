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
                radial-gradient(circle at 15% 15%,
                    rgba(178, 71, 178, 0.14),
                    transparent 32%),
                radial-gradient(circle at 85% 80%,
                    rgba(178, 71, 178, 0.10),
                    transparent 34%),
                #09080b;
        }

        .ticket-search-page::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 10% 20%,
                    rgba(178, 71, 178, 0.10),
                    transparent 28%),
                radial-gradient(circle at 90% 80%,
                    rgba(148, 65, 157, 0.08),
                    transparent 30%);
        }

        .ticket-search-page::after {
            content: "";
            position: absolute;
            width: 520px;
            height: 520px;
            top: -280px;
            right: -180px;
            border-radius: 50%;
            background: rgba(178, 71, 178, 0.06);
            pointer-events: none;
        }

        .ticket-search-inner {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 570px;
            margin: 0 auto;
        }

        /* =========================================================
                               MAIN PANEL
                               ========================================================= */

        .ticket-search-panel {
            padding: 38px;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 28px;
            background:
                linear-gradient(145deg,
                    rgba(28, 24, 31, 0.97),
                    rgba(17, 15, 19, 0.98));
            box-shadow:
                0 35px 80px rgba(0, 0, 0, 0.48),
                0 10px 30px rgba(0, 0, 0, 0.28),
                inset 0 1px 0 rgba(255, 255, 255, 0.035);
            color: #f7f3f8;
        }

        /* =========================================================
                               HEADER
                               ========================================================= */

        .ticket-search-header {
            margin-bottom: 32px;
            text-align: center;
        }

        .ticket-search-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            margin: 0 auto 18px;
            border: 1px solid rgba(178, 71, 178, 0.25);
            border-radius: 17px;
            background:
                linear-gradient(135deg,
                    rgba(178, 71, 178, 0.18),
                    rgba(126, 44, 130, 0.08));
            color: #c56ac5;
            box-shadow:
                0 8px 24px rgba(178, 71, 178, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        .ticket-search-icon svg {
            width: 26px;
            height: 26px;
        }

        .ticket-search-eyebrow {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin-bottom: 8px;
            color: #c56ac5;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .ticket-search-eyebrow::before {
            content: "";
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #b247b2;
            box-shadow:
                0 0 0 3px rgba(178, 71, 178, 0.15),
                0 0 12px rgba(178, 71, 178, 0.35);
        }

        .ticket-search-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: clamp(30px, 5vw, 40px);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -0.045em;
        }

        .ticket-search-header p {
            max-width: 450px;
            margin: 13px auto 0;
            color: #a9a0ac;
            font-size: 14px;
            line-height: 1.7;
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
            margin: 0;
            padding: 0 2px;
            color: #e7e0e9;
            font-size: 13px;
            font-weight: 750;
            letter-spacing: -0.01em;
        }

        .ticket-form-hint {
            color: #77707b;
            font-size: 11px;
            font-weight: 600;
        }

        /* =========================================================
                               INPUTS
                               ========================================================= */

        .ticket-input-wrap {
            position: relative;
        }

        .ticket-input {
            width: 100%;
            height: 60px;
            padding: 0 18px 0 51px;
            border: 1px solid #37313a;
            border-radius: 15px;
            outline: none;
            background: #121015;
            color: #ffffff;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.025);
            transition:
                border-color 180ms ease,
                box-shadow 180ms ease,
                background-color 180ms ease;
        }

        .ticket-input::placeholder {
            color: #6f6872;
            font-weight: 500;
        }

        .ticket-input:hover {
            border-color: #514853;
        }

        .ticket-input:focus {
            border-color: #b247b2;
            background: #151116;
            box-shadow:
                0 0 0 4px rgba(178, 71, 178, 0.11),
                0 8px 24px rgba(178, 71, 178, 0.08);
        }

        .ticket-input-icon {
            position: absolute;
            top: 50%;
            left: 17px;
            width: 20px;
            height: 20px;
            color: #746c77;
            pointer-events: none;
            transform: translateY(-50%);
            transition: color 180ms ease;
        }

        .ticket-input-wrap:focus-within .ticket-input-icon {
            color: #c56ac5;
        }

        .ticket-input-error {
            border-color: #c94d65 !important;
        }

        .ticket-input-error:focus {
            box-shadow:
                0 0 0 4px rgba(201, 77, 101, 0.10) !important;
        }

        .ticket-error {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding-left: 2px;
            color: #e36b83;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.4;
        }

        /* =========================================================
                               BUTTON CONTENT / LOADING
                               ========================================================= */

        .ticket-button-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .ticket-loading-state {
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
            min-height: 58px;
            padding: 0 22px;
            border: 0;
            border-radius: 15px;
            background:
                linear-gradient(135deg,
                    #b247b2 0%,
                    #95419d 100%);
            color: #ffffff;
            font-family: inherit;
            font-size: 14px;
            font-weight: 750;
            letter-spacing: -0.01em;
            cursor: pointer;
            box-shadow:
                0 11px 25px rgba(178, 71, 178, 0.22),
                inset 0 1px 0 rgba(255, 255, 255, 0.16);
            transition:
                transform 180ms ease,
                background 180ms ease,
                box-shadow 180ms ease,
                opacity 180ms ease;
        }

        .ticket-submit-btn:hover {
            transform: translateY(-1px);
            background:
                linear-gradient(135deg,
                    #c052c0 0%,
                    #a548ae 100%);
            box-shadow:
                0 14px 32px rgba(178, 71, 178, 0.28),
                inset 0 1px 0 rgba(255, 255, 255, 0.18);
        }

        .ticket-submit-btn:active {
            transform: translateY(0);
            box-shadow:
                0 7px 17px rgba(178, 71, 178, 0.18);
        }

        .ticket-submit-btn:disabled {
            cursor: wait;
            opacity: 0.68;
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
            margin-bottom: 22px;
            padding: 16px;
            border: 1px solid rgba(178, 71, 178, 0.18);
            border-radius: 17px;
            background:
                linear-gradient(135deg,
                    rgba(178, 71, 178, 0.08),
                    rgba(255, 255, 255, 0.018));
        }

        .ticket-verification-notice-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            flex: 0 0 35px;
            border-radius: 10px;
            background: #19141b;
            color: #c56ac5;
            box-shadow:
                0 4px 12px rgba(0, 0, 0, 0.18);
        }

        .ticket-verification-notice-icon svg {
            width: 17px;
            height: 17px;
        }

        .ticket-verification-notice strong {
            display: block;
            margin-bottom: 3px;
            color: #eee8f0;
            font-size: 13px;
            font-weight: 750;
        }

        .ticket-verification-notice p {
            margin: 0;
            color: #8f8792;
            font-size: 12px;
            line-height: 1.6;
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
            color: #aaa1ad;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
        }

        .ticket-code-input {
            width: 100%;
            height: 72px;
            padding: 0 20px;
            border: 1px solid #39323c;
            border-radius: 16px;
            outline: none;
            background: #121015;
            color: #ffffff;
            font-family: inherit;
            font-size: 28px;
            font-weight: 750;
            letter-spacing: 0.30em;
            text-align: center;
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.025);
            transition:
                border-color 180ms ease,
                box-shadow 180ms ease;
        }

        .ticket-code-input::placeholder {
            color: #4d4650;
            font-weight: 600;
        }

        .ticket-code-input:focus {
            border-color: #b247b2;
            box-shadow:
                0 0 0 4px rgba(178, 71, 178, 0.10),
                0 8px 22px rgba(178, 71, 178, 0.08);
        }

        .ticket-change-email {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 40px;
            border: 0;
            background: transparent;
            color: #b95db9;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: color 160ms ease;
        }

        .ticket-change-email:hover {
            color: #d477d4;
        }

        /* =========================================================
                               TICKET SELECTION
                               ========================================================= */

        .ticket-selection-header {
            margin-bottom: 21px;
        }

        .ticket-selection-header h2 {
            margin: 0 0 7px;
            color: #ffffff;
            font-size: 23px;
            font-weight: 800;
            letter-spacing: -0.035em;
        }

        .ticket-selection-header p {
            margin: 0;
            color: #8f8792;
            font-size: 13px;
            line-height: 1.65;
        }

        .ticket-selection-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .ticket-selection-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            width: 100%;
            padding: 17px 17px 17px 19px;
            border: 1px solid #38313a;
            border-radius: 15px;
            background: #141116;
            color: #eee8f0;
            text-align: left;
            cursor: pointer;
            transition:
                border-color 180ms ease,
                background-color 180ms ease,
                box-shadow 180ms ease;
        }

        .ticket-selection-item:hover {
            border-color: rgba(178, 71, 178, 0.48);
            background: #1a151c;
            box-shadow:
                0 7px 20px rgba(178, 71, 178, 0.08);
        }

        .ticket-selection-item:disabled {
            cursor: wait;
            opacity: 0.65;
        }

        .ticket-selection-copy {
            min-width: 0;
        }

        .ticket-selection-number {
            display: block;
            margin-bottom: 4px;
            color: #c05fc0;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .ticket-selection-subject {
            display: block;
            overflow: hidden;
            color: #e4dce6;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.35;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ticket-selection-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            flex: 0 0 35px;
            border: 1px solid #39323c;
            border-radius: 10px;
            background: #19141b;
            color: #b95db9;
            transition:
                border-color 180ms ease,
                background-color 180ms ease,
                color 180ms ease;
        }

        .ticket-selection-item:hover .ticket-selection-arrow {
            border-color: #b247b2;
            background: #b247b2;
            color: #ffffff;
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
            gap: 12px;
            margin-top: 26px;
            padding-top: 21px;
            border-top: 1px solid #302b32;
        }

        .ticket-security-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            flex: 0 0 35px;
            border: 1px solid rgba(178, 71, 178, 0.16);
            border-radius: 10px;
            background: rgba(178, 71, 178, 0.08);
            color: #c56ac5;
        }

        .ticket-security-icon svg {
            width: 17px;
            height: 17px;
        }

        .ticket-security-copy strong {
            display: block;
            margin-bottom: 3px;
            color: #d8d0da;
            font-size: 12px;
            font-weight: 750;
        }

        .ticket-security-copy p {
            margin: 0;
            color: #77707b;
            font-size: 11px;
            line-height: 1.55;
        }

        /* =========================================================
                               SPINNER
                               ========================================================= */

        .ticket-spinner {
            width: 17px;
            height: 17px;
            border: 2px solid rgba(255, 255, 255, 0.25);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: ticket-spin 700ms linear infinite;
        }

        .ticket-spinner-dark {
            border-color: rgba(178, 71, 178, 0.20);
            border-top-color: #b247b2;
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
                padding: 85px 15px 70px;
                background:
                    radial-gradient(circle at 20% 10%,
                        rgba(178, 71, 178, 0.14),
                        transparent 35%),
                    radial-gradient(circle at 90% 85%,
                        rgba(178, 71, 178, 0.08),
                        transparent 35%),
                    #09080b;
            }

            .ticket-search-panel {
                padding: 27px 19px;
                border-radius: 23px;
            }

            .ticket-search-header {
                margin-bottom: 27px;
            }

            .ticket-search-header h1 {
                font-size: 30px;
            }

            .ticket-search-header p {
                font-size: 13px;
            }

            .ticket-search-form {
                gap: 18px;
            }

            .ticket-input {
                height: 57px;
            }

            .ticket-submit-btn {
                min-height: 56px;
            }

            .ticket-code-input {
                height: 65px;
                font-size: 24px;
                letter-spacing: 0.24em;
            }

            .ticket-selection-subject {
                max-width: 220px;
            }
        }

        .ticket-email-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            flex: 0 0 50px;
            border-radius: 10px;
            background: #19141b;
            color: #c56ac5;
            box-shadow:
                0 4px 12px rgba(0, 0, 0, 0.18);
        }

        .ticket-email-icon svg {
            width: 50px;
            height: 50px;
        }

        /* =========================================================
                               REDUCED MOTION
                               ========================================================= */

        @media (prefers-reduced-motion: reduce) {

            .ticket-input,
            .ticket-submit-btn,
            .ticket-code-input,
            .ticket-change-email,
            .ticket-selection-item,
            .ticket-selection-arrow {
                transition: none;
            }

            .ticket-spinner {
                animation: none;
            }
        }

        .ticket-search-honeypot {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            overflow: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
    </style>
@endpush

<div class="ticket-search-root">

    <x-home.navbar />

    <main>

        <section class="ticket-search-page">

            <div class="ticket-search-inner">

                <div class="ticket-search-panel">

                    {{-- =====================================================
                         HEADER
                         ====================================================== --}}

                    <header class="ticket-search-header">

                        <div class="ticket-search-icon">

                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                aria-hidden="true">

                                <path
                                    d="M4 7.5A2.5 2.5 0 0 1 6.5 5h11A2.5 2.5 0 0 1 20 7.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 16.5v-9Z" />

                                <path d="M8 9h8" />

                                <path d="M8 13h5" />

                            </svg>

                        </div>

                        <div class="ticket-search-eyebrow">
                            My Tickets
                        </div>

                        <h1>
                            Find your support ticket
                        </h1>

                        <p>
                            Enter the email address associated with your
                            support request. We'll send you a secure
                            verification code to access your tickets.
                        </p>

                    </header>

                    {{-- =====================================================
                         EMAIL LOOKUP
                         ====================================================== --}}

                    @if (!$emailVerificationSent && !$showTicketSelection)
                        <div class="ticket-email-option">

                            <div class="ticket-email-heading">

                                <span class="ticket-email-icon">

                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        aria-hidden="true">

                                        <rect x="3" y="5" width="18" height="14" rx="2" />

                                        <path d="m3 7 9 6 9-6" />

                                    </svg>

                                </span>

                                <h3 class="ticket-email-title">
                                    Find your tickets by email
                                </h3>

                            </div>

                            <p class="ticket-email-description">
                                Enter the email address you used when
                                contacting us. We'll send a secure
                                verification code so you can access your
                                support tickets.
                            </p>

                            <form wire:submit.prevent="prepareSendVerificationCode" class="ticket-search-form">

                                <div class="ticket-form-group">
                                    <!-- Honeypot -->
                                    <div class="ticket-search-honeypot" aria-hidden="true">
                                        <label for="ticket-website">
                                            Website
                                        </label>

                                        <input id="ticket-website" type="text" wire:model="website" tabindex="-1"
                                            autocomplete="off">
                                    </div>

                                    <label for="ticket-email" class="ticket-form-label">

                                        <span>
                                            Email address
                                        </span>

                                    </label>

                                    <div class="ticket-input-wrap">

                                        <svg class="ticket-input-icon" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8" aria-hidden="true">

                                            <rect x="3" y="5" width="18" height="14" rx="2" />

                                            <path d="m3 7 9 6 9-6" />

                                        </svg>

                                        <input id="ticket-email" type="email" wire:model="email"
                                            class="ticket-input @error('email') ticket-input-error @enderror"
                                            placeholder="you@example.com" autocomplete="email"
                                            @error('email')
                                                aria-invalid="true"
                                            @enderror />

                                    </div>

                                    @error('email')
                                        <p class="ticket-error">

                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" aria-hidden="true">

                                                <circle cx="12" cy="12" r="9" />

                                                <path d="M12 8v4" />

                                                <path d="M12 16h.01" />

                                            </svg>

                                            {{ $message }}

                                        </p>
                                    @enderror

                                </div>

                                <button type="submit" class="ticket-submit-btn" wire:loading.attr="disabled"
                                    wire:target="prepareSendVerificationCode,sendVerificationCode">

                                    {{-- NORMAL STATE --}}

                                    <span wire:loading.remove
                                        wire:target="prepareSendVerificationCode,sendVerificationCode"
                                        class="ticket-button-content">

                                        <span>
                                            Email me a verification code
                                        </span>

                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            aria-hidden="true">

                                            <path d="M5 12h14" />

                                            <path d="m13 6 6 6-6 6" />

                                        </svg>

                                    </span>

                                    {{-- LOADING STATE --}}

                                    <span wire:loading.flex
                                        wire:target="prepareSendVerificationCode,sendVerificationCode"
                                        class="ticket-loading-state">

                                        <span class="ticket-spinner"></span>

                                        <span>
                                            Sending code...
                                        </span>

                                    </span>

                                </button>

                            </form>

                        </div>
                    @endif

                    {{-- =====================================================
                         EMAIL VERIFICATION
                         ====================================================== --}}

                    @if ($emailVerificationSent && !$showTicketSelection)
                        <div class="ticket-verification-notice">

                            <div class="ticket-verification-notice-icon">

                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    aria-hidden="true">

                                    <rect x="3" y="5" width="18" height="14" rx="2" />

                                    <path d="m3 7 9 6 9-6" />

                                </svg>

                            </div>

                            <div>

                                <strong>
                                    Check your email
                                </strong>

                                <p>
                                    We've sent a 6-digit verification code to
                                    the email address you provided. Enter it
                                    below to continue.
                                </p>

                            </div>

                        </div>

                        <form wire:submit="prepareVerifyEmail" class="ticket-verification-form">

                            <div class="ticket-form-group">

                                <label for="verification-code" class="ticket-code-label">
                                    Enter your verification code
                                </label>

                                <input id="verification-code" type="text" inputmode="numeric" maxlength="6"
                                    autocomplete="one-time-code" wire:model="verificationCode"
                                    class="ticket-code-input @error('verificationCode') ticket-input-error @enderror"
                                    placeholder="000000"
                                    @error('verificationCode')
                                        aria-invalid="true"
                                    @enderror />

                                @error('verificationCode')
                                    <p class="ticket-error" style="justify-content:center;">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            <button type="submit" class="ticket-submit-btn" wire:loading.attr="disabled"
                                wire:target="prepareVerifyEmail,verifyEmail">

                                {{-- NORMAL STATE --}}

                                <span wire:loading.remove wire:target="prepareVerifyEmail,verifyEmail"
                                    class="ticket-button-content">

                                    <span>
                                        Verify and continue
                                    </span>

                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        aria-hidden="true">

                                        <path d="M5 12h14" />

                                        <path d="m13 6 6 6-6 6" />

                                    </svg>

                                </span>

                                {{-- LOADING STATE --}}

                                <span wire:loading.flex wire:target="prepareVerifyEmail,verifyEmail"
                                    class="ticket-loading-state">

                                    <span class="ticket-spinner"></span>

                                    <span>
                                        Verifying code...
                                    </span>

                                </span>

                            </button>

                        </form>

                        <button type="button" class="ticket-change-email" wire:click="changeEmail">
                            Use a different email address
                        </button>
                    @endif

                    {{-- =====================================================
                         TICKET SELECTION
                         ====================================================== --}}

                    @if ($showTicketSelection)

                        <div class="ticket-selection-header">

                            <h2>
                                Select your ticket
                            </h2>

                            <p>
                                We found multiple support tickets associated
                                with this email address. Choose the one you'd
                                like to view.
                            </p>

                        </div>

                        <div class="ticket-selection-list">

                            @foreach (\App\Models\Ticket::query()->whereIn('id', $verifiedTicketIds)->whereRaw('LOWER(email) = ?', [strtolower($email)])->latest('last_activity_at')->get() as $ticket)
                                <button type="button" class="ticket-selection-item"
                                    wire:click="selectTicket({{ $ticket->id }})" wire:loading.attr="disabled"
                                    wire:key="ticket-selection-{{ $ticket->id }}">

                                    <span class="ticket-selection-copy">

                                        <span class="ticket-selection-number">
                                            {{ $ticket->ticket_number }}
                                        </span>

                                        <span class="ticket-selection-subject">
                                            {{ $ticket->subject }}
                                        </span>

                                    </span>

                                    <span class="ticket-selection-arrow">

                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" aria-hidden="true">

                                            <path d="M5 12h14" />

                                            <path d="m13 6 6 6-6 6" />

                                        </svg>

                                    </span>

                                </button>
                            @endforeach

                        </div>

                        <button type="button" class="ticket-change-email" wire:click="changeEmail">
                            Use a different email address
                        </button>

                    @endif

                    {{-- =====================================================
                         SECURITY
                         ====================================================== --}}

                    <div class="ticket-security">

                        <div class="ticket-security-icon">

                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                aria-hidden="true">

                                <path d="M12 3 20 6v5.5c0 4.7-3.2 8-8 9.5-4.8-1.5-8-4.8-8-9.5V6l8-3Z" />

                                <path d="m8.5 12 2.2 2.2 4.8-5" />

                            </svg>

                        </div>

                        <div class="ticket-security-copy">

                            <strong>
                                Your information is secure
                            </strong>

                            <p>
                                Your verification code and private access
                                token are never displayed or stored in your
                                browser.
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
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.google_recaptcha.site_key') }}" async
            defer></script>
    @endonce
    @script
        <script>
            /*
             * ---------------------------------------------------------
             * Send verification-code reCAPTCHA
             * ---------------------------------------------------------
             */
            $wire.on(
                'execute-ticket-send-recaptcha',
                async () => {

                    try {

                        if (
                            typeof grecaptcha === 'undefined'
                        ) {
                            $wire.addError(
                                'form',
                                'Security verification is still loading. Please try again.'
                            );

                            return;
                        }

                        await new Promise((resolve) => {
                            grecaptcha.ready(resolve);
                        });

                        const token =
                            await grecaptcha.execute(
                                '{{ config('services.google_recaptcha.site_key') }}', {
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
                }
            );


            /*
             * ---------------------------------------------------------
             * Verify-email-code reCAPTCHA
             * ---------------------------------------------------------
             */
            $wire.on(
                'execute-ticket-verify-recaptcha',
                async () => {

                    try {

                        if (
                            typeof grecaptcha === 'undefined'
                        ) {
                            $wire.addError(
                                'form',
                                'Security verification is still loading. Please try again.'
                            );

                            return;
                        }

                        await new Promise((resolve) => {
                            grecaptcha.ready(resolve);
                        });

                        const token =
                            await grecaptcha.execute(
                                '{{ config('services.google_recaptcha.site_key') }}', {
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
                }
            );
        </script>
    @endscript

</div>
