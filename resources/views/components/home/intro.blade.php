<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
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

                     <h1 class="text-indigo-200 font-black" style="font-weight: 900;">
                                ClearCode<br>
                                Web Solutions
                            </h1>

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

                            <h1 style="font-weight: 600;">
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