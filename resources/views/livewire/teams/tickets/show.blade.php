<div
    x-data="{
        lightbox: false,
        lightboxSrc: '',
        lightboxName: '',

        highlightMessageId: @js(request()->integer('message') ?: null),

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
        },

        getScrollableParents(element) {
            const parents = []
            let parent = element?.parentElement

            while (parent) {
                const style = window.getComputedStyle(parent)
                const overflowY = style.overflowY

                const isScrollable =
                    (
                        overflowY === 'auto' ||
                        overflowY === 'scroll' ||
                        overflowY === 'overlay'
                    ) &&
                    parent.scrollHeight > parent.clientHeight

                if (isScrollable) {
                    parents.push(parent)
                }

                parent = parent.parentElement
            }

            return parents
        },

        scrollContainerToBottom(container) {
            container.scrollTo({
                top: container.scrollHeight,
                behavior: 'smooth',
            })
        },

        scrollToBottom() {
            const containers = this.getScrollableParents(this.$root)

            if (containers.length) {
                const container = containers[0]

                this.scrollContainerToBottom(container)

                requestAnimationFrame(() => {
                    this.scrollContainerToBottom(container)
                })

                setTimeout(() => {
                    this.scrollContainerToBottom(container)
                }, 300)

                return
            }

            const scrollingElement =
                document.scrollingElement || document.documentElement

            const scrollToDocumentBottom = () => {
                scrollingElement.scrollTo({
                    top: scrollingElement.scrollHeight,
                    behavior: 'smooth',
                })
            }

            scrollToDocumentBottom()

            requestAnimationFrame(() => {
                scrollToDocumentBottom()
            })

            setTimeout(() => {
                scrollToDocumentBottom()
            }, 300)
        },

        scrollToMessage(element) {
            if (!element) {
                return
            }

            element.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest',
            })

            element.classList.remove('message-highlight')

            void element.offsetWidth

            element.classList.add('message-highlight')

            setTimeout(() => {
                element.classList.remove('message-highlight')
            }, 5000)
        },

        navigateToMessage() {
            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        setTimeout(() => {
                            if (this.highlightMessageId) {
                                const element = document.getElementById(
                                    `ticket-message-${this.highlightMessageId}`
                                )

                                if (!element) {
                                    return
                                }

                                this.scrollToMessage(element)

                                return
                            }

                            this.scrollToBottom()
                        }, 150)
                    })
                })
            })
        },
    }"
    x-init="navigateToMessage()"
    x-on:keydown.escape.window="closeLightbox()"
    class="min-h-screen w-full overflow-x-hidden bg-[#f7f5fa] text-zinc-900 antialiased dark:bg-[#0b0a0f] dark:text-white"
>
    @php
        $user = auth()->user();
        $team = $user?->currentTeam;
        $invoice = $ticket->invoice;

        $canCharge = false;

        if ($team) {
            $canCharge =
                $team->roleFor($user)?->hasPermission(
                    \App\Enums\TeamPermission::ChargeCustomer
                ) ?? false;

            if ($team->owner()?->id === $user?->id) {
                $canCharge = true;
            }
        }

        $showChargeSection =
            in_array($status, ['resolved', 'closed'], true)
            && $canCharge;

        $isPaid =
            $invoice?->isPaid() ?? false;

        $transaction =
            $invoice?->successfulTransaction();

        /*
         * Your invoice signature is stored as:
         *
         * data:image/png;base64,...
         */
        $signatureData = trim(
            (string) ($invoice?->signature_data ?? '')
        );

        $hasSignature =
            $signatureData !== ''
            && preg_match(
                '/^data:image\/(?:png|jpe?g|webp);base64,/i',
                $signatureData
            );

        $messageCount =
            $ticket->messages->count();

        $statusDot = match ($ticket->status) {
            'open' => 'bg-emerald-500',
            'pending' => 'bg-amber-500',
            'resolved' => 'bg-blue-500',
            'closed' => 'bg-zinc-400',
            default => 'bg-zinc-400',
        };

        $statusBadge = match ($ticket->status) {
            'open' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20',
            'pending' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20',
            'resolved' => 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/20',
            'closed' => 'bg-zinc-100 text-zinc-600 ring-zinc-200 dark:bg-white/5 dark:text-zinc-300 dark:ring-white/10',
            default => 'bg-zinc-100 text-zinc-600 ring-zinc-200 dark:bg-white/5 dark:text-zinc-300 dark:ring-white/10',
        };
    @endphp

    <style>
        @keyframes ticket-message-highlight {
            0% {
                box-shadow:
                    inset 0 0 0 2px rgba(139, 120, 185, 0),
                    0 0 0 0 rgba(139, 120, 185, 0);
            }

            15% {
                box-shadow:
                    inset 0 0 0 2px rgba(139, 120, 185, 0.7),
                    0 0 0 7px rgba(139, 120, 185, 0.16);
            }

            45% {
                box-shadow:
                    inset 0 0 0 2px rgba(139, 120, 185, 0.55),
                    0 0 0 6px rgba(139, 120, 185, 0.12);
            }

            75% {
                box-shadow:
                    inset 0 0 0 2px rgba(139, 120, 185, 0.22),
                    0 0 0 4px rgba(139, 120, 185, 0.06);
            }

            100% {
                box-shadow:
                    inset 0 0 0 2px rgba(139, 120, 185, 0),
                    0 0 0 0 rgba(139, 120, 185, 0);
            }
        }

        .message-highlight {
            animation: ticket-message-highlight 5s ease-out both;
            border-radius: 24px;
        }

        [x-cloak] {
            display: none !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .message-highlight {
                animation: none;
                box-shadow:
                    inset 0 0 0 2px rgba(139, 120, 185, 0.55),
                    0 0 0 5px rgba(139, 120, 185, 0.12);
            }
        }
    </style>

    {{-- ================================================================
        LIGHTBOX
    ================================================================= --}}
    <template x-teleport="body">
        <div
            x-show="lightbox"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/90 p-3 backdrop-blur-xl sm:p-6"
            role="dialog"
            aria-modal="true"
        >
            <button
                type="button"
                x-on:click="closeLightbox()"
                class="absolute right-3 top-3 z-30 flex size-11 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white backdrop-blur-xl transition hover:bg-white/20 sm:right-5 sm:top-5"
                aria-label="Close image viewer"
            >
                <flux:icon.x-mark class="size-5" />
            </button>

            <button
                type="button"
                x-on:click="closeLightbox()"
                class="absolute inset-0 cursor-default"
                aria-label="Close image"
            ></button>

            <div class="relative z-10 flex max-h-[92vh] max-w-[96vw] flex-col items-center">
                <img
                    :src="lightboxSrc"
                    :alt="lightboxName"
                    x-on:click.stop
                    class="max-h-[82vh] max-w-[96vw] rounded-2xl bg-white object-contain shadow-2xl sm:max-h-[86vh] sm:rounded-3xl"
                />

                <div class="mt-3 max-w-[90vw] rounded-full border border-white/10 bg-white/10 px-4 py-2 backdrop-blur-xl">
                    <span
                        x-text="lightboxName"
                        class="block truncate text-xs font-medium text-white/80 sm:text-sm"
                    ></span>
                </div>
            </div>
        </div>
    </template>

    {{-- ================================================================
        TOP HEADER
    ================================================================= --}}
    <header
        class="sticky top-0 z-50 border-b border-zinc-200/80 bg-[#f7f5fa]/92 backdrop-blur-2xl dark:border-white/[0.06] dark:bg-[#0b0a0f]/92"
    >
        <div class="mx-auto flex min-h-[72px] w-full max-w-[1840px] items-center justify-between gap-3 px-3 sm:px-5 lg:px-7 2xl:px-9">
            <div class="flex min-w-0 items-center gap-3">
                <a
                    href="{{ auth()->check() && auth()->user()->currentTeam
                        ? route('dashboard', ['current_team' => auth()->user()->currentTeam])
                        : route('onboarding') }}"
                    wire:navigate
                    class="group flex size-10 shrink-0 items-center justify-center rounded-2xl border border-zinc-200 bg-white text-zinc-500 shadow-sm transition hover:-translate-x-0.5 hover:border-violet-200 hover:bg-violet-50 hover:text-violet-600 dark:border-white/10 dark:bg-white/5 dark:text-white/50 dark:hover:border-white/15 dark:hover:bg-white/10 dark:hover:text-white"
                    aria-label="Back to dashboard"
                >
                    <flux:icon.arrow-left class="size-4 transition-transform group-hover:-translate-x-0.5" />
                </a>

                <div class="hidden h-8 w-px bg-zinc-200 sm:block dark:bg-white/10"></div>

                <div class="min-w-0">
                    <div class="flex min-w-0 items-center gap-2">
                        <h1 class="min-w-0 truncate text-[15px] font-semibold tracking-tight text-zinc-900 sm:text-base lg:text-lg dark:text-white">
                            {{ $ticket->subject }}
                        </h1>

                        <span class="hidden rounded-lg bg-violet-50 px-2 py-1 font-mono text-[10px] font-semibold text-violet-600 ring-1 ring-inset ring-violet-100 md:inline-flex dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-500/20">
                            {{ $ticket->ticket_number }}
                        </span>
                    </div>

                    <div class="mt-0.5 truncate text-xs text-zinc-500 sm:text-sm dark:text-zinc-400">
                        {{ $ticket->name }}
                    </div>
                </div>
            </div>

            <div class="shrink-0">
                <span
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold ring-1 ring-inset sm:px-3.5 sm:py-2 sm:text-sm {{ $statusBadge }}"
                >
                    <span class="size-2 rounded-full {{ $statusDot }}"></span>
                    <span>{{ ucfirst($ticket->status) }}</span>
                </span>
            </div>
        </div>
    </header>

    {{-- ================================================================
        PAGE
    ================================================================= --}}
    <main class="mx-auto w-full max-w-[1840px] px-3 py-5 sm:px-5 sm:py-7 lg:px-7 lg:py-8 2xl:px-9">
        <div class="grid min-w-0 items-start gap-6 2xl:grid-cols-[minmax(0,1fr)_380px] 2xl:gap-8">

            {{-- ============================================================
                MAIN CONTENT
            ============================================================= --}}
            <div class="min-w-0 space-y-6">

                {{-- ========================================================
                    CONVERSATION HEADER
                ========================================================= --}}
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div class="min-w-0">
                        <div class="mb-2.5 flex items-center gap-2">
                            <span class="flex size-8 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">
                                <flux:icon.chat-bubble-left class="size-4" />
                            </span>

                            <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-violet-600 dark:text-violet-300">
                                Conversation
                            </span>
                        </div>

                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                            <h2 class="text-3xl font-bold tracking-[-0.045em] text-zinc-950 sm:text-4xl dark:text-white">
                                {{ $messageCount }}
                            </h2>

                            <span class="text-base text-zinc-400 sm:text-lg">
                                {{ Str::plural('message', $messageCount) }}
                            </span>
                        </div>
                    </div>

                    <div class="shrink-0 md:text-right">
                        <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-400">
                            Last activity
                        </div>

                        <div class="mt-1 text-sm font-medium text-zinc-600 dark:text-zinc-300">
                            {{ $ticket->last_activity_at?->diffForHumans() ?? '—' }}
                        </div>
                    </div>
                </div>

                {{-- ========================================================
                    CONVERSATION
                ========================================================= --}}
                <section
                    class="overflow-hidden rounded-[28px] border border-zinc-200/90 bg-white shadow-[0_24px_80px_-50px_rgba(35,25,55,.35)] dark:border-white/[0.07] dark:bg-[#141218]"
                >
                    <div class="divide-y divide-zinc-100 dark:divide-white/[0.05]">

                        @php
                            $previousMessageDate = null;
                        @endphp

                        @forelse ($ticket->messages as $message)
                            @php
                                $isCustomer =
                                    in_array(
                                        $message->author_type,
                                        ['guest', 'customer'],
                                        true
                                    );

                                $authorName =
                                    trim(
                                        (string) ($message->author_name ?? 'Unknown')
                                    );

                                $initial =
                                    strtoupper(
                                        substr(
                                            $authorName !== ''
                                                ? $authorName
                                                : 'U',
                                            0,
                                            1
                                        )
                                    );

                                $messageDate =
                                    $message->created_at?->format('Y-m-d');

                                $showDateDivider =
                                    $previousMessageDate !== $messageDate;

                                $previousMessageDate =
                                    $messageDate;

                                $isHighlighted =
                                    request()->integer('message')
                                    === (int) $message->id;
                            @endphp

                            {{-- Date divider --}}
                            @if ($showDateDivider)
                                <div class="flex items-center gap-3 px-4 py-5 sm:px-7 sm:py-6">
                                    <div class="h-px flex-1 bg-zinc-100 dark:bg-white/[0.05]"></div>

                                    <span class="rounded-full border border-zinc-200 bg-zinc-50 px-3 py-1.5 text-[10px] font-semibold text-zinc-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-zinc-400">
                                        {{ $message->created_at?->format('M j, Y') ?? '—' }}
                                    </span>

                                    <div class="h-px flex-1 bg-zinc-100 dark:bg-white/[0.05]"></div>
                                </div>
                            @endif

                            {{-- Message --}}
                            <article
                                id="ticket-message-{{ $message->id }}"
                                wire:key="ticket-message-{{ $message->id }}"
                                class="scroll-mt-28 px-4 py-6 sm:px-7 sm:py-7 {{ $isHighlighted ? 'message-highlight' : '' }}"
                            >
                                <div class="flex items-start gap-3 sm:gap-4 {{ $isCustomer ? '' : 'justify-end' }}">

                                    @if ($isCustomer)
                                        {{-- Customer avatar --}}
                                        <div class="relative flex size-10 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-sm font-bold text-violet-700 dark:bg-violet-500/10 dark:text-violet-200 sm:size-11">
                                            {{ $initial }}
                                        </div>
                                    @endif

                                    <div class="min-w-0 max-w-[calc(100%-52px)] sm:max-w-[78%] lg:max-w-[72%]">
                                        {{-- Meta --}}
                                        <div class="mb-2 flex flex-wrap items-center gap-2 {{ $isCustomer ? '' : 'justify-end' }}">
                                            <span class="text-sm font-semibold text-zinc-800 dark:text-white">
                                                {{ $authorName }}
                                            </span>

                                            @if (! $isCustomer)
                                                <span class="rounded-full bg-violet-50 px-2 py-0.5 text-[9px] font-bold uppercase tracking-[0.12em] text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">
                                                    Team
                                                </span>
                                            @endif

                                            <span class="text-xs text-zinc-400">
                                                {{ $message->created_at?->format('g:i A') ?? '—' }}
                                            </span>
                                        </div>

                                        {{-- Text --}}
                                        @if (filled($message->message))
                                            <div
                                                class="w-fit max-w-full rounded-[22px] px-4 py-3.5 text-[14px] leading-7 sm:px-5 sm:py-4 sm:text-[15px]
                                                {{ $isCustomer
                                                    ? 'rounded-tl-md border border-zinc-200 bg-zinc-50 text-zinc-700 dark:border-white/[0.07] dark:bg-white/[0.04] dark:text-zinc-200'
                                                    : 'rounded-tr-md bg-[#8069b0] text-white shadow-[0_14px_35px_-22px_rgba(106,82,155,.9)]' }}"
                                            >
                                                <div class="whitespace-pre-wrap break-words">
                                                    {{ $message->message }}
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Attachments --}}
                                        @if ($message->attachments->isNotEmpty())
                                            <div class="mt-3 space-y-3">
                                                @foreach ($message->attachments as $attachment)
                                                    @php
                                                        $mime = strtolower(
                                                            (string) ($attachment->mime_type ?? '')
                                                        );

                                                        $isImage =
                                                            str_starts_with(
                                                                $mime,
                                                                'image/'
                                                            );

                                                        $isPdf =
                                                            $mime === 'application/pdf';

                                                        $previewUrl = route(
                                                            'ticket.attachments.preview',
                                                            [
                                                                'current_team' =>
                                                                    request()->route('current_team'),
                                                                'attachment' =>
                                                                    $attachment->id,
                                                            ]
                                                        );

                                                        $downloadUrl = route(
                                                            'ticket.attachments.download',
                                                            [
                                                                'current_team' =>
                                                                    request()->route('current_team'),
                                                                'attachment' =>
                                                                    $attachment->id,
                                                            ]
                                                        );
                                                    @endphp

                                                    @if ($isImage)
                                                        <div class="group/image overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-white/[0.07] dark:bg-white/[0.03]">
                                                            <button
                                                                type="button"
                                                                x-on:click="openLightbox(
                                                                    @js($previewUrl),
                                                                    @js($attachment->original_name)
                                                                )"
                                                                class="relative block w-full cursor-zoom-in"
                                                            >
                                                                <div class="max-h-[520px] overflow-hidden bg-zinc-100 dark:bg-zinc-900">
                                                                    <img
                                                                        src="{{ $previewUrl }}"
                                                                        alt="{{ $attachment->original_name }}"
                                                                        loading="lazy"
                                                                        decoding="async"
                                                                        class="block max-h-[520px] w-full object-contain transition duration-500 group-hover/image:scale-[1.01]"
                                                                    />
                                                                </div>

                                                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-violet-900/0 transition group-hover/image:bg-violet-900/10">
                                                                    <span class="flex size-12 scale-90 items-center justify-center rounded-2xl bg-white text-violet-600 opacity-0 shadow-2xl transition group-hover/image:scale-100 group-hover/image:opacity-100">
                                                                        <flux:icon.arrows-pointing-out class="size-5" />
                                                                    </span>
                                                                </div>
                                                            </button>

                                                            <div class="flex items-center gap-3 border-t border-zinc-200 bg-white px-3.5 py-3 dark:border-white/[0.05] dark:bg-[#17141c]">
                                                                <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">
                                                                    <flux:icon.photo class="size-4" />
                                                                </div>

                                                                <div class="min-w-0 flex-1">
                                                                    <div class="truncate text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                                                        {{ $attachment->original_name }}
                                                                    </div>

                                                                    <div class="mt-0.5 text-[10px] text-zinc-400">
                                                                        {{ number_format($attachment->size / 1024, 1) }} KB
                                                                    </div>
                                                                </div>

                                                                <a
                                                                    href="{{ $downloadUrl }}"
                                                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl text-zinc-400 transition hover:bg-violet-50 hover:text-violet-600 dark:hover:bg-white/5 dark:hover:text-violet-300"
                                                                    title="Download"
                                                                >
                                                                    <flux:icon.arrow-down-tray class="size-4" />
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <a
                                                            href="{{ $downloadUrl }}"
                                                            class="group/file flex w-full max-w-xl items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 p-3.5 transition hover:-translate-y-0.5 hover:border-violet-200 hover:bg-white hover:shadow-lg dark:border-white/[0.07] dark:bg-white/[0.03] dark:hover:border-white/10 dark:hover:bg-white/[0.05]"
                                                        >
                                                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm dark:bg-white/10">
                                                                @if ($isPdf)
                                                                    <flux:icon.document-text class="size-5 text-red-500" />
                                                                @else
                                                                    <flux:icon.paper-clip class="size-5 text-violet-500 dark:text-violet-300" />
                                                                @endif
                                                            </div>

                                                            <div class="min-w-0 flex-1">
                                                                <div class="truncate text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                                                                    {{ $attachment->original_name }}
                                                                </div>

                                                                <div class="mt-1 text-xs text-zinc-400">
                                                                    {{ number_format($attachment->size / 1024, 1) }} KB

                                                                    <span class="mx-1">·</span>

                                                                    {{ strtoupper(pathinfo($attachment->original_name, PATHINFO_EXTENSION)) }}
                                                                </div>
                                                            </div>

                                                            <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 transition group-hover/file:bg-violet-600 group-hover/file:text-white dark:bg-violet-500/10 dark:text-violet-300">
                                                                <flux:icon.arrow-down-tray class="size-4" />
                                                            </div>
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="mt-2 text-xs text-zinc-400 {{ $isCustomer ? '' : 'text-right' }}">
                                            {{ $message->created_at?->diffForHumans() ?? '—' }}
                                        </div>
                                    </div>

                                    @if (! $isCustomer)
                                        {{-- Team avatar --}}
                                        <div class="relative flex size-10 shrink-0 items-center justify-center rounded-2xl bg-[#8069b0] text-sm font-bold text-white shadow-[0_10px_24px_-10px_rgba(109,83,157,.8)] sm:size-11">
                                            {{ $initial }}

                                            <span class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full border-2 border-white bg-emerald-500 dark:border-[#141218]"></span>
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="px-6 py-24 text-center">
                                <div class="mx-auto mb-5 flex size-16 items-center justify-center rounded-3xl bg-violet-100 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">
                                    <flux:icon.chat-bubble-left-right class="size-7" />
                                </div>

                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                                    No messages yet
                                </h3>

                                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                    Send a reply below to begin the conversation.
                                </p>
                            </div>
                        @endforelse
                    </div>

                    {{-- ====================================================
                        COMPOSER
                    ===================================================== --}}
                    <div class="border-t border-zinc-200 bg-zinc-50/80 p-3 sm:p-5 dark:border-white/[0.06] dark:bg-[#100f14]">
                        <div class="overflow-hidden rounded-[24px] border border-zinc-200 bg-white shadow-[0_14px_50px_-35px_rgba(45,35,65,.4)] transition focus-within:border-violet-300 focus-within:ring-4 focus-within:ring-violet-100 dark:border-white/[0.08] dark:bg-[#17141c] dark:focus-within:border-violet-500/30 dark:focus-within:ring-violet-500/10">

                            <textarea
                                wire:model="newMessage"
                                rows="4"
                                placeholder="Write a reply to {{ $ticket->name }}..."
                                class="block min-h-[125px] w-full resize-none border-0 bg-transparent px-4 py-4 text-[15px] leading-7 text-zinc-800 outline-none placeholder:text-zinc-400 focus:border-0 focus:ring-0 sm:px-5 sm:py-5 dark:text-white"
                            ></textarea>

                            @if (count($attachments))
                                <div class="border-t border-zinc-100 px-4 py-4 dark:border-white/[0.05]">
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-violet-600 dark:text-violet-300">
                                            Ready to send
                                        </div>

                                        <div class="text-xs text-zinc-400">
                                            {{ count($attachments) }}
                                            {{ Str::plural('file', count($attachments)) }}
                                        </div>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                        @foreach ($attachments as $index => $attachment)
                                            @if ($attachment)
                                                @php
                                                    $uploadMime =
                                                        strtolower(
                                                            (string) $attachment->getMimeType()
                                                        );

                                                    $uploadIsImage =
                                                        str_starts_with(
                                                            $uploadMime,
                                                            'image/'
                                                        );
                                                @endphp

                                                <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50 dark:border-white/[0.07] dark:bg-white/[0.03]">
                                                    @if ($uploadIsImage)
                                                        <div class="aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-900">
                                                            <img
                                                                src="{{ $attachment->temporaryUrl() }}"
                                                                alt="{{ $attachment->getClientOriginalName() }}"
                                                                class="h-full w-full object-cover"
                                                            />
                                                        </div>
                                                    @else
                                                        <div class="flex min-h-[82px] items-center gap-3 px-3">
                                                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm dark:bg-white/10">
                                                                @if ($uploadMime === 'application/pdf')
                                                                    <flux:icon.document-text class="size-5 text-red-500" />
                                                                @else
                                                                    <flux:icon.paper-clip class="size-5 text-violet-500 dark:text-violet-300" />
                                                                @endif
                                                            </div>

                                                            <div class="min-w-0">
                                                                <div class="truncate text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                                                    {{ $attachment->getClientOriginalName() }}
                                                                </div>

                                                                <div class="mt-1 text-[10px] text-zinc-400">
                                                                    {{ number_format($attachment->getSize() / 1024, 1) }} KB
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <button
                                                        type="button"
                                                        wire:click="removeAttachment({{ $index }})"
                                                        class="absolute right-2 top-2 flex size-8 items-center justify-center rounded-xl bg-black/70 text-white opacity-0 shadow-lg transition group-hover:opacity-100 hover:bg-red-500"
                                                        title="Remove attachment"
                                                    >
                                                        <flux:icon.x-mark class="size-3.5" />
                                                    </button>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Upload state --}}
                            <div
                                wire:loading
                                wire:target="attachments"
                                class="border-t border-zinc-100 px-4 py-3 dark:border-white/[0.05]"
                            >
                                <div class="flex items-center gap-2 text-xs font-medium text-violet-600 dark:text-violet-300">
                                    <flux:icon.arrow-path class="size-4 animate-spin" />
                                    Uploading attachments…
                                </div>
                            </div>

                            {{-- Composer footer --}}
                            <div class="flex flex-col gap-3 border-t border-zinc-100 px-3 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-white/[0.05]">
                                <div class="flex min-w-0 items-center justify-between gap-2 sm:justify-start">
                                    <label class="group flex cursor-pointer items-center gap-2 rounded-xl px-2 py-1.5 text-sm font-semibold text-zinc-500 transition hover:bg-violet-50 hover:text-violet-600 dark:hover:bg-white/5 dark:hover:text-violet-300">
                                        <span class="flex size-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">
                                            <flux:icon.paper-clip class="size-4" />
                                        </span>

                                        <span>Attach</span>

                                        <input
                                            type="file"
                                            wire:model="attachments"
                                            multiple
                                            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                                            class="hidden"
                                        />
                                    </label>

                                    <span class="hidden text-xs text-zinc-400 sm:inline">
                                        Images & files · 10 MB max
                                    </span>
                                </div>

                                <flux:button
                                    wire:click="sendMessage"
                                    wire:loading.attr="disabled"
                                    wire:target="sendMessage"
                                    variant="primary"
                                    class="min-h-11 w-full cursor-pointer rounded-xl bg-[#8069b0]! px-5 text-sm font-semibold text-white shadow-[0_12px_28px_-15px_rgba(104,78,150,.85)] transition hover:bg-[#71569f]! disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
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
                </section>

                {{-- ========================================================
                    BILLING / PAYMENT
                ========================================================= --}}
                @if ($showChargeSection)
                    <section class="space-y-5">

                        {{-- ====================================================
                            PAID CARD
                        ===================================================== --}}
                        @if ($isPaid && $invoice)
                            <div class="overflow-hidden rounded-[28px] border border-emerald-200/80 bg-white shadow-[0_24px_70px_-40px_rgba(16,185,129,.3)] dark:border-emerald-500/15 dark:bg-[#141218]">

                                <div class="relative overflow-hidden border-b border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-teal-50 px-5 py-6 sm:px-7 sm:py-7 dark:border-emerald-500/10 dark:from-emerald-500/10 dark:via-[#141218] dark:to-teal-500/5">
                                    <div class="pointer-events-none absolute -right-16 -top-16 size-52 rounded-full bg-emerald-200/30 blur-3xl dark:bg-emerald-400/10"></div>

                                    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="flex min-w-0 items-start gap-4">
                                            <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 shadow-sm dark:bg-emerald-500/15 dark:text-emerald-300">
                                                <flux:icon.check class="size-6" />
                                            </div>

                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="text-lg font-bold tracking-tight text-zinc-950 sm:text-xl dark:text-white">
                                                        Payment complete
                                                    </h3>

                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                                        Paid
                                                    </span>
                                                </div>

                                                <p class="mt-1.5 max-w-2xl text-sm leading-6 text-emerald-800/80 dark:text-emerald-200/80">
                                                    This ticket has been billed and the customer payment was successfully received.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="w-full rounded-2xl border border-emerald-200/70 bg-white/80 px-4 py-3 backdrop-blur lg:w-auto lg:min-w-[180px] dark:border-emerald-500/10 dark:bg-zinc-900/60">
                                            <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">
                                                Total billed
                                            </div>

                                            <div class="mt-1 text-2xl font-bold tabular-nums tracking-tight text-zinc-950 dark:text-white">
                                                ${{ number_format($invoice->total_amount / 100, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 sm:p-6 lg:p-7">
                                    <div class="grid gap-5 lg:grid-cols-2">

                                        {{-- Services --}}
                                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 sm:p-5 dark:border-zinc-800 dark:bg-zinc-950/40">
                                            <div class="mb-4 flex items-start justify-between gap-4">
                                                <div>
                                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                                        Services billed
                                                    </div>

                                                    <div class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                                        Charges associated with this ticket
                                                    </div>
                                                </div>

                                                <span class="rounded-xl bg-white px-3 py-1.5 text-xs font-semibold text-zinc-500 ring-1 ring-zinc-200 dark:bg-zinc-900 dark:text-zinc-300 dark:ring-zinc-800">
                                                    {{ $invoice->items->count() }}
                                                    {{ $invoice->items->count() === 1 ? 'item' : 'items' }}
                                                </span>
                                            </div>

                                            <div class="divide-y divide-zinc-200/80 dark:divide-zinc-800">
                                                @foreach ($invoice->items as $item)
                                                    <div class="flex items-start justify-between gap-4 py-3.5 first:pt-0 last:pb-0">
                                                        <div class="min-w-0">
                                                            <div class="break-words text-sm font-medium text-zinc-800 dark:text-white">
                                                                {{ $item->description }}
                                                            </div>

                                                            @if ($item->is_subscription)
                                                                <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-violet-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">
                                                                    <flux:icon.arrow-path-rounded-square class="size-3" />
                                                                    Recurring monthly
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <span class="shrink-0 text-sm font-semibold tabular-nums text-zinc-900 dark:text-white">
                                                            ${{ number_format($item->amount / 100, 2) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="mt-4 flex items-center justify-between gap-4 border-t border-zinc-200 pt-4 dark:border-zinc-800">
                                                <span class="text-sm font-semibold text-zinc-600 dark:text-zinc-400">
                                                    Total billed today
                                                </span>

                                                <span class="text-base font-bold tabular-nums text-zinc-950 dark:text-white">
                                                    ${{ number_format($invoice->total_amount / 100, 2) }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Payment economics --}}
                                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-4 sm:p-5 dark:border-zinc-800 dark:bg-zinc-950/40">
                                            <div class="mb-4">
                                                <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                                    Payment breakdown
                                                </div>

                                                <div class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                                    Actual Stripe transaction economics
                                                </div>
                                            </div>

                                            <div class="space-y-3">
                                                <div class="flex items-center justify-between gap-4 text-sm">
                                                    <span class="text-zinc-500 dark:text-zinc-400">
                                                        Gross
                                                    </span>

                                                    <span class="font-medium tabular-nums text-zinc-900 dark:text-white">
                                                        ${{ number_format($invoice->total_amount / 100, 2) }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center justify-between gap-4 text-sm">
                                                    <span class="text-zinc-500 dark:text-zinc-400">
                                                        Stripe fees
                                                    </span>

                                                    <span class="font-medium tabular-nums text-zinc-900 dark:text-white">
                                                        ${{ number_format($invoice->stripeProcessingFeeInCents() / 100, 2) }}
                                                    </span>
                                                </div>

                                                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                                                    <div class="flex items-center justify-between gap-4">
                                                        <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                                                            Net profit
                                                        </span>

                                                        <span class="text-base font-bold tabular-nums text-emerald-900 dark:text-emerald-200">
                                                            ${{ number_format($invoice->netAmountInCents() / 100, 2) }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="border-t border-zinc-200 pt-3 dark:border-zinc-800"></div>

                                                <div class="flex items-center justify-between gap-4 text-sm">
                                                    <span class="text-zinc-500 dark:text-zinc-400">
                                                        Pablo commission
                                                        ({{ number_format(
                                                            $invoice->total_amount > 0
                                                                ? ($invoice->pabloCommissionInCents() / $invoice->total_amount) * 100
                                                                : 0,
                                                            0
                                                        ) }}%)
                                                    </span>

                                                    <span class="font-medium tabular-nums text-zinc-900 dark:text-white">
                                                        ${{ number_format($invoice->pabloCommissionInCents() / 100, 2) }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center justify-between gap-4 text-sm">
                                                    <span class="text-zinc-500 dark:text-zinc-400">
                                                        Owner share
                                                    </span>

                                                    <span class="font-medium tabular-nums text-zinc-900 dark:text-white">
                                                        ${{ number_format($invoice->ownerShareInCents() / 100, 2) }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center justify-between gap-4 text-sm">
                                                    <span class="text-zinc-500 dark:text-zinc-400">
                                                        Partner share
                                                    </span>

                                                    <span class="font-medium tabular-nums text-zinc-900 dark:text-white">
                                                        ${{ number_format($invoice->partnerShareInCents() / 100, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Signature --}}
                                        @if ($hasSignature)
                                            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white lg:col-span-2 dark:border-zinc-800 dark:bg-zinc-950">
                                                <div class="flex flex-col gap-4 border-b border-zinc-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5 dark:border-zinc-800">
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">
                                                            <flux:icon.pencil class="size-5" />
                                                        </div>

                                                        <div>
                                                            <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">
                                                                Captured customer signature
                                                            </h4>

                                                            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                                                Signature recorded on the invoice agreement.
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        x-on:click="openLightbox(@js($signatureData), 'Customer signature')"
                                                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2 text-xs font-semibold text-zinc-600 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-white/5 dark:hover:text-violet-300"
                                                    >
                                                        <flux:icon.arrows-pointing-out class="size-4" />
                                                        View signature
                                                    </button>
                                                </div>

                                                <div class="bg-zinc-50 p-4 sm:p-5 dark:bg-zinc-950/60">
                                                    <button
                                                        type="button"
                                                        x-on:click="openLightbox(@js($signatureData), 'Customer signature')"
                                                        class="group flex min-h-[180px] w-full items-center justify-center overflow-hidden rounded-2xl border border-dashed border-zinc-300 bg-white p-5 transition hover:border-violet-300 hover:bg-violet-50/20 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-violet-500/40"
                                                    >
                                                        <img
                                                            src="{{ $signatureData }}"
                                                            alt="Captured customer signature"
                                                            class="block max-h-56 max-w-full object-contain transition duration-300 group-hover:scale-[1.015]"
                                                            loading="lazy"
                                                            decoding="async"
                                                        />
                                                    </button>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Transaction --}}
                                        @if ($transaction)
                                            <div class="rounded-2xl border border-zinc-200 bg-white p-4 sm:p-5 lg:col-span-2 dark:border-zinc-800 dark:bg-zinc-950">
                                                <div class="mb-4 flex items-center gap-3">
                                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                        <flux:icon.credit-card class="size-5" />
                                                    </div>

                                                    <div>
                                                        <h4 class="text-sm font-semibold text-zinc-900 dark:text-white">
                                                            Payment details
                                                        </h4>

                                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                            Recorded transaction information
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                                    <div class="rounded-xl bg-zinc-50 px-3.5 py-3 dark:bg-zinc-900">
                                                        <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-zinc-400">
                                                            Paid on
                                                        </div>

                                                        <div class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                                            {{ $transaction->created_at?->format('M j, Y g:i A') ?? '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="rounded-xl bg-zinc-50 px-3.5 py-3 dark:bg-zinc-900">
                                                        <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-zinc-400">
                                                            Payment method
                                                        </div>

                                                        <div class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                                            {{ ucfirst($transaction->brand ?? $transaction->payment_method_type ?? 'Card') }}

                                                            @if ($transaction->last4)
                                                                <span class="text-zinc-400">
                                                                    •••• {{ $transaction->last4 }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="rounded-xl bg-zinc-50 px-3.5 py-3 dark:bg-zinc-900">
                                                        <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-zinc-400">
                                                            Net received
                                                        </div>

                                                        <div class="mt-1 text-sm font-semibold tabular-nums text-emerald-700 dark:text-emerald-300">
                                                            ${{ number_format($transaction->net_amount / 100, 2) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Recurring --}}
                                    @if ($invoice->hasRecurringBilling())
                                        <div class="mt-5 rounded-2xl border border-violet-200/80 bg-gradient-to-br from-violet-50 to-fuchsia-50/50 p-4 sm:p-5 dark:border-violet-500/15 dark:from-violet-500/10 dark:to-fuchsia-500/5">
                                            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                                <div>
                                                    <div class="text-sm font-semibold text-violet-950 dark:text-violet-100">
                                                        Monthly recurring
                                                    </div>

                                                    <div class="mt-0.5 text-xs text-violet-700/70 dark:text-violet-300/70">
                                                        Ongoing subscription economics
                                                    </div>
                                                </div>

                                                <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                                                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                                    Stripe auto-pay active
                                                </span>
                                            </div>

                                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                                <div class="rounded-xl bg-white/70 p-3.5 dark:bg-zinc-900/40">
                                                    <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-violet-700/70 dark:text-violet-300/70">
                                                        Gross monthly
                                                    </div>

                                                    <div class="mt-1 text-base font-semibold tabular-nums text-violet-950 dark:text-white">
                                                        ${{ number_format($invoice->monthlyRecurringAmountInCents() / 100, 2) }}
                                                    </div>
                                                </div>

                                                <div class="rounded-xl bg-white/70 p-3.5 dark:bg-zinc-900/40">
                                                    <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-violet-700/70 dark:text-violet-300/70">
                                                        Stripe fees (est.)
                                                    </div>

                                                    <div class="mt-1 text-base font-semibold tabular-nums text-violet-950 dark:text-white">
                                                        ${{ number_format($invoice->estimatedMonthlyStripeFeeInCents() / 100, 2) }}
                                                    </div>
                                                </div>

                                                <div class="rounded-xl border border-violet-200 bg-white/80 p-3.5 dark:border-violet-500/15 dark:bg-zinc-900/50">
                                                    <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-violet-700/70 dark:text-violet-300/70">
                                                        Net profit
                                                    </div>

                                                    <div class="mt-1 text-base font-bold tabular-nums text-violet-950 dark:text-white">
                                                        ${{ number_format($invoice->monthlyNetAmountInCents() / 100, 2) }}
                                                    </div>
                                                </div>

                                                <div class="rounded-xl bg-white/70 p-3.5 dark:bg-zinc-900/40">
                                                    <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-violet-700/70 dark:text-violet-300/70">
                                                        Pablo commission
                                                    </div>

                                                    <div class="mt-1 text-sm font-semibold tabular-nums text-violet-950 dark:text-white">
                                                        ${{ number_format($invoice->monthlyPabloCommissionInCents() / 100, 2) }}
                                                    </div>
                                                </div>

                                                <div class="rounded-xl bg-white/70 p-3.5 dark:bg-zinc-900/40">
                                                    <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-violet-700/70 dark:text-violet-300/70">
                                                        Owner share
                                                    </div>

                                                    <div class="mt-1 text-sm font-semibold tabular-nums text-violet-950 dark:text-white">
                                                        ${{ number_format($invoice->monthlyOwnerShareInCents() / 100, 2) }}
                                                    </div>
                                                </div>

                                                <div class="rounded-xl bg-white/70 p-3.5 dark:bg-zinc-900/40">
                                                    <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-violet-700/70 dark:text-violet-300/70">
                                                        Partner share
                                                    </div>

                                                    <div class="mt-1 text-sm font-semibold tabular-nums text-violet-950 dark:text-white">
                                                        ${{ number_format($invoice->monthlyPartnerShareInCents() / 100, 2) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-4 border-t border-violet-200/70 pt-4 dark:border-violet-500/15">
                                                <div class="grid gap-3 sm:grid-cols-3">
                                                    <div>
                                                        <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-violet-700/70 dark:text-violet-300/70">
                                                            Recurring term
                                                        </div>

                                                        <div class="mt-1 text-sm font-semibold text-violet-950 dark:text-white">
                                                            {{ $invoice->subscription_months ?? 12 }} months minimum
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-violet-700/70 dark:text-violet-300/70">
                                                            Next payment
                                                        </div>

                                                        <div class="mt-1 text-sm font-semibold text-violet-950 dark:text-white">
                                                            {{ $invoice->recurringChargeNextDueAt()?->format('M j, Y') ?? '—' }}
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-violet-700/70 dark:text-violet-300/70">
                                                            Renews / expires
                                                        </div>

                                                        <div class="mt-1 text-sm font-semibold text-violet-950 dark:text-white">
                                                            {{ $invoice->recurringChargeExpiresAt()?->format('M j, Y') ?? '—' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        @else
                            {{-- =================================================
                                READY TO CHARGE
                            ================================================== --}}
                            <div class="overflow-hidden rounded-[28px] border border-indigo-200/80 bg-white shadow-[0_24px_70px_-40px_rgba(99,102,241,.3)] dark:border-indigo-500/15 dark:bg-[#141218]">

                                <div class="relative overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-blue-50 px-5 py-6 sm:px-7 sm:py-7 dark:from-indigo-500/10 dark:via-[#141218] dark:to-blue-500/5">
                                    <div class="pointer-events-none absolute -right-16 -top-16 size-52 rounded-full bg-indigo-200/30 blur-3xl dark:bg-indigo-400/10"></div>

                                    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="flex min-w-0 items-start gap-4">
                                            <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                                                <flux:icon.check-circle class="size-6" />
                                            </div>

                                            <div class="min-w-0">
                                                <h3 class="text-lg font-bold tracking-tight text-zinc-950 sm:text-xl dark:text-white">
                                                    Ticket resolved
                                                </h3>

                                                <p class="mt-1.5 max-w-2xl text-sm leading-6 text-indigo-800/80 dark:text-indigo-200/80">
                                                    The work is complete and the customer is ready to be billed securely.
                                                </p>
                                            </div>
                                        </div>

                                        @if ($invoice)
                                            <div class="w-full rounded-2xl border border-indigo-200/80 bg-white/80 px-4 py-3 backdrop-blur lg:w-auto lg:min-w-[180px] dark:border-indigo-500/10 dark:bg-zinc-900/60">
                                                <div class="text-[10px] font-bold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">
                                                    Amount due
                                                </div>

                                                <div class="mt-1 text-2xl font-bold tabular-nums tracking-tight text-zinc-950 dark:text-white">
                                                    ${{ number_format($invoice->total_amount / 100, 2) }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="relative mt-6 grid gap-3 md:grid-cols-2">
                                        <div class="rounded-2xl border border-indigo-200/70 bg-white/75 p-4 dark:border-indigo-500/10 dark:bg-zinc-900/50">
                                            <div class="flex items-start gap-3">
                                                <flux:icon.shield-check class="mt-0.5 size-5 shrink-0 text-indigo-600 dark:text-indigo-300" />

                                                <div>
                                                    <div class="text-sm font-semibold text-indigo-950 dark:text-indigo-100">
                                                        Secure Stripe billing
                                                    </div>

                                                    <div class="mt-1 text-xs leading-5 text-indigo-900/70 dark:text-indigo-200/70">
                                                        Payment processing is handled through Stripe's secure payment infrastructure.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($hasSignature)
                                            <button
                                                type="button"
                                                x-on:click="openLightbox(@js($signatureData), 'Customer signature')"
                                                class="group rounded-2xl border border-indigo-200/70 bg-white/75 p-4 text-left transition hover:border-indigo-300 hover:bg-white dark:border-indigo-500/10 dark:bg-zinc-900/50 dark:hover:border-indigo-500/30"
                                            >
                                                <div class="flex items-center gap-3">
                                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">
                                                        <flux:icon.pencil class="size-5" />
                                                    </div>

                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-sm font-semibold text-indigo-950 dark:text-indigo-100">
                                                                Signature captured
                                                            </span>

                                                            <flux:icon.arrows-pointing-out class="size-3.5 text-indigo-500" />
                                                        </div>

                                                        <div class="mt-1 text-xs text-indigo-900/70 dark:text-indigo-200/70">
                                                            Click to view the signed agreement.
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @if ($invoice && $hasSignature)
                                    <div class="border-t border-zinc-200 p-4 sm:p-6 dark:border-zinc-800">
                                        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-700! dark:border-zinc-800 dark:bg-zinc-950/50!">
                                            <div class="border-b border-zinc-200 px-4 py-3.5 dark:border-zinc-800">
                                                <div class="flex items-center justify-between gap-3">
                                                    <div>
                                                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                                            Customer signature
                                                        </div>

                                                        <div class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                                            Captured on the invoice
                                                        </div>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        x-on:click="openLightbox(@js($signatureData), 'Customer signature')"
                                                        class="rounded-xl p-2 text-zinc-400 transition hover:bg-white hover:text-violet-600 dark:hover:bg-white/5 dark:hover:text-violet-300"
                                                        title="View signature"
                                                    >
                                                        <flux:icon.arrows-pointing-out class="size-4" />
                                                    </button>
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                x-on:click="openLightbox(@js($signatureData), 'Customer signature')"
                                                class="flex min-h-[160px] w-full items-center justify-center bg-white p-5 dark:bg-zinc-900"
                                            >
                                                <img
                                                    src="{{ $signatureData }}"
                                                    alt="Captured customer signature"
                                                    class="block max-h-52 max-w-full object-contain"
                                                    loading="lazy"
                                                    decoding="async"
                                                />
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                <div class="border-t border-zinc-200 p-4 sm:p-6 dark:border-zinc-800">
                                    <button
                                        type="button"
                                        x-on:click="$dispatch('open-charge-modal', { ticketId: {{ $ticket->id }} })"
                                        class="group flex min-h-14 w-full items-center justify-center gap-2.5 rounded-2xl bg-indigo-600 px-5 py-4 text-sm font-semibold text-white shadow-[0_15px_35px_-18px_rgba(79,70,229,.85)] transition hover:-translate-y-0.5 hover:bg-indigo-500 hover:shadow-[0_18px_40px_-18px_rgba(79,70,229,.9)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 active:translate-y-0 dark:focus-visible:ring-offset-[#141218]"
                                    >
                                        <flux:icon.credit-card class="size-5 transition-transform group-hover:scale-105" />
                                        <span>Charge Customer</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </section>
                @endif
            </div>

            {{-- ============================================================
                SIDEBAR
            ============================================================= --}}
            <aside class="min-w-0 space-y-5 2xl:sticky 2xl:top-24">

                {{-- ========================================================
                    CUSTOMER
                ========================================================= --}}
                <section class="overflow-hidden rounded-[26px] border border-zinc-200 bg-white shadow-[0_18px_50px_-35px_rgba(40,30,60,.35)] dark:border-white/[0.07] dark:bg-[#141218]">
                    <div class="border-b border-zinc-100 p-5 dark:border-white/[0.05]">
                        <div class="mb-4 text-[10px] font-bold uppercase tracking-[0.17em] text-zinc-400">
                            Customer
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-lg font-bold text-violet-700 dark:bg-violet-500/10 dark:text-violet-200">
                                {{ strtoupper(substr($ticket->name, 0, 1)) }}
                            </div>

                            <div class="min-w-0">
                                <div class="truncate text-base font-semibold text-zinc-900 dark:text-white">
                                    {{ $ticket->name }}
                                </div>

                                <div class="mt-1 truncate text-sm text-zinc-400">
                                    {{ $ticket->email }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-px bg-zinc-100 dark:bg-white/[0.05]">
                        <div class="bg-white p-4 dark:bg-[#141218]">
                            <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-zinc-400">
                                Ticket
                            </div>

                            <div class="mt-1 truncate font-mono text-xs font-semibold text-zinc-800 dark:text-zinc-200">
                                {{ $ticket->ticket_number }}
                            </div>
                        </div>

                        <div class="bg-white p-4 dark:bg-[#141218]">
                            <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-zinc-400">
                                Messages
                            </div>

                            <div class="mt-1 text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                                {{ $messageCount }}
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ========================================================
                    TICKET DETAILS
                ========================================================= --}}
                <section class="rounded-[26px] border border-zinc-200 bg-white p-5 shadow-[0_18px_50px_-35px_rgba(40,30,60,.35)] dark:border-white/[0.07] dark:bg-[#141218]">
                    <div class="mb-6 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-base font-semibold text-zinc-900 dark:text-white">
                                Ticket details
                            </div>

                            <div class="mt-1 text-xs text-zinc-400">
                                Request information
                            </div>
                        </div>

                        <div class="flex size-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">
                            <flux:icon.ticket class="size-5" />
                        </div>
                    </div>

                    <div class="space-y-5">

                        {{-- Status --}}
                        <div>
                            <div class="mb-2 text-xs font-medium text-zinc-400">
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
                                    class="flex min-h-12 w-full items-center justify-between rounded-2xl border border-zinc-200 bg-zinc-50 px-4 text-sm font-semibold text-zinc-700 transition hover:border-violet-200 hover:bg-white focus:outline-none focus:ring-4 focus:ring-violet-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-white dark:hover:border-white/15 dark:hover:bg-white/[0.05] dark:focus:ring-violet-500/10"
                                >
                                    <span class="flex items-center gap-3">
                                        <span class="size-2.5 rounded-full {{ $statusDot }}"></span>

                                        {{ ucfirst($status) }}
                                    </span>

                                    <flux:icon.chevron-down
                                        class="size-4 text-zinc-400 transition"
                                        x-bind:class="open && 'rotate-180'"
                                    />
                                </button>

                                <div
                                    x-show="open"
                                    x-cloak
                                    x-transition.origin.top
                                    class="absolute left-0 right-0 z-50 mt-2 overflow-hidden rounded-2xl border border-zinc-200 bg-white p-1.5 shadow-[0_22px_60px_-25px_rgba(40,30,60,.35)] dark:border-white/10 dark:bg-[#1c1821]"
                                >
                                    @foreach ([
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
                                            class="flex w-full items-center justify-between rounded-xl px-3.5 py-3 text-left text-sm font-medium transition hover:bg-zinc-50 dark:hover:bg-white/5"
                                        >
                                            <span class="flex items-center gap-3">
                                                <span
                                                    class="size-2.5 rounded-full {{ match ($value) {
                                                        'open' => 'bg-emerald-500',
                                                        'pending' => 'bg-amber-500',
                                                        'resolved' => 'bg-blue-500',
                                                        'closed' => 'bg-zinc-400',
                                                        default => 'bg-zinc-400',
                                                    } }}"
                                                ></span>

                                                {{ $label }}
                                            </span>

                                            @if ($status === $value)
                                                <flux:icon.check class="size-4 text-violet-600 dark:text-violet-300" />
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Priority --}}
                        <div class="border-t border-zinc-100 pt-5 dark:border-white/[0.05]">
                            <div class="mb-2 text-xs font-medium text-zinc-400">
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
                                    class="flex min-h-12 w-full items-center justify-between rounded-2xl border border-zinc-200 bg-zinc-50 px-4 text-sm font-semibold text-zinc-700 transition hover:border-violet-200 hover:bg-white focus:outline-none focus:ring-4 focus:ring-violet-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-white dark:hover:border-white/15 dark:hover:bg-white/[0.05] dark:focus:ring-violet-500/10"
                                >
                                    <span class="flex items-center gap-3">
                                        <span
                                            class="flex size-8 items-center justify-center rounded-xl
                                            {{ match ($priority ?: 'normal') {
                                                'low' => 'bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-400/10',
                                                'normal' => 'bg-violet-50 text-violet-600 ring-1 ring-inset ring-violet-100 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-500/20',
                                                'high' => 'bg-orange-50 text-orange-600 ring-1 ring-inset ring-orange-100 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/20',
                                                'urgent' => 'bg-red-50 text-red-600 ring-1 ring-inset ring-red-100 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/20',
                                                default => 'bg-violet-50 text-violet-600 ring-1 ring-inset ring-violet-100 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-500/20',
                                            } }}"
                                        >
                                            @if (($priority ?: 'normal') === 'low')
                                                <flux:icon.arrow-down class="size-4" />
                                            @elseif (($priority ?: 'normal') === 'normal')
                                                <flux:icon.minus class="size-4" />
                                            @elseif (($priority ?: 'normal') === 'high')
                                                <flux:icon.arrow-up class="size-4" />
                                            @elseif (($priority ?: 'normal') === 'urgent')
                                                <flux:icon.exclamation-triangle class="size-4" />
                                            @endif
                                        </span>

                                        <span>{{ ucfirst($priority ?: 'normal') }}</span>
                                    </span>

                                    <flux:icon.chevron-down
                                        class="size-4 text-zinc-400 transition"
                                        x-bind:class="open && 'rotate-180'"
                                    />
                                </button>

                                <div
                                    x-show="open"
                                    x-cloak
                                    x-transition.origin.top
                                    class="absolute left-0 right-0 z-50 mt-2 overflow-hidden rounded-2xl border border-zinc-200 bg-white p-1.5 shadow-[0_22px_60px_-25px_rgba(40,30,60,.35)] dark:border-white/10 dark:bg-[#1c1821]"
                                >
                                    @foreach ([
                                        'low' => [
                                            'label' => 'Low',
                                            'description' => 'Can wait',
                                            'class' => 'bg-slate-100 text-slate-500 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-400/10',
                                        ],
                                        'normal' => [
                                            'label' => 'Normal',
                                            'description' => 'Standard request',
                                            'class' => 'bg-violet-50 text-violet-600 ring-1 ring-inset ring-violet-100 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-500/20',
                                        ],
                                        'high' => [
                                            'label' => 'High',
                                            'description' => 'Needs attention',
                                            'class' => 'bg-orange-50 text-orange-600 ring-1 ring-inset ring-orange-100 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/20',
                                        ],
                                        'urgent' => [
                                            'label' => 'Urgent',
                                            'description' => 'Needs immediate action',
                                            'class' => 'bg-red-50 text-red-600 ring-1 ring-inset ring-red-100 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/20',
                                        ],
                                    ] as $value => $option)
                                        <button
                                            type="button"
                                            wire:click="updatePriority('{{ $value }}')"
                                            x-on:click="open = false"
                                            wire:loading.attr="disabled"
                                            wire:target="updatePriority('{{ $value }}')"
                                            class="group flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition hover:bg-zinc-50 dark:hover:bg-white/5"
                                        >
                                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl {{ $option['class'] }}">
                                                @if ($value === 'low')
                                                    <flux:icon.arrow-down class="size-4" />
                                                @elseif ($value === 'normal')
                                                    <flux:icon.minus class="size-4" />
                                                @elseif ($value === 'high')
                                                    <flux:icon.arrow-up class="size-4" />
                                                @else
                                                    <flux:icon.exclamation-triangle class="size-4" />
                                                @endif
                                            </span>

                                            <span class="min-w-0 flex-1">
                                                <span class="block text-sm font-semibold text-zinc-800 dark:text-white/90">
                                                    {{ $option['label'] }}
                                                </span>

                                                <span class="block text-[11px] text-zinc-400">
                                                    {{ $option['description'] }}
                                                </span>
                                            </span>

                                            @if (($priority ?: 'normal') === $value)
                                                <span class="flex size-7 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300">
                                                    <flux:icon.check class="size-3.5" />
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Ticket number --}}
                        <div class="border-t border-zinc-100 pt-5 dark:border-white/[0.05]">
                            <div class="text-xs font-medium text-zinc-400">
                                Ticket number
                            </div>

                            <div class="mt-2 break-all font-mono text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                                {{ $ticket->ticket_number }}
                            </div>
                        </div>

                        {{-- Created --}}
                        <div class="border-t border-zinc-100 pt-5 dark:border-white/[0.05]">
                            <div class="text-xs font-medium text-zinc-400">
                                Created
                            </div>

                            <div class="mt-2 text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                                {{ $ticket->created_at?->format('M j, Y · g:i A') ?? '—' }}
                            </div>
                        </div>

                        {{-- Last activity --}}
                        <div>
                            <div class="text-xs font-medium text-zinc-400">
                                Last activity
                            </div>

                            <div class="mt-2 text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                                {{ $ticket->last_activity_at?->diffForHumans() ?? '—' }}
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ========================================================
                    SIGNATURE SUMMARY
                ========================================================= --}}
                @if ($hasSignature)
                    <section class="overflow-hidden rounded-[26px] border border-sky-200/80 bg-white shadow-[0_18px_50px_-35px_rgba(14,165,233,.25)] dark:border-sky-500/15 dark:bg-[#141218]">
                        <div class="border-b border-sky-100 px-5 py-4 dark:border-sky-500/10">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">
                                    <flux:icon.pencil class="size-5" />
                                </div>

                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        Signature captured
                                    </div>

                                    <div class="mt-0.5 text-xs text-zinc-400">
                                        Signed invoice agreement
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            <button
                                type="button"
                                x-on:click="openLightbox(@js($signatureData), 'Customer signature')"
                                class="group flex min-h-[135px] w-full items-center justify-center overflow-hidden rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-4 transition hover:border-violet-300 hover:bg-violet-50/20 dark:border-zinc-700 dark:bg-zinc-950/50 dark:hover:border-violet-500/40"
                            >
                                <img
                                    src="{{ $signatureData }}"
                                    alt="Captured customer signature"
                                    class="block max-h-40 max-w-full object-contain transition duration-300 group-hover:scale-[1.015]"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </button>

                            <button
                                type="button"
                                x-on:click="openLightbox(@js($signatureData), 'Customer signature')"
                                class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-600 transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:text-violet-300"
                            >
                                <flux:icon.arrows-pointing-out class="size-4" />
                                View full signature
                            </button>
                        </div>
                    </section>
                @endif

                {{-- ========================================================
                    ACTIVITY
                ========================================================= --}}
                <section class="relative overflow-hidden rounded-[26px] bg-[#5f517b] p-6 text-white shadow-[0_22px_60px_-35px_rgba(80,60,110,.75)]">
                    <div class="pointer-events-none absolute -right-16 -top-20 size-52 rounded-full bg-white/[0.07] blur-3xl"></div>
                    <div class="pointer-events-none absolute -bottom-20 -left-16 size-44 rounded-full bg-white/[0.05] blur-3xl"></div>

                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-[0.17em] text-white/55">
                                    Activity
                                </div>

                                <div class="mt-1 text-sm text-white/65">
                                    Conversation volume
                                </div>
                            </div>

                            <div class="flex size-10 items-center justify-center rounded-xl bg-white/10">
                                <flux:icon.bolt class="size-4" />
                            </div>
                        </div>

                        <div class="mt-7 text-5xl font-bold tracking-[-0.05em]">
                            {{ $messageCount }}
                        </div>

                        <div class="mt-1 text-sm text-white/60">
                            {{ Str::plural('message', $messageCount) }} exchanged
                        </div>

                        <div class="mt-7 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-white/[0.08] p-3">
                                <div class="text-[10px] uppercase tracking-[0.13em] text-white/45">
                                    Status
                                </div>

                                <div class="mt-1 text-sm font-semibold text-white">
                                    {{ ucfirst($status) }}
                                </div>
                            </div>

                            <div class="rounded-2xl bg-white/[0.08] p-3">
                                <div class="text-[10px] uppercase tracking-[0.13em] text-white/45">
                                    Priority
                                </div>

                                <div class="mt-1 text-sm font-semibold text-white">
                                    {{ ucfirst($priority ?: 'normal') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </main>

    {{-- ================================================================
        CHARGE CUSTOMER MODAL
    ================================================================= --}}
    <livewire:teams.tickets.charge-customer-modal />
</div>