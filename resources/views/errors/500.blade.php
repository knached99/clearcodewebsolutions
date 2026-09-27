<title>Internal Server Error</title>
<style>
    .error500 {
        position: relative;
        min-height: 100svh;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #050409;
        color: white;
        isolation: isolate;
    }

    .error500-bg {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 70% 40%,
                rgba(124, 58, 237, .18),
                transparent 30%),
            radial-gradient(circle at 15% 80%,
                rgba(217, 70, 239, .09),
                transparent 28%);
    }

    .error500-noise {
        position: absolute;
        inset: 0;
        opacity: .08;
        background-image:
            repeating-linear-gradient(0deg,
                transparent,
                transparent 3px,
                rgba(255, 255, 255, .12) 4px);
        animation: error500Noise .15s steps(2) infinite;
    }

    @keyframes error500Noise {
        0% {
            transform: translateY(0);
        }

        100% {
            transform: translateY(4px);
        }
    }

    .error500-content {
        position: relative;
        z-index: 5;
        width: min(1100px, calc(100% - 32px));
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        gap: 4rem;
    }

    .error500-art {
        width: min(500px, 90vw);
        aspect-ratio: 1;
        margin-inline: auto;
    }

    .error500-art svg {
        width: 100%;
        height: 100%;
        overflow: visible;
    }

    .serverOrbit {
        transform-origin: 250px 250px;
        animation: serverSpin 18s linear infinite;
    }

    .serverOrbit2 {
        transform-origin: 250px 250px;
        animation: serverSpinReverse 13s linear infinite;
    }

    @keyframes serverSpin {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes serverSpinReverse {
        to {
            transform: rotate(-360deg);
        }
    }

    .serverCore {
        animation: serverCore 3s ease-in-out infinite;
        transform-origin: center;
    }

    @keyframes serverCore {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.04);
        }
    }

    .serverLight {
        animation: serverLight 1.1s ease-in-out infinite;
    }

    .serverLight:nth-child(2) {
        animation-delay: .15s;
    }

    .serverLight:nth-child(3) {
        animation-delay: .3s;
    }

    @keyframes serverLight {

        0%,
        100% {
            opacity: .25;
        }

        50% {
            opacity: 1;
        }
    }

    .error500-copy {
        order: 2;
    }

    .error500-code {
        font-size: clamp(5rem, 12vw, 9rem);
        line-height: .75;
        font-weight: 900;
        letter-spacing: -.1em;
        background: linear-gradient(135deg,
                #fff,
                #ddd6fe 35%,
                #a78bfa 65%,
                #e879f9);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .error500-eyebrow {
        margin-top: 2rem;
        color: #c4b5fd;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3em;
    }

    .error500-title {
        margin-top: .75rem;
        font-size: clamp(2rem, 4vw, 3.5rem);
        line-height: 1;
        font-weight: 750;
        letter-spacing: -.055em;
    }

    .error500-text {
        max-width: 510px;
        margin-top: 1.25rem;
        color: rgba(255, 255, 255, .42);
        line-height: 1.8;
    }

    .error500-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 2rem;
    }

    .error500-button {
        min-height: 56px;
        padding: 0 23px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, .1);
        text-decoration: none;
        font-size: 13px;
        font-weight: 650;
        transition: .25s ease;
    }

    .error500-button:hover {
        transform: translateY(-3px);
        cursor: pointer;
    }

    .error500-primary {
        color: white;
        background: linear-gradient(135deg, #7c3aed, #9333ea, #c026d3);
        box-shadow: 0 15px 40px rgba(124, 58, 237, .25);
    }

    .error500-secondary {
        color: rgba(255, 255, 255, .7);
        background: rgba(255, 255, 255, .045);
        backdrop-filter: blur(18px);
    }

    @media (max-width: 800px) {
        .error500-content {
            grid-template-columns: 1fr;
            gap: 1rem;
            text-align: center;
        }

        .error500-art {
            width: min(360px, 85vw);
        }

        .error500-text {
            margin-inline: auto;
        }

        .error500-actions {
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .error500-art {
            width: min(290px, 80vw);
        }

        .error500-actions {
            flex-direction: column;
        }

        .error500-button {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .error500 *,
        .error500::before,
        .error500::after {
            animation: none !important;
        }
    }
</style>

<div class="error500">

    <div class="error500-bg"></div>
    <div class="error500-noise"></div>

    <div class="error500-content">

        <div class="error500-art">

            <svg viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">

                <defs>

                    <linearGradient id="serverGradient">
                        <stop stop-color="#c4b5fd" />
                        <stop offset=".5" stop-color="#8b5cf6" />
                        <stop offset="1" stop-color="#e879f9" />
                    </linearGradient>

                    <radialGradient id="serverGlow">
                        <stop stop-color="#8b5cf6" stop-opacity=".3" />
                        <stop offset="1" stop-color="#8b5cf6" stop-opacity="0" />
                    </radialGradient>

                    <filter id="serverBlur">
                        <feGaussianBlur stdDeviation="15" />
                    </filter>

                </defs>

                <circle cx="250" cy="250" r="190" fill="url(#serverGlow)" filter="url(#serverBlur)" />

                <g class="serverOrbit">

                    <circle cx="250" cy="250" r="205" stroke="#a78bfa" stroke-opacity=".12" stroke-width="1"
                        stroke-dasharray="4 15" />

                    <circle cx="455" cy="250" r="5" fill="#c4b5fd" />

                </g>

                <g class="serverOrbit2">

                    <circle cx="250" cy="250" r="170" stroke="#e879f9" stroke-opacity=".14" stroke-width="1"
                        stroke-dasharray="2 12" />

                    <circle cx="420" cy="250" r="4" fill="#e879f9" />

                </g>

                <g class="serverCore">

                    <rect x="120" y="145" width="260" height="210" rx="28" fill="#090711"
                        stroke="url(#serverGradient)" stroke-width="3" />

                    <rect x="145" y="170" width="210" height="160" rx="18" fill="#0e0a18" stroke="#a78bfa"
                        stroke-opacity=".15" />

                    <g class="serverLight">

                        <circle cx="175" cy="205" r="6" fill="#a78bfa" />

                        <circle cx="195" cy="205" r="6" fill="#a78bfa" />

                        <circle cx="215" cy="205" r="6" fill="#a78bfa" />

                    </g>

                    <rect x="175" y="235" width="150" height="8" rx="4" fill="#fff"
                        fill-opacity=".08" />

                    <rect x="175" y="255" width="105" height="8" rx="4" fill="#fff"
                        fill-opacity=".06" />

                    <rect x="175" y="275" width="130" height="8" rx="4" fill="#fff"
                        fill-opacity=".06" />

                    <path d="M228 300H272" stroke="#e879f9" stroke-width="3" stroke-linecap="round" />

                </g>

                <text x="250" y="390" text-anchor="middle" fill="#a78bfa" fill-opacity=".55" font-size="12"
                    font-weight="700" letter-spacing="4">
                    SYSTEM FAILURE
                </text>

            </svg>

        </div>

        <div class="error500-copy">

            <div class="error500-code">
                500
            </div>

            <div class="error500-eyebrow">
                Internal system failure
            </div>

            <h1 class="error500-title">
                Something broke.
            </h1>

            <p class="error500-text">
                Something unexpected happened on our side.
                The system couldn't complete your request.
                Try going back or returning to the homepage.
            </p>

            <div class="error500-actions">

                <button type="button" class="error500-button error500-primary"
                    onclick="
                    const currentUrl = window.location.href;

                    if (window.history.length > 1) {
                        window.history.back();

                        setTimeout(() => {
                            if (window.location.href === currentUrl) {
                                window.location.assign(@js(url('/')));
                            }
                        }, 500);
                    } else {
                        window.location.assign(@js(url('/')));
                    }
                ">
                    ← Go back
                </button>

               <a href="{{ auth()->check()
                ? route('dashboard', ['current_team' => request()->route('current_team')])
                : route('home') }}"
                class="error500-button error500-secondary">
                Return home
            </a>

            </div>

        </div>

    </div>

</div>
