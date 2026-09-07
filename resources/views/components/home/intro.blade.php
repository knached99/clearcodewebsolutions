<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')

  <style>
    /*
    |--------------------------------------------------------------------------
    | CLEARCODE — LAVA LAMP + WATER FLUID LOGO
    |--------------------------------------------------------------------------
    |
    | Combined Physics:
    | 1. Lava Lamp: Slow, vertical rising/falling blobs with metaball gooey surface tension.
    | 2. Water Dynamics: Fine fractal noise turbulence (`feTurbulence`) creating 
    |    internal fluid ripples, caustics, and liquid movement.
    |
    | Colors: Strict palette of Lilac, Purples, and Greens.
    | No hover effect. No glass effect. No neon glow.
    |--------------------------------------------------------------------------
    */

    .liquid-logo {
        position: relative;
        display: inline-block;

        /* Soft off-white base */
        background-color: #eee8f2ec;

        /* Lava lamp blobs + water ripple turbulence SVG engine */
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100%25' height='100%25' viewBox='0 0 600 300' preserveAspectRatio='none'%3E%3Cdefs%3E%3Cfilter id='lava-water' x='-35%25' y='-35%25' width='170%25' height='170%25'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.03 0.05' numOctaves='2' result='noise'%3E%3Canimate attributeName='baseFrequency' values='0.03 0.05;0.05 0.03;0.03 0.05' dur='8s' repeatCount='indefinite'/%3E%3C/feTurbulence%3E%3CfeDisplacementMap in='SourceGraphic' in2='noise' scale='12' xChannelSelector='R' yChannelSelector='G' result='displaced'/%3E%3CfeGaussianBlur in='displaced' stdDeviation='14' result='blur'/%3E%3CfeColorMatrix in='blur' mode='matrix' values='1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 32 -14' result='goo'/%3E%3C/filter%3E%3ClinearGradient id='p1' x1='0%25' y1='100%25' x2='100%25' y2='0%25'%3E%3Cstop offset='0%25' stop-color='%234c1d95'/%3E%3Cstop offset='100%25' stop-color='%238b5cf6'/%3E%3C/linearGradient%3E%3ClinearGradient id='p2' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%236d28d9'/%3E%3Cstop offset='100%25' stop-color='%23c8a2c8'/%3E%3C/linearGradient%3E%3ClinearGradient id='g1' x1='100%25' y1='100%25' x2='0%25' y2='0%25'%3E%3Cstop offset='0%25' stop-color='%2322c875'/%3E%3Cstop offset='100%25' stop-color='%2334eb8f'/%3E%3C/linearGradient%3E%3ClinearGradient id='l1' x1='0%25' y1='50%25' x2='100%25' y2='50%25'%3E%3Cstop offset='0%25' stop-color='%23c8a2c8'/%3E%3Cstop offset='100%25' stop-color='%23e1c9e1'/%3E%3C/linearGradient%3E%3ClinearGradient id='g2' x1='50%25' y1='0%25' x2='50%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%23065f46'/%3E%3Cstop offset='100%25' stop-color='%235bffa7'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='600' height='300' fill='%23eee8f2'/%3E%3Cg filter='url(%23lava-water)'%3E%3Cellipse cx='150' cy='250' rx='95' ry='85' fill='url(%23p1)'%3E%3Canimate attributeName='cy' values='250;60;250' dur='14s' repeatCount='indefinite'/%3E%3Canimate attributeName='cx' values='150;220;150' dur='18s' repeatCount='indefinite'/%3E%3Canimate attributeName='rx' values='95;70;110;95' dur='10s' repeatCount='indefinite'/%3E%3C/ellipse%3E%3Cellipse cx='400' cy='80' rx='110' ry='90' fill='url(%23g1)'%3E%3Canimate attributeName='cy' values='80;240;80' dur='16s' repeatCount='indefinite'/%3E%3Canimate attributeName='cx' values='400;320;450;400' dur='20s' repeatCount='indefinite'/%3E%3C/ellipse%3E%3Cellipse cx='280' cy='150' rx='80' ry='70' fill='url(%23l1)'%3E%3Canimate attributeName='cy' values='150;50;220;150' dur='12s' repeatCount='indefinite'/%3E%3Canimate attributeName='cx' values='280;380;180;280' dur='15s' repeatCount='indefinite'/%3E%3C/ellipse%3E%3Cellipse cx='480' cy='220' rx='90' ry='80' fill='url(%23p2)'%3E%3Canimate attributeName='cy' values='220;90;220' dur='13s' repeatCount='indefinite'/%3E%3Canimate attributeName='cx' values='480;350;480' dur='17s' repeatCount='indefinite'/%3E%3C/ellipse%3E%3Cellipse cx='100' cy='120' rx='75' ry='65' fill='url(%23g2)'%3E%3Canimate attributeName='cy' values='120;260;120' dur='15s' repeatCount='indefinite'/%3E%3Canimate attributeName='cx' values='100;180;100' dur='19s' repeatCount='indefinite'/%3E%3C/ellipse%3E%3C/g%3E%3C/svg%3E");

        background-size: cover;
        background-position: center;

        /* Clip fluid strictly to letter strokes */
        -webkit-background-clip: text;
        background-clip: text;

        /* Hide native text color to reveal fluid underneath */
        color: transparent !important;
        -webkit-text-fill-color: transparent !important;

        /* Soft outline for letter definition */
        -webkit-text-stroke: 1.4px #ddd2e4;

        text-shadow: none;
        transition: none;
    }

    /* Lock hover state to match static state */
    .logo:hover .liquid-logo {
        background-color: #eee8f2;
        color: transparent !important;
        -webkit-text-fill-color: transparent !important;
        -webkit-text-stroke: 1.4px #ddd2e4;
        text-shadow: none;
    }

    .logo {
        cursor: default;
    }

    @media (max-width: 767px) {
        .liquid-logo { -webkit-text-stroke: 1.1px #ddd2e4; }
        .logo:hover .liquid-logo { -webkit-text-stroke: 1.1px #ddd2e4; }
    }

    @media (min-width: 768px) and (max-width: 1199px) {
        .liquid-logo { -webkit-text-stroke: 1.3px #ddd2e4; }
    }

    @media (min-width: 1200px) {
        .liquid-logo { -webkit-text-stroke: 1.4px #ddd2e4; }
    }

    @media (prefers-reduced-motion: reduce) {
        .liquid-logo {
            background-image: linear-gradient(90deg, #4c1d95, #8b5cf6, #c8a2c8, #34eb8f, #065f46);
            background-color: transparent;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent !important;
            -webkit-text-fill-color: transparent !important;
            -webkit-text-stroke: 1.2px #ddd2e4;
        }
    }
</style>
</head>


<body class="min-h-screen overflow-x-hidden bg-black text-white antialiased">

    <section class="intro fullscreen">

        {{-- Background --}}
        <div class="color-layer color-layer-mobile color-layer-dark"></div>


        {{-- =========================================================
             HEADER
             ========================================================= --}}
        <header class="intro__header">

            <div class="container-fluid px-4">

                <div class="row gx-5">

                    <div class="col-12">

                        <div class="logo">

                            <h1 class="liquid-logo">
                                ClearCode<br>
                                Web Solutions
                            </h1>

                        </div>

                    </div>

                </div>

            </div>

        </header>


        {{-- =========================================================
             HERO CONTENT
             ========================================================= --}}
        <div class="intro__content">

            <div class="headline">

                <div class="container-fluid px-4">

                    <div class="row gx-5">

                        <div class="col-12">

                            <h1>
                                Building the web<br>
                                Clearly
                            </h1>


                            <p>
                                Welcome to Clear Code Web Solutions.
                                We create

                                <span style="color: #cee57a">
                                    modern, high-performance websites
                                    and digital solutions
                                </span>

                                that help businesses stand out and grow.
                            </p>


                            {{-- =================================================
                                 BUTTONS
                                 ================================================= --}}
                            <div class="headline__btngroup">

                                <a
                                    class="btn btn-solid-light"
                                    href="#contact"
                                >
                                    <span class="btn-caption">
                                        Get Started
                                    </span>
                                </a>


                                <a
                                    href="#work"
                                    class="btn btn-outline-light scroll-to-preview"
                                >
                                    <span class="btn-caption">
                                        View Our Work
                                    </span>
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             SCROLL INDICATOR
             ========================================================= --}}
        <a
            href="#services"
            class="intro__scroll scroll-to-preview"
            aria-label="Scroll to services"
        >
            <span></span>
        </a>

    </section>


    {{-- =============================================================
         TOAST
         ============================================================= --}}
    @persist('toast')

        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>

    @endpersist


    @fluxScripts

</body>
</html>