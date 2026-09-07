<style>
    /* 
       True Fluid Physics Logo
       Uses an embedded SVG with feTurbulence and a Metaball Goo filter for actual fluid math
    */
    .liquid-logo {
        /* Base color to keep letters solid white where there is no fluid */
        background-color: #ffffff;
        
        /* The URL-encoded animated SVG fluid simulation */
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100%25' height='100%25'%3E%3Cdefs%3E%3Cfilter id='fluid'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.02' numOctaves='3' result='noise'%3E%3Canimate attributeName='baseFrequency' values='0.02;0.035;0.02' dur='12s' repeatCount='indefinite'/%3E%3C/feTurbulence%3E%3CfeDisplacementMap in='SourceGraphic' in2='noise' scale='45' xChannelSelector='R' yChannelSelector='G' result='warp'/%3E%3CfeGaussianBlur in='warp' stdDeviation='12' result='blur'/%3E%3CfeColorMatrix in='blur' mode='matrix' values='1 0 0 0 0 0 1 0 0 0 0 0 1 0 0 0 0 0 35 -15' result='goo'/%3E%3CfeComposite in='goo' in2='SourceGraphic' operator='in'/%3E%3C/filter%3E%3C/defs%3E%3Crect width='100%25' height='100%25' fill='%23ffffff'/%3E%3Cg filter='url(%23fluid)'%3E%3Ccircle cx='20%25' cy='30%25' r='40%25' fill='%23800080'%3E%3Canimate attributeName='cx' values='20%25;80%25;20%25' dur='16s' repeatCount='indefinite'/%3E%3Canimate attributeName='cy' values='30%25;70%25;30%25' dur='14s' repeatCount='indefinite'/%3E%3C/circle%3E%3Ccircle cx='80%25' cy='70%25' r='45%25' fill='%2334eb8f'%3E%3Canimate attributeName='cx' values='80%25;20%25;80%25' dur='13s' repeatCount='indefinite'/%3E%3Canimate attributeName='cy' values='70%25;20%25;70%25' dur='17s' repeatCount='indefinite'/%3E%3C/circle%3E%3Ccircle cx='50%25' cy='50%25' r='35%25' fill='%23c8a2c8'%3E%3Canimate attributeName='r' values='35%25;55%25;35%25' dur='11s' repeatCount='indefinite'/%3E%3Canimate attributeName='cy' values='50%25;20%25;50%25' dur='19s' repeatCount='indefinite'/%3E%3C/circle%3E%3C/g%3E%3C/svg%3E");
        background-size: cover;
        background-position: center;
        
        /* Strict clipping to the physical letter strokes */
        -webkit-background-clip: text;
        background-clip: text;
        
        /* Hide the native text color layer to reveal the liquid underneath */
        color: transparent !important;
        -webkit-text-fill-color: transparent !important;
    }
</style>

<section class="intro fullscreen">

    <div class="color-layer color-layer-mobile color-layer-dark"></div>

    {{-- Header --}}
    <header class="intro__header">

        <div class="container-fluid px-4">

            <div class="row gx-5">

                <div class="col-12">

                    <div class="logo">

                        <!-- ADDED 'liquid-logo' CLASS HERE. TEXT IS UNTOUCHED -->
                        <h1 class="text-white liquid-logo">
                            ClearCode<br>
                            Web Solutions
                        </h1>

                    </div>

                </div>

            </div>

        </div>

    </header>

    {{-- Hero Content --}}
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
                                class="class="btn btn-outline-light scroll-to-preview"
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

    {{-- Scroll --}}
    <a
        href="#services"
        class="intro__scroll scroll-to-preview"
    >
        <span></span>
    </a>

</section>