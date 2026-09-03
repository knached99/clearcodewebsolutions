<div
    x-data="{
        lightbox: false,
        lightboxSrc: '',
        lightboxName: '',

        openLightbox(src, name = '') {
            this.lightboxSrc = src
            this.lightboxName = name
            this.lightbox = true
            document.body.classList.add('overflow-hidden')
        },

        closeLightbox() {
            this.lightbox = false
            this.lightboxSrc = ''
            this.lightboxName = ''
            document.body.classList.remove('overflow-hidden')
        }
    }"
    x-on:keydown.escape.window="closeLightbox()"
    class="min-h-screen bg-[#faf9fc] text-[#27232f] antialiased dark:bg-[#0d0b11] dark:text-white"
>
    {{-- ================================================================
        LIGHTBOX
    ================================================================= --}}

    <template x-teleport="body">
        <div
            x-show="lightbox"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/90 p-4 backdrop-blur-xl"
        >
            <button
                type="button"
                x-on:click="closeLightbox()"
                class="absolute right-4 top-4 z-30 flex size-12 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white backdrop-blur-xl transition hover:bg-white/20"
            >
                <flux:icon.x-mark class="size-5" />
            </button>

            <button
                type="button"
                x-on:click="closeLightbox()"
                class="absolute inset-0 cursor-default"
                aria-label="Close image"
            ></button>

            <img
                :src="lightboxSrc"
                :alt="lightboxName"
                x-on:click.stop
                class="relative z-10 max-h-[90vh] max-w-[94vw] rounded-3xl object-contain shadow-2xl"
            />

            <div
                class="absolute bottom-5 left-1/2 z-30 max-w-[90vw] -translate-x-1/2 rounded-full bg-white/10 px-4 py-2 backdrop-blur-xl"
            >
                <span
                    x-text="lightboxName"
                    class="block truncate text-sm font-medium text-white/80"
                ></span>
            </div>
        </div>
    </template>


    {{-- ================================================================
        HEADER
    ================================================================= --}}

    <header class="sticky top-0 z-50 border-b border-[#eae6f0]/80 bg-[#faf9fc]/90 backdrop-blur-2xl dark:border-white/5 dark:bg-[#0d0b11]/90">

        <div class="mx-auto flex min-h-[76px] max-w-[1580px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

            <div class="flex min-w-0 items-center gap-3">

                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate
                    class="group flex size-11 shrink-0 items-center justify-center rounded-2xl border border-[#e5e0eb] bg-white text-[#706a79] shadow-[0_3px_12px_rgba(40,30,60,.04)] transition hover:border-[#cfc4e5] hover:bg-[#f7f3ff] hover:text-[#7865b5] dark:border-white/10 dark:bg-white/5 dark:text-white/50"
                >
                    <flux:icon.arrow-left class="size-4 transition group-hover:-translate-x-0.5" />
                </a>

                <div class="hidden h-8 w-px bg-[#e6e1eb] sm:block dark:bg-white/10"></div>

                <div class="min-w-0">
                    <div class="flex min-w-0 items-center gap-2">
                        <h1 class="truncate text-base font-semibold tracking-[-0.02em] text-[#24202b] sm:text-lg dark:text-white">
                            {{ $ticket->subject }}
                        </h1>

                        <span class="hidden rounded-lg bg-[#f1edfa] px-2.5 py-1 font-mono text-[10px] font-semibold text-[#7969a1] md:inline">
                            {{ $ticket->ticket_number }}
                        </span>
                    </div>

                    <div class="mt-0.5 truncate text-xs text-[#8e8996] sm:text-sm">
                        {{ $ticket->name }}
                    </div>
                </div>
            </div>


            {{-- Header status --}}
            <div class="hidden items-center gap-3 sm:flex">

                <div
                    class="flex items-center gap-2 rounded-full border border-[#e8e1f4] bg-white px-3.5 py-2 text-sm font-semibold shadow-sm dark:border-white/10 dark:bg-white/5"
                >
                    <span
                        class="size-2 rounded-full
                            {{ match($ticket->status) {
                                'open' => 'bg-emerald-500',
                                'pending' => 'bg-amber-500',
                                'resolved' => 'bg-blue-500',
                                'closed' => 'bg-zinc-400',
                                default => 'bg-zinc-400',
                            } }}"
                    ></span>

                    <span class="text-[#625b6c] dark:text-white/70">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </div>

            </div>

        </div>
    </header>


    {{-- ================================================================
        PAGE
    ================================================================= --}}

    <main class="mx-auto w-full max-w-[1580px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">

        <div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1fr)_350px]">


            {{-- ========================================================
                MAIN COLUMN
            ========================================================= --}}

            <section class="min-w-0">

                {{-- Conversation heading --}}

                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                    <div>
                        <div class="mb-2 flex items-center gap-2.5">

                            <span class="flex size-8 items-center justify-center rounded-xl bg-[#eee9f8] text-[#8875b6]">
                                <flux:icon.chat-bubble-left class="size-4" />
                            </span>

                            <span class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#8875b6]">
                                Conversation
                            </span>

                        </div>

                        <h2 class="text-3xl font-bold tracking-[-0.045em] text-[#26222d] sm:text-4xl dark:text-white">
                            {{ $ticket->messages->count() }}

                            <span class="font-normal text-[#aaa5b0]">
                                {{ Str::plural('message', $ticket->messages->count()) }}
                            </span>
                        </h2>
                    </div>

                    <div class="sm:text-right">
                        <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#aaa5b0]">
                            Last activity
                        </div>

                        <div class="mt-1 text-sm font-medium text-[#625c69] dark:text-white/60">
                            {{ $ticket->last_activity_at?->diffForHumans() ?? '—' }}
                        </div>
                    </div>

                </div>


                {{-- ====================================================
                    CONVERSATION CARD
                ===================================================== --}}

                <div class="overflow-hidden rounded-[28px] border border-[#e9e4ef] bg-white shadow-[0_20px_60px_-40px_rgba(55,40,80,.30)] dark:border-white/10 dark:bg-[#151219]">

                    <div class="divide-y divide-[#f0edf3] dark:divide-white/5">

                        @forelse($ticket->messages as $message)

                            @php
                                $isCustomer = in_array(
                                    $message->author_type,
                                    ['guest', 'customer'],
                                    true
                                );

                                $initial = strtoupper(
                                    substr($message->author_name, 0, 1)
                                );
                            @endphp


                            {{-- Date divider --}}

                            @if(
                                $loop->first ||
                                !$message->created_at->isSameDay(
                                    $ticket->messages[$loop->index - 1]->created_at
                                )
                            )
                                <div class="flex items-center gap-4 px-5 py-6 sm:px-8">

                                    <div class="h-px flex-1 bg-[#eeeaf1] dark:bg-white/5"></div>

                                    <span class="rounded-full border border-[#e7e0f2] bg-[#faf8fd] px-3 py-1.5 text-[10px] font-semibold text-[#9186a3] dark:border-white/10 dark:bg-white/5">
                                        {{ $message->created_at->format('M j, Y') }}
                                    </span>

                                    <div class="h-px flex-1 bg-[#eeeaf1] dark:bg-white/5"></div>

                                </div>
                            @endif


                            {{-- Message --}}

                            <div class="px-5 py-6 sm:px-8 sm:py-7">

                                <div class="flex gap-4 {{ $isCustomer ? '' : 'flex-row-reverse' }}">

                                    {{-- Avatar --}}

                                    <div
                                        class="relative flex size-11 shrink-0 items-center justify-center rounded-2xl text-sm font-bold
                                        {{ $isCustomer
                                            ? 'bg-[#eeeaf7] text-[#756694] dark:bg-[#2a2235] dark:text-[#c8b8e7]'
                                            : 'bg-[#8875b6] text-white shadow-[0_8px_20px_-8px_rgba(136,117,182,.8)]'
                                        }}"
                                    >
                                        {{ $initial }}

                                        @if(!$isCustomer)
                                            <span class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-white bg-emerald-500 dark:border-[#151219]"></span>
                                        @endif
                                    </div>


                                    {{-- Message body --}}

                                    <div class="min-w-0 max-w-[88%] sm:max-w-[78%]">

                                        <div class="mb-2.5 flex flex-wrap items-center gap-2 {{ $isCustomer ? '' : 'justify-end' }}">

                                            <span class="text-sm font-semibold text-[#403b47] dark:text-white/90">
                                                {{ $message->author_name }}
                                            </span>

                                            @if(!$isCustomer)
                                                <span class="rounded-full bg-[#f0ebf8] px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-[#806ea7]">
                                                    Team
                                                </span>
                                            @endif

                                            <span class="text-xs text-[#aaa5b0]">
                                                {{ $message->created_at->format('g:i A') }}
                                            </span>

                                        </div>


                                        @if(filled($message->message))

                                            <div
                                                class="
                                                {{ $isCustomer
                                                    ? 'rounded-2xl rounded-tl-md border border-[#e7e3e9] bg-[#faf9fb] text-[#47424d] dark:border-white/10 dark:bg-white/5 dark:text-white/80'
                                                    : 'rounded-2xl rounded-tr-md bg-[#8b78b9] text-white shadow-[0_12px_30px_-18px_rgba(105,84,155,.8)]'
                                                }}
                                                px-5 py-4 text-[15px] leading-7 sm:text-base
                                                "
                                            >
                                                <div class="whitespace-pre-wrap break-words">
                                                    {{ $message->message }}
                                                </div>
                                            </div>

                                        @endif


                                        {{-- Attachments --}}

                                        @if($message->attachments->isNotEmpty())

                                            <div class="mt-3 space-y-3">

                                                @foreach($message->attachments as $attachment)

                                                    @php
                                                        $mime = strtolower($attachment->mime_type ?? '');

                                                        $isImage = str_starts_with(
                                                            $mime,
                                                            'image/'
                                                        );

                                                        $isPdf = $mime === 'application/pdf';

                                                        $previewUrl = route(
                                                            'ticket.attachments.preview',
                                                            [
                                                                'current_team' => request()->route('current_team'),
                                                                'attachment' => $attachment->id,
                                                            ]
                                                        );

                                                        $downloadUrl = route(
                                                            'ticket.attachments.download',
                                                            [
                                                                'current_team' => request()->route('current_team'),
                                                                'attachment' => $attachment->id,
                                                            ]
                                                        );
                                                    @endphp


                                                    @if($isImage)

                                                        <div class="group/image max-w-[720px] overflow-hidden rounded-2xl border border-[#e7e3eb] bg-[#f7f5f9] dark:border-white/10 dark:bg-white/5">

                                                            <button
                                                                type="button"
                                                                x-on:click="openLightbox(
                                                                    @js($previewUrl),
                                                                    @js($attachment->original_name)
                                                                )"
                                                                class="relative block w-full cursor-zoom-in"
                                                            >
                                                                <img
                                                                    src="{{ $previewUrl }}"
                                                                    alt="{{ $attachment->original_name }}"
                                                                    loading="lazy"
                                                                    class="block max-h-[620px] w-full object-cover transition duration-500 group-hover/image:scale-[1.015]"
                                                                />

                                                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-[#4d3e68]/0 transition group-hover/image:bg-[#4d3e68]/20">

                                                                    <span class="flex size-12 scale-90 items-center justify-center rounded-2xl bg-white text-[#77659d] opacity-0 shadow-2xl transition group-hover/image:scale-100 group-hover/image:opacity-100">
                                                                        <flux:icon.arrows-pointing-out class="size-5" />
                                                                    </span>

                                                                </div>
                                                            </button>

                                                            <div class="flex items-center justify-between border-t border-[#e8e3eb] bg-white px-4 py-3 dark:border-white/5 dark:bg-[#18151d]">

                                                                <div class="flex min-w-0 items-center gap-3">

                                                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-[#f0ebf8] text-[#8875b6]">
                                                                        <flux:icon.photo class="size-4" />
                                                                    </div>

                                                                    <div class="min-w-0">

                                                                        <div class="max-w-[300px] truncate text-xs font-semibold text-[#514c57] dark:text-white/80">
                                                                            {{ $attachment->original_name }}
                                                                        </div>

                                                                        <div class="mt-0.5 text-[10px] text-[#aaa5b0]">
                                                                            {{ number_format($attachment->size / 1024, 1) }} KB
                                                                        </div>

                                                                    </div>

                                                                </div>

                                                                <a
                                                                    href="{{ $downloadUrl }}"
                                                                    class="flex size-9 items-center justify-center rounded-xl text-[#aaa5b0] transition hover:bg-[#f2eef8] hover:text-[#806da8]"
                                                                    title="Download"
                                                                >
                                                                    <flux:icon.arrow-down-tray class="size-4" />
                                                                </a>

                                                            </div>

                                                        </div>

                                                    @else

                                                        <a
                                                            href="{{ $downloadUrl }}"
                                                            class="group/file flex max-w-xl items-center gap-3 rounded-2xl border border-[#e7e3eb] bg-[#faf9fb] p-3.5 transition hover:-translate-y-0.5 hover:border-[#d8cdea] hover:bg-white hover:shadow-lg dark:border-white/10 dark:bg-white/5"
                                                        >

                                                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm dark:bg-white/10">

                                                                @if($isPdf)
                                                                    <flux:icon.document-text class="size-5 text-red-500" />
                                                                @else
                                                                    <flux:icon.paper-clip class="size-5 text-[#8875b6]" />
                                                                @endif

                                                            </div>

                                                            <div class="min-w-0 flex-1">

                                                                <div class="truncate text-sm font-semibold text-[#46414c] dark:text-white/80">
                                                                    {{ $attachment->original_name }}
                                                                </div>

                                                                <div class="mt-1 text-xs text-[#aaa5b0]">
                                                                    {{ number_format($attachment->size / 1024, 1) }} KB
                                                                    <span class="mx-1">·</span>
                                                                    {{ strtoupper(pathinfo($attachment->original_name, PATHINFO_EXTENSION)) }}
                                                                </div>

                                                            </div>

                                                            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-[#eee9f7] text-[#806da8] transition group-hover/file:bg-[#8875b6] group-hover/file:text-white">
                                                                <flux:icon.arrow-down-tray class="size-4" />
                                                            </div>

                                                        </a>

                                                    @endif

                                                @endforeach

                                            </div>

                                        @endif


                                        <div class="mt-2 text-xs text-[#aaa5b0] {{ $isCustomer ? '' : 'text-right' }}">
                                            {{ $message->created_at->diffForHumans() }}
                                        </div>

                                    </div>

                                </div>

                            </div>

                        @empty

                            <div class="px-6 py-24 text-center">

                                <div class="mx-auto mb-5 flex size-16 items-center justify-center rounded-3xl bg-[#f0ebf8] text-[#8875b6]">
                                    <flux:icon.chat-bubble-left-right class="size-7" />
                                </div>

                                <h3 class="text-lg font-semibold text-[#36313d] dark:text-white">
                                    Nothing here yet
                                </h3>

                                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-[#938e99]">
                                    Send a reply below to start the conversation.
                                </p>

                            </div>

                        @endforelse

                    </div>


                    {{-- ==================================================
                        COMPOSER
                    =================================================== --}}

                    <div class="border-t border-[#ebe7ef] bg-[#fbfafc] p-3 sm:p-5 dark:border-white/5 dark:bg-[#111016]">

                        <div class="overflow-hidden rounded-[24px] border border-[#e5e0e9] bg-white shadow-[0_12px_40px_-25px_rgba(50,40,65,.35)] focus-within:border-[#cfc1e5] focus-within:ring-4 focus-within:ring-[#eee9f7] dark:border-white/10 dark:bg-[#18151d] dark:focus-within:ring-[#261e30]">

                            <textarea
                                wire:model="newMessage"
                                rows="4"
                                placeholder="Write a reply to {{ $ticket->name }}..."
                                class="block min-h-[130px] w-full resize-none border-0 bg-transparent px-5 py-5 text-[15px] leading-7 text-[#332e39] outline-none placeholder:text-[#aaa5af] focus:border-0 focus:ring-0 sm:text-base dark:text-white"
                            ></textarea>


                            @if(count($attachments))

                                <div class="border-t border-[#eeeaf1] px-4 py-4 dark:border-white/5">

                                    <div class="mb-3 flex items-center justify-between">

                                        <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#8875b6]">
                                            Ready to send
                                        </span>

                                        <span class="text-xs text-[#aaa5b0]">
                                            {{ count($attachments) }}
                                            {{ Str::plural('file', count($attachments)) }}
                                        </span>

                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">

                                        @foreach($attachments as $index => $attachment)

                                            @if($attachment)

                                                @php
                                                    $uploadMime = strtolower(
                                                        $attachment->getMimeType()
                                                    );

                                                    $uploadIsImage = str_starts_with(
                                                        $uploadMime,
                                                        'image/'
                                                    );
                                                @endphp

                                                <div class="group relative overflow-hidden rounded-2xl border border-[#e8e2ef] bg-[#faf8fc] dark:border-white/10 dark:bg-white/5">

                                                    @if($uploadIsImage)

                                                        <div class="aspect-[16/10] overflow-hidden">
                                                            <img
                                                                src="{{ $attachment->temporaryUrl() }}"
                                                                alt="{{ $attachment->getClientOriginalName() }}"
                                                                class="h-full w-full object-cover"
                                                            />
                                                        </div>

                                                    @else

                                                        <div class="flex h-20 items-center gap-3 px-3">

                                                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white dark:bg-white/10">

                                                                @if($uploadMime === 'application/pdf')
                                                                    <flux:icon.document-text class="size-5 text-red-500" />
                                                                @else
                                                                    <flux:icon.paper-clip class="size-5 text-[#8875b6]" />
                                                                @endif

                                                            </div>

                                                            <div class="min-w-0">

                                                                <div class="truncate text-xs font-semibold text-[#57515e] dark:text-white/70">
                                                                    {{ $attachment->getClientOriginalName() }}
                                                                </div>

                                                                <div class="mt-1 text-[10px] text-[#aaa5b0]">
                                                                    {{ number_format($attachment->getSize() / 1024, 1) }} KB
                                                                </div>

                                                            </div>

                                                        </div>

                                                    @endif

                                                    <button
                                                        type="button"
                                                        wire:click="removeAttachment({{ $index }})"
                                                        class="absolute right-2 top-2 flex size-8 items-center justify-center rounded-xl bg-black/70 text-white opacity-0 shadow-lg transition group-hover:opacity-100 hover:bg-red-500"
                                                    >
                                                        <flux:icon.x-mark class="size-3.5" />
                                                    </button>

                                                </div>

                                            @endif

                                        @endforeach

                                    </div>

                                </div>

                            @endif


                            {{-- Uploading --}}

                            <div
                                wire:loading
                                wire:target="attachments"
                                class="border-t border-[#eeeaf1] px-5 py-3 dark:border-white/5"
                            >
                                <div class="flex items-center gap-2 text-xs font-medium text-[#8875b6]">
                                    <flux:icon.arrow-path class="size-4 animate-spin" />
                                    Uploading attachments…
                                </div>
                            </div>


                            {{-- Composer footer --}}

                            <div class="flex flex-col gap-3 border-t border-[#eeeaf1] px-3 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-white/5">

                                <div class="flex items-center justify-between gap-2 sm:justify-start">

                                    <label class="group flex cursor-pointer items-center gap-2 rounded-xl px-2 py-2 text-sm font-semibold text-[#716b78] transition hover:bg-[#f4f0f8] hover:text-[#806da8]">

                                        <span class="flex size-9 items-center justify-center rounded-xl bg-[#f1ecf8] text-[#8875b6]">
                                            <flux:icon.paper-clip class="size-4" />
                                        </span>

                                        Attach

                                        <input
                                            type="file"
                                            wire:model="attachments"
                                            multiple
                                            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                                            class="hidden"
                                        />

                                    </label>

                                    <span class="hidden text-xs text-[#aaa5b0] sm:inline">
                                        Images & files · 10 MB max
                                    </span>

                                </div>


                               <flux:button
                                wire:click="sendMessage"
                                wire:loading.attr="disabled"
                                wire:target="sendMessage"
                                variant="primary"
                                class="min-h-11 cursor-pointer rounded-xl bg-[#8875b6] px-5 text-sm font-semibold shadow-[0_10px_25px_-12px_rgba(120,100,165,.8)] transition-colors duration-200 hover:bg-black hover:text-white disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <flux:icon.paper-airplane
                                    wire:loading.remove
                                    wire:target="sendMessage"
                                    class="size-4"
                                />

                                <flux:icon.arrow-path
                                    wire:loading
                                    wire:target="sendMessage"
                                    class="size-4 animate-spin"
                                />

                                <span wire:loading.remove wire:target="sendMessage">
                                    Send reply
                                </span>

                                <span wire:loading wire:target="sendMessage">
                                    Sending…
                                </span>
                            </flux:button>
                            </div>

                        </div>


                        @error('newMessage')
                            <div class="mt-2 px-2 text-xs font-medium text-red-600">
                                {{ $message }}
                            </div>
                        @enderror

                        @error('attachments.*')
                            <div class="mt-2 px-2 text-xs font-medium text-red-600">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </section>


            {{-- ========================================================
                SIDEBAR
            ========================================================= --}}

            <aside class="space-y-5 xl:sticky xl:top-24 xl:self-start">

                {{-- ====================================================
                    CUSTOMER
                ===================================================== --}}

                <div class="overflow-hidden rounded-[26px] border border-[#e8e3ed] bg-white shadow-[0_12px_40px_-30px_rgba(50,40,65,.3)] dark:border-white/10 dark:bg-[#151219]">

                    <div class="border-b border-[#eeeaf1] p-5 dark:border-white/5">

                        <div class="mb-5 text-[10px] font-bold uppercase tracking-[0.16em] text-[#9a91a5]">
                            Customer
                        </div>

                        <div class="flex items-center gap-3">

                            <div class="flex size-13 shrink-0 items-center justify-center rounded-2xl bg-[#eee9f7] text-lg font-bold text-[#806da8]">
                                {{ strtoupper(substr($ticket->name, 0, 1)) }}
                            </div>

                            <div class="min-w-0">

                                <div class="truncate text-base font-semibold text-[#35303b] dark:text-white">
                                    {{ $ticket->name }}
                                </div>

                                <div class="mt-1 truncate text-sm text-[#9a949f]">
                                    {{ $ticket->email }}
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="p-4">

                        <a
                            href="mailto:{{ $ticket->email }}"
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#f2edf9] text-sm font-semibold text-[#75629e] transition hover:bg-[#eae3f5]"
                        >
                            <flux:icon.envelope class="size-4" />
                            Email customer
                        </a>

                    </div>

                </div>


                {{-- ====================================================
                    TICKET DETAILS
                ===================================================== --}}

                <div class="rounded-[26px] border border-[#e8e3ed] bg-white p-5 shadow-[0_12px_40px_-30px_rgba(50,40,65,.3)] dark:border-white/10 dark:bg-[#151219]">

                    <div class="mb-6 flex items-center justify-between">

                        <div>
                            <div class="text-base font-semibold text-[#35303b] dark:text-white">
                                Ticket details
                            </div>

                            <div class="mt-1 text-xs text-[#9a949f]">
                                Request information
                            </div>
                        </div>

                        <div class="flex size-10 items-center justify-center rounded-xl bg-[#f1ecf8] text-[#8875b6]">
                            <flux:icon.ticket class="size-5" />
                        </div>

                    </div>


                    <div class="space-y-5">

                        {{-- =================================================
                            STATUS DROPDOWN
                        ================================================== --}}

                        <div>
                            <div class="mb-2 text-xs font-medium text-[#9b95a0]">
                                Status
                            </div>

                            <div
                                x-data="{ open: false }"
                                x-on:click.outside="open = false"
                                class="relative"
                            >

                                <button
                                    type="button"
                                    x-on:click="open = !open"
                                    class="flex min-h-12 w-full items-center justify-between rounded-2xl border border-[#e4dfea] bg-[#fcfbfd] px-4 text-sm font-semibold text-[#48424f] transition hover:border-[#cfc2e2] hover:bg-white focus:outline-none focus:ring-4 focus:ring-[#f0ebf8] dark:border-white/10 dark:bg-white/5 dark:text-white"
                                >

                                    <span class="flex items-center gap-3">

                                        <span
                                            class="size-2.5 rounded-full
                                            {{ match($status) {
                                                'open' => 'bg-emerald-500',
                                                'pending' => 'bg-amber-500',
                                                'resolved' => 'bg-blue-500',
                                                'closed' => 'bg-zinc-400',
                                                default => 'bg-zinc-400',
                                            } }}"
                                        ></span>

                                        {{ ucfirst($status) }}

                                    </span>

                                    <flux:icon.chevron-down
                                        class="size-4 text-[#9d96a5] transition"
                                        x-bind:class="open && 'rotate-180'"
                                    />

                                </button>


                                <div
                                    x-show="open"
                                    x-cloak
                                    x-transition.origin.top
                                    class="absolute left-0 right-0 z-50 mt-2 overflow-hidden rounded-2xl border border-[#e4dfea] bg-white p-1.5 shadow-[0_20px_50px_-20px_rgba(40,30,55,.3)] dark:border-white/10 dark:bg-[#1d1922]"
                                >

                                    @foreach([
                                        'open' => 'Open',
                                        'pending' => 'Pending',
                                        'resolved' => 'Resolved',
                                        'closed' => 'Closed',
                                    ] as $value => $label)

                                        <button
                                            type="button"
                                            wire:click="updateStatus('{{ $value }}')"
                                            x-on:click="open = false"
                                            wire:loading.attr="disabled"
                                            wire:target="updateStatus('{{ $value }}')"
                                            class="flex w-full items-center justify-between rounded-xl px-3.5 py-3 text-left text-sm font-medium transition hover:bg-[#f5f1f9] dark:hover:bg-white/5"
                                        >

                                            <span class="flex items-center gap-3">

                                                <span
                                                    class="size-2.5 rounded-full
                                                    {{ match($value) {
                                                        'open' => 'bg-emerald-500',
                                                        'pending' => 'bg-amber-500',
                                                        'resolved' => 'bg-blue-500',
                                                        'closed' => 'bg-zinc-400',
                                                    } }}"
                                                ></span>

                                                {{ $label }}

                                            </span>

                                            @if($status === $value)
                                                <flux:icon.check class="size-4 text-[#8875b6]" />
                                            @endif

                                        </button>

                                    @endforeach

                                </div>

                            </div>
                        </div>


                        {{-- =================================================
                            PRIORITY DROPDOWN
                        ================================================== --}}

                        <div class="border-t border-[#eeeaf1] pt-5 dark:border-white/5">

                            <div class="mb-2 text-xs font-medium text-[#9b95a0]">
                                Priority
                            </div>

                            <div
                                x-data="{ open: false }"
                                x-on:click.outside="open = false"
                                class="relative"
                            >

                                <button
                                    type="button"
                                    x-on:click="open = !open"
                                    class="flex min-h-12 w-full items-center justify-between rounded-2xl border border-[#e4dfea] bg-[#fcfbfd] px-4 text-sm font-semibold text-[#48424f] transition hover:border-[#cfc2e2] hover:bg-white focus:outline-none focus:ring-4 focus:ring-[#f0ebf8] dark:border-white/10 dark:bg-white/5 dark:text-white"
                                >

                                    <span class="flex items-center gap-3">

                                        <span
                                            class="flex size-7 items-center justify-center rounded-lg
                                            {{ match($priority) {
                                                'urgent' => 'bg-red-50 text-red-600',
                                                'high' => 'bg-orange-50 text-orange-600',
                                                'normal' => 'bg-[#eee9f7] text-[#806da8]',
                                                'low' => 'bg-zinc-100 text-zinc-500',
                                                default => 'bg-zinc-100 text-zinc-500',
                                            } }}"
                                        >
                                            <flux:icon.flag class="size-3.5" />
                                        </span>

                                        {{ ucfirst($priority ?: 'normal') }}

                                    </span>

                                    <flux:icon.chevron-down
                                        class="size-4 text-[#9d96a5] transition"
                                        x-bind:class="open && 'rotate-180'"
                                    />

                                </button>


                                <div
                                    x-show="open"
                                    x-cloak
                                    x-transition.origin.top
                                    class="absolute left-0 right-0 z-50 mt-2 overflow-hidden rounded-2xl border border-[#e4dfea] bg-white p-1.5 shadow-[0_20px_50px_-20px_rgba(40,30,55,.3)] dark:border-white/10 dark:bg-[#1d1922]"
                                >

                                    @foreach([
                                        'low' => [
                                            'label' => 'Low',
                                            'description' => 'Can wait',
                                            'class' => 'bg-zinc-100 text-zinc-500',
                                        ],
                                        'normal' => [
                                            'label' => 'Normal',
                                            'description' => 'Standard request',
                                            'class' => 'bg-[#eee9f7] text-[#806da8]',
                                        ],
                                        'high' => [
                                            'label' => 'High',
                                            'description' => 'Needs attention',
                                            'class' => 'bg-orange-50 text-orange-600',
                                        ],
                                        'urgent' => [
                                            'label' => 'Urgent',
                                            'description' => 'Needs immediate action',
                                            'class' => 'bg-red-50 text-red-600',
                                        ],
                                    ] as $value => $option)

                                        <button
                                            type="button"
                                            wire:click="updatePriority('{{ $value }}')"
                                            x-on:click="open = false"
                                            wire:loading.attr="disabled"
                                            class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition hover:bg-[#f5f1f9] dark:hover:bg-white/5"
                                        >

                                            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $option['class'] }}">
                                                <flux:icon.flag class="size-3.5" />
                                            </span>

                                            <span class="min-w-0 flex-1">

                                                <span class="block text-sm font-semibold text-[#49434f] dark:text-white/90">
                                                    {{ $option['label'] }}
                                                </span>

                                                <span class="block text-[11px] text-[#aaa4af]">
                                                    {{ $option['description'] }}
                                                </span>

                                            </span>

                                            @if($priority === $value)
                                                <flux:icon.check class="size-4 shrink-0 text-[#8875b6]" />
                                            @endif

                                        </button>

                                    @endforeach

                                </div>

                            </div>

                        </div>


                        {{-- Ticket number --}}

                        <div class="border-t border-[#eeeaf1] pt-5 dark:border-white/5">

                            <div class="text-xs font-medium text-[#9b95a0]">
                                Ticket number
                            </div>

                            <div class="mt-2 font-mono text-sm font-semibold text-[#4a4451] dark:text-white/80">
                                {{ $ticket->ticket_number }}
                            </div>

                        </div>


                        {{-- Created --}}

                        <div class="border-t border-[#eeeaf1] pt-5 dark:border-white/5">

                            <div class="text-xs font-medium text-[#9b95a0]">
                                Created
                            </div>

                            <div class="mt-2 text-sm font-semibold text-[#4a4451] dark:text-white/80">
                                {{ $ticket->created_at->format('M j, Y · g:i A') }}
                            </div>

                        </div>


                        {{-- Last activity --}}

                        <div>

                            <div class="text-xs font-medium text-[#9b95a0]">
                                Last activity
                            </div>

                            <div class="mt-2 text-sm font-semibold text-[#4a4451] dark:text-white/80">
                                {{ $ticket->last_activity_at?->diffForHumans() ?? '—' }}
                            </div>

                        </div>

                    </div>

                </div>


                {{-- ====================================================
                    ACTIVITY CARD
                ===================================================== --}}

                <div class="relative overflow-hidden rounded-[26px] bg-[#62547d] p-6 text-white shadow-[0_20px_50px_-30px_rgba(80,60,110,.7)]">

                    <div class="absolute -right-20 -top-20 size-48 rounded-full bg-white/[0.07] blur-3xl"></div>

                    <div class="relative">

                        <div class="mb-7 flex items-center justify-between">

                            <span class="text-[10px] font-bold uppercase tracking-[0.16em] text-white/60">
                                Activity
                            </span>

                            <div class="flex size-9 items-center justify-center rounded-xl bg-white/10">
                                <flux:icon.bolt class="size-4" />
                            </div>

                        </div>

                        <div class="text-5xl font-bold tracking-[-0.04em]">
                            {{ $ticket->messages->count() }}
                        </div>

                        <div class="mt-2 text-sm text-white/60">
                            {{ Str::plural('message', $ticket->messages->count()) }} exchanged
                        </div>

                    </div>

                </div>

            </aside>

        </div>

    </main>
</div>