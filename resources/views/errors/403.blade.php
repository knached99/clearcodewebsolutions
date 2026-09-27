<title>Forbidden</title>
<style>
    body {
        background-color: #000 !important;
    }

    .error403 {
        position: relative;
        min-height: 100svh;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #05040a;
        color: #fff;
        isolation: isolate;
    }

    .error403-bg {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 50% 45%,
                rgba(124, 58, 237, .18),
                transparent 25%),
            radial-gradient(circle at 15% 85%,
                rgba(168, 85, 247, .10),
                transparent 30%),
            radial-gradient(circle at 90% 10%,
                rgba(217, 70, 239, .08),
                transparent 25%);
        animation: error403Atmosphere 10s ease-in-out infinite alternate;
    }

    @keyframes error403Atmosphere {
        from {
            transform: scale(1);
            opacity: .8;
        }

        to {
            transform: scale(1.08);
            opacity: 1;
        }
    }

    .error403-grid {
        position: absolute;
        inset: -50%;
        background-image:
            linear-gradient(rgba(255, 255, 255, .035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, .035) 1px, transparent 1px);
        background-size: 70px 70px;
        transform: perspective(700px) rotateX(62deg) translateY(20%);
        mask-image: linear-gradient(to bottom,
                transparent,
                black 25%,
                black 70%,
                transparent);
        opacity: .45;
    }

    .error403-content {
        position: relative;
        z-index: 5;
        width: min(1100px, calc(100% - 32px));
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        gap: 4rem;
    }

    .error403-copy {
        text-align: left;
    }

    .error403-label {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 13px;
        border: 1px solid rgba(167, 139, 250, .18);
        border-radius: 999px;
        background: rgba(124, 58, 237, .08);
        color: #c4b5fd;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .22em;
        text-transform: uppercase;
    }

    .error403-label-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #a78bfa;
        box-shadow: 0 0 15px #a78bfa;
        animation: error403Dot 1.5s ease-in-out infinite;
    }

    @keyframes error403Dot {
        50% {
            opacity: .3;
            transform: scale(.65);
        }
    }

    .error403-code {
        margin-top: 1.5rem;
        font-size: clamp(5rem, 11vw, 9rem);
        line-height: .8;
        font-weight: 900;
        letter-spacing: -.1em;
        background: linear-gradient(135deg,
                #ffffff 10%,
                #ddd6fe 40%,
                #a78bfa 70%,
                #e879f9);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .error403-title {
        margin-top: 1.7rem;
        font-size: clamp(2rem, 4vw, 3.5rem);
        line-height: 1;
        font-weight: 750;
        letter-spacing: -.055em;
    }

    .error403-description {
        max-width: 500px;
        margin-top: 1.25rem;
        color: rgba(255, 255, 255, .42);
        line-height: 1.8;
        font-size: .98rem;
    }

    .error403-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 2rem;
    }

    .error403-button {
        min-height: 56px;
        padding: 0 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, .1);
        text-decoration: none;
        font-size: 13px;
        font-weight: 650;
        cursor: pointer;
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .error403-button:hover {
        transform: translateY(-3px);
        cursor: pointer;
    }

    .error403-primary {
        color: #fff;
        background: linear-gradient(135deg,
                #7c3aed,
                #9333ea,
                #c026d3);
        box-shadow: 0 15px 40px rgba(124, 58, 237, .25);
    }

    .error403-secondary {
        color: rgba(255, 255, 255, .7);
        background: rgba(255, 255, 255, .045);
        backdrop-filter: blur(18px);
    }

    .error403-art {
        position: relative;
        width: 100%;
        aspect-ratio: 1;
        max-width: 470px;
        margin-inline: auto;
    }

    .error403-art svg {
        width: 100%;
        height: 100%;
        overflow: visible;
    }

    .error403-orbit {
        transform-origin: 250px 250px;
        animation: error403Orbit 22s linear infinite;
    }

    .error403-orbit-reverse {
        transform-origin: 250px 250px;
        animation: error403OrbitReverse 16s linear infinite;
    }

    @keyframes error403Orbit {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes error403OrbitReverse {
        to {
            transform: rotate(-360deg);
        }
    }

    .error403-shield {
        animation: error403Shield 4s ease-in-out infinite;
        transform-origin: center;
    }

    @keyframes error403Shield {

        0%,
        100% {
            transform: translateY(0) scale(1);
        }

        50% {
            transform: translateY(-12px) scale(1.025);
        }
    }

    .error403-lock {
        animation: error403Lock 2.8s ease-in-out infinite;
        transform-origin: center;
    }

    @keyframes error403Lock {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-5px);
        }
    }

    .error403-scan {
        animation: error403Scan 3.2s ease-in-out infinite;
    }

    @keyframes error403Scan {

        0%,
        100% {
            opacity: .15;
            transform: translateY(-65px);
        }

        50% {
            opacity: .8;
            transform: translateY(65px);
        }
    }

    @media (max-width: 800px) {
        .error403-content {
            grid-template-columns: 1fr;
            gap: 1rem;
            text-align: center;
        }

        .error403-copy {
            text-align: center;
            order: 2;
        }

        .error403-art {
            width: min(360px, 85vw);
            order: 1;
        }

        .error403-description {
            margin-inline: auto;
        }

        .error403-actions {
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .error403-content {
            width: min(100% - 24px, 1100px);
        }

        .error403-art {
            width: min(290px, 78vw);
        }

        .error403-actions {
            flex-direction: column;
        }

        .error403-button {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .error403 *,
        .error403::before,
        .error403::after {
            animation: none !important;
        }
    }
</style>

<div class="error403">

    <div class="error403-bg"></div>
    <div class="error403-grid"></div>

    <div class="error403-content">

        <div class="error403-copy">

            <div class="error403-label">
                <span class="error403-label-dot"></span>
                Access restricted
            </div>

            <div class="error403-code">
                403
            </div>

            <h1 class="error403-title">
                You can't<br>
                enter here.
            </h1>

            <p class="error403-description">
                This area is restricted to users with the appropriate
                permissions. If you think this is a mistake, contact
                your team administrator.
            </p>

            <div class="error403-actions">

                <button type="button" class="error403-button error403-primary"
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
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M19 12H5m7 7-7-7 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>

                    Go back
                </button>

                <a href="{{ auth()->check()
                ? route('dashboard', ['current_team' => request()->route('current_team')])
                : route('home') }}"
                class="error403-button error403-secondary">
                Return home
            </a>

            </div>

        </div>

        <div class="error403-art">

            <svg viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">

                <defs>

                    <radialGradient id="g403" cx="50%" cy="50%">
                        <stop offset="0%" stop-color="#8b5cf6" stop-opacity=".35" />
                        <stop offset="100%" stop-color="#8b5cf6" stop-opacity="0" />
                    </radialGradient>

                    <linearGradient id="shield403" x1="80" y1="50" x2="420" y2="440">
                        <stop stop-color="#c4b5fd" />
                        <stop offset=".45" stop-color="#8b5cf6" />
                        <stop offset="1" stop-color="#d946ef" />
                    </linearGradient>

                    <filter id="glow403">
                        <feGaussianBlur stdDeviation="12" />
                    </filter>

                </defs>

                <circle cx="250" cy="250" r="210" fill="url(#g403)" />

                <g class="error403-orbit">

                    <circle cx="250" cy="250" r="195" stroke="#a78bfa" stroke-opacity=".16" stroke-width="1"
                        stroke-dasharray="3 14" />

                    <circle cx="445" cy="250" r="5" fill="#c4b5fd" filter="url(#glow403)" />

                </g>

                <g class="error403-orbit-reverse">

                    <circle cx="250" cy="250" r="160" stroke="#e879f9" stroke-opacity=".16" stroke-width="1"
                        stroke-dasharray="2 18" />

                    <circle cx="410" cy="250" r="4" fill="#e879f9" />

                </g>

                <g class="error403-shield">

                    <path d="M250 92
                           L365 136
                           V232
                           C365 315 317 371 250 404
                           C183 371 135 315 135 232
                           V136
                           L250 92Z" fill="#0d0917" stroke="url(#shield403)" stroke-width="3" />

                    <path d="M250 113
                           L345 149
                           V229
                           C345 294 310 342 250 374
                           C190 342 155 294 155 229
                           V149
                           L250 113Z" fill="rgba(124,58,237,.08)" stroke="#a78bfa" stroke-opacity=".18" />

                    <g class="error403-lock">

                        <rect x="205" y="221" width="90" height="82" rx="16" fill="#171026"
                            stroke="#c4b5fd" stroke-width="2" />

                        <path d="M224 221V195C224 180.64 235.64 169 250 169C264.36 169 276 180.64 276 195V221"
                            stroke="#c4b5fd" stroke-width="9" stroke-linecap="round" />

                        <circle cx="250" cy="257" r="8" fill="#fff" />

                        <path d="M250 265V279" stroke="#fff" stroke-width="5" stroke-linecap="round" />

                    </g>

                </g>

                <path class="error403-scan" d="M125 250H375" stroke="#c4b5fd" stroke-width="2" stroke-opacity=".5"
                    filter="url(#glow403)" />

            </svg>

        </div>

    </div>

</div>
