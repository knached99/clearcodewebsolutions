<div class="w-full max-w-full">

    <div
        class="flex w-full max-w-full items-center gap-5 bg-transparent px-4 py-5 sm:gap-7 sm:px-6"
    >

        {{-- =========================================================
             LOGO
        ========================================================== --}}

        <a
            href="{{ route('home') }}"
            aria-label="ClearCode WebSolutions"
            class="flex shrink-0 items-center"
        >
            <img
                src="{{ asset('assets/img/app__logo.jpg') }}"
                alt="ClearCode WebSolutions"
                class="block h-auto w-[130px] object-contain
                       sm:w-[165px]
                       lg:w-[190px]
                       drop-shadow-[0_4px_12px_rgba(0,0,0,0.75)]"
            >
        </a>


        {{-- =========================================================
             BRAND TEXT
        ========================================================== --}}

        <div class="min-w-0 flex-1 leading-none">

            <div
                class="text-2xl font-black tracking-[-0.04em] text-white
                       drop-shadow-[0_2px_7px_rgba(0,0,0,0.95)]
                       sm:text-3xl"
            >
                ClearCode
            </div>

            <div
                class="mt-2 text-xs font-bold uppercase tracking-[0.22em]
                       text-[#ADD322]
                       drop-shadow-[0_2px_6px_rgba(0,0,0,0.95)]
                       sm:text-sm"
            >
                WebSolutions
            </div>

        </div>

    </div>

</div>