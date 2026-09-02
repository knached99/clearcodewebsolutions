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
    class="min-h-screen bg-[#f7f5fb] text-zinc-900 dark:bg-[#09070f] dark:text-white"
>

    {{-- ================================================================
        LIGHTBOX
    ================================================================= --}}
    <template x-teleport="body">
        <div
            x-show="lightbox"
            x-cloak
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-[#08050d]/95 p-5 backdrop-blur-xl"
        >

            <button
                type="button"
                x-on:click="closeLightbox()"
                class="absolute right-5 top-5 z-30 flex size-11 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white backdrop-blur-xl transition hover:bg-white/20"
            >
                <flux:icon.x-mark class="size-5" />
            </button>

            <div
                class="absolute bottom-6 left-1/2 z-30 max-w-[80vw] -translate-x-1/2 rounded-full border border-white/10 bg-white/10 px-4 py-2 backdrop-blur-xl"
            >
                <span
                    x-text="lightboxName"
                    class="block truncate text-xs font-medium text-white/70"
                ></span>
            </div>

            <button
                type="button"
                x-on:click="closeLightbox()"
                class="absolute inset-0"
                aria-label="Close"
            ></button>

            <img
                :src="lightboxSrc"
                :alt="lightboxName"
                x-on:click.stop
                class="relative z-10 max-h-[88vh] max-w-[94vw] rounded-2xl object-contain shadow-[0_30px_100px_rgba(0,0,0,.5)]"
            />
        </div>
    </template>


    {{-- ================================================================
        TOP HEADER
    ================================================================= --}}
    <header class="sticky top-0 z-40 border-b border-violet-200/60 bg-white/80 backdrop-blur-2xl dark:border-violet-900/20 dark:bg-[#0b0812]/80">

        <div class="mx-auto flex h-[72px] max-w-[1500px] items-center justify-between px-4 sm:px-6 lg:px-8">

            <div class="flex min-w-0 items-center gap-3">

                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate
                    class="group flex size-10 shrink-0 items-center justify-center rounded-2xl border border-zinc-200 bg-white text-zinc-500 shadow-sm transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:border-violet-800 dark:hover:bg-violet-950/30 dark:hover:text-violet-300"
                >
                    <flux:icon.arrow-left class="size-4 transition group-hover:-translate-x-0.5" />
                </a>

                <div class="hidden h-7 w-px bg-zinc-200 dark:bg-zinc-800 sm:block"></div>

                <div class="min-w-0">

                    <div class="flex items-center gap-2">

                        <h1 class="truncate text-sm font-semibold tracking-tight text-zinc-950 dark:text-white">
                            {{ $ticket->subject }}
                        </h1>

                        <span class="hidden rounded-lg bg-violet-50 px-2 py-1 font-mono text-[9px] font-bold text-violet-600 sm:inline dark:bg-violet-950/40 dark:text-violet-300">
                            {{ $ticket->ticket_number }}
                        </span>

                    </div>

                    <div class="mt-0.5 text-[10px] text-zinc-400">
                        {{ $ticket->name }}
                    </div>

                </div>

            </div>


            {{-- Status --}}
            <div class="flex items-center gap-2">

                <div class="hidden items-center gap-2 rounded-full border border-violet-100 bg-violet-50 px-3 py-2 dark:border-violet-900/40 dark:bg-violet-950/30 sm:flex">

                    <span
                        class="size-1.5 rounded-full
                        {{ match($ticket->status) {
                            'open' => 'bg-emerald-500',
                            'pending' => 'bg-amber-500',
                            'resolved' => 'bg-blue-500',
                            'closed' => 'bg-zinc-400',
                            default => 'bg-zinc-400',
                        } }}"
                    ></span>

                    <span class="text-[10px] font-bold text-violet-700 dark:text-violet-300">
                        {{ ucfirst($ticket->status) }}
                    </span>

                </div>


                <div class="relative">

                    <select
                        wire:model.change="status"
                        wire:loading.attr="disabled"
                        wire:target="status"
                        class="appearance-none rounded-xl border border-zinc-200 bg-white py-2.5 pl-3 pr-8 text-xs font-semibold text-zinc-800 shadow-sm outline-none transition hover:border-violet-300 focus:border-violet-400 focus:ring-4 focus:ring-violet-100 disabled:opacity-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200 dark:focus:border-violet-700 dark:focus:ring-violet-950"
                    >
                        <option value="open">Open</option>
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>

                    <flux:icon.chevron-down
                        class="pointer-events-none absolute right-2.5 top-1/2 size-3.5 -translate-y-1/2 text-zinc-400"
                    />

                </div>

            </div>

        </div>

    </header>


    {{-- ================================================================
        PAGE
    ================================================================= --}}
    <main class="mx-auto max-w-[1500px] px-4 py-6 sm:px-6 lg:px-8">

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_310px]">


            {{-- ========================================================
                CONVERSATION
            ========================================================= --}}
            <section class="min-w-0">


                {{-- Conversation intro --}}
                <div class="mb-5 flex items-end justify-between">

                    <div>

                        <div class="mb-2 flex items-center gap-2">

                            <span class="flex size-6 items-center justify-center rounded-lg bg-violet-600 text-white shadow-lg shadow-violet-500/20">
                                <flux:icon.chat-bubble-left class="size-3" />
                            </span>

                            <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-violet-500">
                                Conversation
                            </span>

                        </div>

                        <h2 class="text-2xl font-bold tracking-[-0.04em] text-zinc-950 dark:text-white">
                            {{ $ticket->messages->count() }}
                            <span class="font-normal text-zinc-400">
                                {{ Str::plural('message', $ticket->messages->count()) }}
                            </span>
                        </h2>

                    </div>

                    <div class="hidden text-right sm:block">

                        <div class="text-[9px] font-bold uppercase tracking-widest text-zinc-400">
                            Last activity
                        </div>

                        <div class="mt-1 text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                            {{ $ticket->last_activity_at?->diffForHumans() ?? '—' }}
                        </div>

                    </div>

                </div>


                {{-- ====================================================
                    MESSAGE PANEL
                ===================================================== --}}
                <div class="relative overflow-hidden rounded-[2rem] border border-violet-100 bg-white shadow-[0_20px_70px_-35px_rgba(109,40,217,.25)] dark:border-violet-900/20 dark:bg-[#100c18]">

                    {{-- Purple ambient glow --}}
                    <div class="pointer-events-none absolute -right-32 -top-32 size-96 rounded-full bg-violet-500/10 blur-3xl"></div>

                    <div class="pointer-events-none absolute -bottom-40 -left-40 size-96 rounded-full bg-fuchsia-500/[0.06] blur-3xl"></div>


                    {{-- Message stream --}}
                    <div class="relative">

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


                            {{-- Date --}}
                            @if(
                                $loop->first ||
                                !$message->created_at->isSameDay(
                                    $ticket->messages[$loop->index - 1]->created_at
                                )
                            )

                                <div class="flex items-center gap-4 px-5 py-7 sm:px-8">

                                    <div class="h-px flex-1 bg-zinc-100 dark:bg-zinc-800"></div>

                                    <span class="rounded-full border border-violet-100 bg-violet-50 px-3 py-1 text-[8px] font-bold uppercase tracking-[0.18em] text-violet-500 dark:border-violet-900/30 dark:bg-violet-950/30 dark:text-violet-300">
                                        {{ $message->created_at->format('M j, Y') }}
                                    </span>

                                    <div class="h-px flex-1 bg-zinc-100 dark:bg-zinc-800"></div>

                                </div>

                            @endif


                            {{-- =================================================
                                MESSAGE
                            ================================================== --}}
                            <div
                                class="group relative px-5 py-5 sm:px-8 sm:py-6"
                            >

                                <div
                                    class="flex gap-3.5
                                    {{ $isCustomer ? '' : 'flex-row-reverse' }}"
                                >

                                    {{-- Avatar --}}
                                    <div
                                        class="relative flex size-9 shrink-0 items-center justify-center rounded-xl text-[10px] font-bold shadow-sm
                                        {{ $isCustomer
                                            ? 'bg-violet-100 text-violet-700 dark:bg-violet-950 dark:text-violet-300'
                                            : 'bg-gradient-to-br from-violet-600 to-fuchsia-600 text-white shadow-violet-500/20'
                                        }}"
                                    >
                                        {{ $initial }}

                                        @if(!$isCustomer)
                                            <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full border-2 border-white bg-emerald-500 dark:border-[#100c18]"></span>
                                        @endif
                                    </div>


                                    {{-- Body --}}
                                    <div class="min-w-0 max-w-[88%] sm:max-w-[80%]">

                                        {{-- Meta --}}
                                        <div
                                            class="mb-2 flex items-center gap-2
                                            {{ $isCustomer ? '' : 'justify-end' }}"
                                        >

                                            <span class="text-[11px] font-bold text-zinc-800 dark:text-zinc-200">
                                                {{ $message->author_name }}
                                            </span>

                                            @if(!$isCustomer)
                                                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[7px] font-bold uppercase tracking-widest text-violet-600 dark:bg-violet-950/60 dark:text-violet-300">
                                                    Team
                                                </span>
                                            @endif

                                            <span class="text-[9px] text-zinc-400">
                                                {{ $message->created_at->format('g:i A') }}
                                            </span>

                                        </div>


                                        {{-- Text bubble --}}
                                        @if(filled($message->message))

                                            <div
                                                class="
                                                {{ $isCustomer
                                                    ? 'rounded-2xl rounded-tl-md border border-zinc-200 bg-zinc-50 text-zinc-800 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200'
                                                    : 'rounded-2xl rounded-tr-md bg-gradient-to-br from-violet-600 to-violet-700 text-white shadow-lg shadow-violet-600/10'
                                                }}
                                                px-4 py-3.5 text-sm leading-6"
                                            >
                                                <div class="whitespace-pre-wrap break-words">
                                                    {{ $message->message }}
                                                </div>
                                            </div>

                                        @endif


                                        {{-- =================================================
                                            INLINE ATTACHMENTS
                                        ================================================== --}}
                                        @if($message->attachments->isNotEmpty())

                                            <div class="mt-3 space-y-2.5">

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


                                                    {{-- =============================================
                                                        REAL IMAGE
                                                    ============================================== --}}
                                                    @if($isImage)

                                                        <div
                                                            class="group/image relative overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100 shadow-md dark:border-zinc-700 dark:bg-zinc-900"
                                                            style="max-width: 680px;"
                                                        >

                                                            {{-- IMAGE --}}
                                                            <button
                                                                type="button"
                                                                x-on:click="openLightbox(
                                                                    @js($previewUrl),
                                                                    @js($attachment->file_name)
                                                                )"
                                                                class="relative block w-full cursor-zoom-in"
                                                            >

                                                                <img
                                                                    src="{{ $previewUrl }}"
                                                                    alt="{{ $attachment->file_name }}"
                                                                    loading="lazy"
                                                                    class="block max-h-[600px] w-full object-cover"
                                                                />

                                                                {{-- Hover controls --}}
                                                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-violet-950/0 transition duration-300 group-hover/image:bg-violet-950/25">

                                                                    <span class="flex size-12 scale-90 items-center justify-center rounded-2xl bg-white/95 text-violet-700 opacity-0 shadow-2xl backdrop-blur-xl transition duration-300 group-hover/image:scale-100 group-hover/image:opacity-100">
                                                                        <flux:icon.arrows-pointing-out class="size-5" />
                                                                    </span>

                                                                </div>

                                                            </button>


                                                            {{-- Image footer --}}
                                                            <div class="flex items-center justify-between border-t border-black/5 bg-white/95 px-3 py-2.5 backdrop-blur dark:border-white/5 dark:bg-zinc-950/95">

                                                                <div class="flex min-w-0 items-center gap-2">

                                                                    <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-500 dark:bg-violet-950/40 dark:text-violet-300">
                                                                        <flux:icon.photo class="size-3.5" />
                                                                    </span>

                                                                    <div class="min-w-0">

                                                                        <div class="max-w-[250px] truncate text-[10px] font-semibold text-zinc-700 dark:text-zinc-300">
                                                                            {{ $attachment->file_name }}
                                                                        </div>

                                                                        <div class="text-[8px] text-zinc-400">
                                                                            {{ number_format($attachment->file_size / 1024, 1) }} KB
                                                                        </div>

                                                                    </div>

                                                                </div>


                                                                <a
                                                                    href="{{ $downloadUrl }}"
                                                                    class="flex size-8 shrink-0 items-center justify-center rounded-xl text-zinc-400 transition hover:bg-violet-50 hover:text-violet-600 dark:hover:bg-violet-950/40 dark:hover:text-violet-300"
                                                                    title="Download"
                                                                >
                                                                    <flux:icon.arrow-down-tray class="size-3.5" />
                                                                </a>

                                                            </div>

                                                        </div>


                                                    {{-- =============================================
                                                        OTHER FILE
                                                    ============================================== --}}
                                                    @else

                                                        <a
                                                            href="{{ $downloadUrl }}"
                                                            class="group/file flex max-w-lg items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 p-3 transition hover:-translate-y-0.5 hover:border-violet-200 hover:bg-white hover:shadow-lg hover:shadow-violet-500/10 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-violet-800 dark:hover:bg-zinc-800"
                                                        >

                                                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm dark:bg-zinc-800">

                                                                @if($isPdf)
                                                                    <flux:icon.document-text class="size-5 text-red-500" />
                                                                @else
                                                                    <flux:icon.paper-clip class="size-5 text-violet-500" />
                                                                @endif

                                                            </div>

                                                            <div class="min-w-0 flex-1">

                                                                <div class="truncate text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                                                    {{ $attachment->file_name }}
                                                                </div>

                                                                <div class="mt-1 text-[9px] text-zinc-400">
                                                                    {{ number_format($attachment->file_size / 1024, 1) }} KB
                                                                    <span class="mx-1">·</span>
                                                                    {{ strtoupper(pathinfo($attachment->file_name, PATHINFO_EXTENSION)) }}
                                                                </div>

                                                            </div>

                                                            <div class="flex size-8 items-center justify-center rounded-xl bg-violet-100 text-violet-600 transition group-hover/file:bg-violet-600 group-hover/file:text-white dark:bg-violet-950/50 dark:text-violet-300">
                                                                <flux:icon.arrow-down-tray class="size-3.5" />
                                                            </div>

                                                        </a>

                                                    @endif

                                                @endforeach

                                            </div>

                                        @endif


                                        {{-- Time --}}
                                        <div
                                            class="mt-2 text-[9px] text-zinc-400
                                            {{ $isCustomer ? '' : 'text-right' }}"
                                        >
                                            {{ $message->created_at->diffForHumans() }}
                                        </div>

                                    </div>

                                </div>

                            </div>

                        @empty

                            <div class="px-6 py-24 text-center">

                                <div class="mx-auto mb-5 flex size-16 items-center justify-center rounded-3xl bg-violet-100 dark:bg-violet-950/40">

                                    <flux:icon.chat-bubble-left-right class="size-7 text-violet-500" />

                                </div>

                                <h3 class="text-sm font-bold text-zinc-900 dark:text-white">
                                    Nothing here yet
                                </h3>

                                <p class="mx-auto mt-1 max-w-xs text-xs leading-5 text-zinc-500">
                                    Send a reply to start the conversation.
                                </p>

                            </div>

                        @endforelse

                    </div>


                    {{-- ====================================================
                        COMPOSER
                    ===================================================== --}}
                    <div class="border-t border-violet-100 bg-gradient-to-b from-violet-50/50 to-white p-3 dark:border-violet-900/20 dark:from-violet-950/10 dark:to-[#0d0a13] sm:p-5">

                        <div class="overflow-hidden rounded-[1.5rem] border border-violet-100 bg-white shadow-[0_15px_50px_-25px_rgba(109,40,217,.35)] focus-within:border-violet-300 focus-within:ring-4 focus-within:ring-violet-100 dark:border-zinc-800 dark:bg-zinc-900 dark:focus-within:border-violet-800 dark:focus-within:ring-violet-950/30">

                            <textarea
                                wire:model="newMessage"
                                rows="4"
                                placeholder="Write a reply to {{ $ticket->name }}..."
                                class="block w-full resize-none border-0 bg-transparent px-5 pt-5 text-sm leading-6 text-zinc-900 outline-none ring-0 placeholder:text-zinc-400 focus:border-0 focus:ring-0 dark:text-white"
                            ></textarea>


                            {{-- Selected files --}}
                            @if(count($attachments))

                                <div class="border-t border-violet-100 px-4 py-4 dark:border-zinc-800">

                                    <div class="mb-3 flex items-center justify-between">

                                        <span class="text-[9px] font-bold uppercase tracking-[0.18em] text-violet-500">
                                            Ready to send
                                        </span>

                                        <span class="text-[9px] text-zinc-400">
                                            {{ count($attachments) }}
                                            {{ Str::plural('file', count($attachments)) }}
                                        </span>

                                    </div>


                                    <div class="grid gap-2 sm:grid-cols-3">

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

                                                <div class="group relative overflow-hidden rounded-xl border border-violet-100 bg-violet-50/40 dark:border-violet-900/30 dark:bg-violet-950/20">

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

                                                            <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white dark:bg-zinc-900">

                                                                @if($uploadMime === 'application/pdf')
                                                                    <flux:icon.document-text class="size-4 text-red-500" />
                                                                @else
                                                                    <flux:icon.paper-clip class="size-4 text-violet-500" />
                                                                @endif

                                                            </div>

                                                            <div class="min-w-0">

                                                                <div class="truncate text-[10px] font-semibold text-zinc-700 dark:text-zinc-300">
                                                                    {{ $attachment->getClientOriginalName() }}
                                                                </div>

                                                                <div class="mt-0.5 text-[9px] text-zinc-400">
                                                                    {{ number_format($attachment->getSize() / 1024, 1) }} KB
                                                                </div>

                                                            </div>

                                                        </div>

                                                    @endif


                                                    <button
                                                        type="button"
                                                        wire:click="removeAttachment({{ $index }})"
                                                        class="absolute right-2 top-2 flex size-7 items-center justify-center rounded-lg bg-black/70 text-white opacity-0 shadow-lg backdrop-blur transition group-hover:opacity-100 hover:bg-red-500"
                                                    >
                                                        <flux:icon.x-mark class="size-3" />
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
                                class="border-t border-violet-100 px-5 py-3 dark:border-zinc-800"
                            >
                                <div class="flex items-center gap-2 text-[10px] font-medium text-violet-500">
                                    <flux:icon.arrow-path class="size-3 animate-spin" />
                                    Uploading attachments…
                                </div>
                            </div>


                            {{-- Footer --}}
                            <div class="flex items-center justify-between gap-3 border-t border-violet-100 px-3 py-3 dark:border-zinc-800">

                                <div class="flex items-center gap-1">

                                    <label class="group flex cursor-pointer items-center gap-2 rounded-xl px-3 py-2 text-[10px] font-bold text-zinc-500 transition hover:bg-violet-50 hover:text-violet-600 dark:hover:bg-violet-950/30 dark:hover:text-violet-300">

                                        <span class="flex size-7 items-center justify-center rounded-lg bg-violet-50 text-violet-500 transition group-hover:bg-violet-100 dark:bg-violet-950/40 dark:text-violet-300">
                                            <flux:icon.paper-clip class="size-3.5" />
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

                                    <span class="hidden text-[9px] text-zinc-400 sm:inline">
                                        Images & files · 10 MB max
                                    </span>

                                </div>


                                <flux:button
                                    wire:click="sendMessage"
                                    wire:loading.attr="disabled"
                                    wire:target="sendMessage"
                                    variant="primary"
                                    class="rounded-xl bg-violet-600 px-4 shadow-lg shadow-violet-500/20 hover:bg-violet-700"
                                >

                                    <flux:icon.paper-airplane
                                        wire:loading.remove
                                        wire:target="sendMessage"
                                        class="size-3.5"
                                    />

                                    <flux:icon.arrow-path
                                        wire:loading
                                        wire:target="sendMessage"
                                        class="size-3.5 animate-spin"
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
                            <div class="mt-2 px-2 text-[10px] font-medium text-red-600">
                                {{ $message }}
                            </div>
                        @enderror

                        @error('attachments.*')
                            <div class="mt-2 px-2 text-[10px] font-medium text-red-600">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </section>


            {{-- ========================================================
                SIDEBAR
            ========================================================= --}}
            <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">


                {{-- Customer --}}
                <div class="overflow-hidden rounded-[1.75rem] border border-violet-100 bg-white shadow-sm dark:border-violet-900/20 dark:bg-[#100c18]">

                    <div class="relative overflow-hidden bg-gradient-to-br from-violet-600 via-violet-700 to-fuchsia-700 px-5 py-6 text-white">

                        <div class="absolute -right-10 -top-10 size-32 rounded-full bg-white/10 blur-2xl"></div>

                        <div class="relative">

                            <div class="mb-4 text-[8px] font-bold uppercase tracking-[0.2em] text-violet-200">
                                Customer
                            </div>

                            <div class="flex items-center gap-3">

                                <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-sm font-bold ring-1 ring-white/20 backdrop-blur">
                                    {{ strtoupper(substr($ticket->name, 0, 1)) }}
                                </div>

                                <div class="min-w-0">

                                    <div class="truncate text-sm font-bold">
                                        {{ $ticket->name }}
                                    </div>

                                    <div class="mt-0.5 truncate text-[10px] text-violet-200">
                                        {{ $ticket->email }}
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="p-4">

                        <a
                            href="mailto:{{ $ticket->email }}"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-violet-50 py-2.5 text-[10px] font-bold text-violet-700 transition hover:bg-violet-100 dark:bg-violet-950/30 dark:text-violet-300 dark:hover:bg-violet-950/50"
                        >
                            <flux:icon.envelope class="size-3.5" />
                            Email customer
                        </a>

                    </div>

                </div>


                {{-- Ticket details --}}
                <div class="rounded-[1.75rem] border border-violet-100 bg-white p-5 shadow-sm dark:border-violet-900/20 dark:bg-[#100c18]">

                    <div class="mb-5 flex items-center justify-between">

                        <div>
                            <div class="text-sm font-bold text-zinc-900 dark:text-white">
                                Ticket details
                            </div>

                            <div class="mt-0.5 text-[9px] text-zinc-400">
                                Request information
                            </div>
                        </div>

                        <div class="flex size-8 items-center justify-center rounded-xl bg-violet-50 text-violet-500 dark:bg-violet-950/40 dark:text-violet-300">
                            <flux:icon.ticket class="size-4" />
                        </div>

                    </div>


                    <div class="space-y-4">

                        <div class="flex items-center justify-between">

                            <span class="text-[10px] text-zinc-400">
                                Status
                            </span>

                            <span class="flex items-center gap-2 text-[10px] font-bold text-zinc-700 dark:text-zinc-200">

                                <span
                                    class="size-1.5 rounded-full
                                    {{ match($ticket->status) {
                                        'open' => 'bg-emerald-500',
                                        'pending' => 'bg-amber-500',
                                        'resolved' => 'bg-blue-500',
                                        'closed' => 'bg-zinc-400',
                                        default => 'bg-zinc-400',
                                    } }}"
                                ></span>

                                {{ ucfirst($ticket->status) }}

                            </span>

                        </div>


                        <div class="h-px bg-zinc-100 dark:bg-zinc-800"></div>


                        <div class="flex items-center justify-between">

                            <span class="text-[10px] text-zinc-400">
                                Priority
                            </span>

                            <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[9px] font-bold text-violet-600 dark:bg-violet-950/40 dark:text-violet-300">
                                {{ ucfirst($ticket->priority) }}
                            </span>

                        </div>


                        <div class="h-px bg-zinc-100 dark:bg-zinc-800"></div>


                        <div>

                            <div class="text-[10px] text-zinc-400">
                                Ticket number
                            </div>

                            <div class="mt-1.5 font-mono text-[10px] font-bold text-zinc-800 dark:text-zinc-200">
                                {{ $ticket->ticket_number }}
                            </div>

                        </div>


                        <div class="h-px bg-zinc-100 dark:bg-zinc-800"></div>


                        <div>

                            <div class="text-[10px] text-zinc-400">
                                Created
                            </div>

                            <div class="mt-1.5 text-[10px] font-bold text-zinc-700 dark:text-zinc-200">
                                {{ $ticket->created_at->format('M j, Y · g:i A') }}
                            </div>

                        </div>


                        <div>

                            <div class="text-[10px] text-zinc-400">
                                Last activity
                            </div>

                            <div class="mt-1.5 text-[10px] font-bold text-zinc-700 dark:text-zinc-200">
                                {{ $ticket->last_activity_at?->diffForHumans() ?? '—' }}
                            </div>

                        </div>

                    </div>

                </div>


                {{-- Activity --}}
                <div class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-violet-700 via-violet-800 to-fuchsia-800 p-5 text-white shadow-xl shadow-violet-900/20">

                    <div class="absolute -right-16 -top-16 size-44 rounded-full bg-white/10 blur-3xl"></div>

                    <div class="relative">

                        <div class="mb-5 flex items-center justify-between">

                            <span class="text-[8px] font-bold uppercase tracking-[0.2em] text-violet-200">
                                Activity
                            </span>

                            <span class="flex size-8 items-center justify-center rounded-xl bg-white/10">
                                <flux:icon.bolt class="size-3.5" />
                            </span>

                        </div>

                        <div class="text-4xl font-bold tracking-tight">
                            {{ $ticket->messages->count() }}
                        </div>

                        <div class="mt-1 text-[10px] text-violet-200">
                            {{ Str::plural('message', $ticket->messages->count()) }}
                            exchanged
                        </div>

                    </div>

                </div>

            </aside>

        </div>

    </main>

</div>