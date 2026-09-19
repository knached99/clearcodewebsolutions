<div class="min-h-screen bg-[#07070c] text-white
            flex items-center justify-center px-5">

    <div
        class="relative
               w-full max-w-lg
               overflow-hidden
               rounded-[2rem]
               border border-white/[0.08]
               bg-white/[0.025]
               backdrop-blur-2xl
               p-8 sm:p-12
               text-center
               shadow-[0_40px_120px_rgba(0,0,0,.5)]"
    >

        <div
            class="absolute
                   -top-32
                   left-1/2
                   -translate-x-1/2
                   w-80 h-80
                   rounded-full
                   bg-[#ADD322]/10
                   blur-[100px]"
        ></div>


        <div class="relative">

            <div
                class="mx-auto
                       w-20 h-20
                       rounded-full
                       bg-[#ADD322]/10
                       border border-[#ADD322]/20
                       flex items-center
                       justify-center"
            >
                <i
                    class="fa-solid fa-check
                           text-3xl
                           text-[#ADD322]"
                ></i>
            </div>


            <div
                class="mt-7
                       text-[9px]
                       uppercase
                       tracking-[0.3em]
                       font-black
                       text-[#ADD322]"
            >
                Payment successful
            </div>


            <h1
                class="mt-3
                       text-3xl
                       sm:text-4xl
                       font-black"
            >
                You're all set.
            </h1>


            <p
                class="mt-4
                       text-sm
                       leading-6
                       text-white/40"
            >
                Your payment has been received successfully.
                A receipt will be sent to
                {{ $invoice->customer?->email }}.
            </p>


            <div
                class="mt-8
                       rounded-2xl
                       border border-white/[0.06]
                       bg-black/20
                       px-5 py-4"
            >

                <div class="flex justify-between">

                    <span class="text-xs text-white/30">
                        Amount paid
                    </span>

                    <strong class="text-[#ADD322]">
                        ${{ number_format($invoice->total_amount / 100, 2) }}
                    </strong>

                </div>

            </div>

        </div>

    </div>

</div>