<title>Page Not Found</title>
<style>
    body {
        background-color: #000 !important;
    }

    .error404 {
        position: relative;
        min-height: 100svh;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #04040a;
        color: white;
        isolation: isolate;
    }

    .error404-stars {
        position: absolute;
        inset: 0;
        background-image:
            radial-gradient(circle, rgba(255, 255, 255, .7) 1px, transparent 1px),
            radial-gradient(circle, rgba(167, 139, 250, .5) 1px, transparent 1px);
        background-size: 90px 90px, 150px 150px;
        background-position: 0 0, 40px 70px;
        opacity: .18;
        animation: error404Stars 35s linear infinite;
    }

    @keyframes error404Stars {
        to {
            background-position: 0 900px, 40px 970px;
        }
    }

    .error404-content {
        position: relative;
        z-index: 5;
        width: min(1100px, calc(100% - 32px));
        display: grid;
        grid-template-columns: .9fr 1.1fr;
        align-items: center;
        gap: 4rem;
    }

    .error404-art {
        order: 1;
        width: min(500px, 90vw);
        aspect-ratio: 1;
        margin-inline: auto;
    }

    .error404-art svg {
        width: 100%;
        height: 100%;
        overflow: visible;
    }

    .portalOuter {
        transform-origin: 250px 250px;
        animation: portalSpin 24s linear infinite;
    }

    .portalMiddle {
        transform-origin: 250px 250px;
        animation: portalSpinReverse 15s linear infinite;
    }

    .portalInner {
        transform-origin: 250px 250px;
        animation: portalPulse 4s ease-in-out infinite;
    }

    @keyframes portalSpin {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes portalSpinReverse {
        to {
            transform: rotate(-360deg);
        }
    }

    @keyframes portalPulse {

        0%,
        100% {
            transform: scale(.96);
            opacity: .8;
        }

        50% {
            transform: scale(1.04);
            opacity: 1;
        }
    }

    .error404-float {
        animation: error404Float 5s ease-in-out infinite;
    }

    @keyframes error404Float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-12px);
        }
    }

    .error404-copy {
        order: 2;
    }

    .error404-code {
        font-size: clamp(5.5rem, 13vw, 10rem);
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

    .error404-eyebrow {
        margin-top: 2rem;
        color: #c4b5fd;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3em;
    }

    .error404-title {
        margin-top: .75rem;
        font-size: clamp(2rem, 4vw, 3.5rem);
        line-height: 1;
        font-weight: 750;
        letter-spacing: -.055em;
    }

    .error404-text {
        max-width: 500px;
        margin-top: 1.25rem;
        color: rgba(255, 255, 255, .42);
        line-height: 1.8;
    }

    .error404-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 2rem;
    }

    .error404-button {
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

    .error404-button:hover {
        transform: translateY(-3px);
        cursor: pointer;
    }

    .error404-primary {
        color: white;
        background: linear-gradient(135deg, #7c3aed, #9333ea, #c026d3);
        box-shadow: 0 15px 40px rgba(124, 58, 237, .25);
    }

    .error404-secondary {
        color: rgba(255, 255, 255, .7);
        background: rgba(255, 255, 255, .045);
        backdrop-filter: blur(18px);
    }

    @media (max-width: 800px) {
        .error404-content {
            grid-template-columns: 1fr;
            gap: 1rem;
            text-align: center;
        }

        .error404-art {
            order: 1;
            width: min(360px, 85vw);
        }

        .error404-copy {
            order: 2;
        }

        .error404-text {
            margin-inline: auto;
        }

        .error404-actions {
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .error404-art {
            width: min(290px, 80vw);
        }

        .error404-actions {
            flex-direction: column;
        }

        .error404-button {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .error404 *,
        .error404::before,
        .error404::after {
            animation: none !important;
        }
    }
</style>

<div class="error404">

    <div class="error404-stars"></div>

    <div class="error404-content">

        <div class="error404-art">

            <svg viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">

                <defs>

                    <radialGradient id="portalGlow">
                        <stop stop-color="#a78bfa" stop-opacity=".35" />
                        <stop offset="1" stop-color="#7c3aed" stop-opacity="0" />
                    </radialGradient>

                    <linearGradient id="portalGradient">
                        <stop stop-color="#c4b5fd" />
                        <stop offset=".5" stop-color="#8b5cf6" />
                        <stop offset="1" stop-color="#e879f9" />
                    </linearGradient>

                    <filter id="portalBlur">
                        <feGaussianBlur stdDeviation="15" />
                    </filter>

                </defs>

                <circle cx="250" cy="250" r="180" fill="url(#portalGlow)" filter="url(#portalBlur)" />

                <g class="portalOuter">

                    <circle cx="250" cy="250" r="205" stroke="#a78bfa" stroke-opacity=".12" stroke-width="1"
                        stroke-dasharray="3 16" />

                    <circle cx="455" cy="250" r="5" fill="#e879f9" />

                </g>

                <g class="portalMiddle">

                    <circle cx="250" cy="250" r="165" stroke="#c4b5fd" stroke-opacity=".25" stroke-width="1"
                        stroke-dasharray="1 10" />

                    <circle cx="415" cy="250" r="4" fill="#c4b5fd" />

                </g>

                <g class="portalInner">

                    <circle cx="250" cy="250" r="120" fill="#080612" stroke="url(#portalGradient)"
                        stroke-width="3" />

                    <circle cx="250" cy="250" r="96" fill="#0b0715" stroke="#a78bfa" stroke-opacity=".18" />

                    <path d="M205 218
                           C205 190 225 170 250 170
                           C275 170 295 190 295 218" stroke="#c4b5fd" stroke-width="3" stroke-linecap="round" />

                    <path d="M205 282
                           C205 310 225 330 250 330
                           C275 330 295 310 295 282" stroke="#e879f9" stroke-width="3" stroke-linecap="round" />

                    <text x="250" y="267" text-anchor="middle" fill="white" font-size="54" font-weight="800"
                        letter-spacing="-5">
                        ?
                    </text>

                </g>

                <g class="error404-float">

                    <circle cx="125" cy="160" r="4" fill="#c4b5fd" />
                    <circle cx="385" cy="135" r="3" fill="#e879f9" />
                    <circle cx="395" cy="370" r="4" fill="#a78bfa" />
                    <circle cx="115" cy="350" r="3" fill="#a78bfa" />

                </g>

            </svg>

        </div>

        <div class="error404-copy">

            <div class="error404-code">
                404
            </div>

            <div class="error404-eyebrow">
                Coordinates unavailable
            </div>

            <h1 class="error404-title">
                Lost in the void.
            </h1>

            <p class="error404-text">
                This page doesn't exist at this address.
                It may have moved, been deleted, or never existed
                in the first place.
            </p>

            <div class="error404-actions">

                <button type="button" class="error404-button error404-primary"
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
                class="error404-button error404-secondary">
                Return home
            </a>

            </div>

        </div>

    </div>

</div>
