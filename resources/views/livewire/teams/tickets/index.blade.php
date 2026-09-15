<div>
    <style>
        /* =========================================================
           TICKETS KANBAN — FULL CSS
           Dark Luxury + Light Mode
           ========================================================= */
        /* =========================================================
           BASE & DARK MODE (Default)
           ========================================================= */
        .cc-tickets {
            --brand-purple: #963c96;
            --brand-lime: #ADD322;
            --brand-light-purple: #CCA3FF;
            --brand-cyan: #ccfef4;

            --bg-color: #050408;
            --panel-bg: rgba(13, 11, 19, 0.7);

            color: #ffffff;
            isolation: isolate;
            /* REMOVED: background and background-attachment: fixed */
        }

        /* =========================================================
           COMBINED BACKGROUND & NOISE TEXTURE
           ========================================================= */
        .cc-tickets .noise {
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            /* Force hardware acceleration */
            transform: translate3d(0, 0, 0);
            will-change: transform;
            /* Combined all gradients into this single, static, GPU-accelerated layer */
            background:
                /* Noise gradients */
                radial-gradient(550px circle at 25% 18%, rgba(204, 163, 255, 0.08), transparent 70%),
                radial-gradient(450px circle at 80% 28%, rgba(173, 211, 34, 0.03), transparent 70%),
                linear-gradient(115deg, transparent 0%, rgba(255, 255, 255, 0.015) 45%, transparent 70%),
                /* Base background gradients */
                radial-gradient(800px circle at 10% -5%, rgba(150, 60, 150, 0.15), transparent 50%),
                radial-gradient(900px circle at 90% 10%, rgba(109, 40, 217, 0.12), transparent 50%),
                radial-gradient(600px circle at 50% 60%, rgba(204, 163, 255, 0.05), transparent 60%),
                radial-gradient(500px circle at 15% 100%, rgba(173, 211, 34, 0.05), transparent 60%),
                linear-gradient(135deg, #030205 0%, #08060d 30%, #0a0711 52%, #06050a 78%, #030305 100%);
        }

        /* =========================================================
           GLASSMORPHISM (Header Only)
           ========================================================= */
        .cc-tickets .glass-panel {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transform: translate3d(0, 0, 0);
        }

        /* =========================================================
           KANBAN COLUMNS
           ========================================================= */
        .cc-tickets .kanban-column {
            /* Slightly increased base opacity to compensate for removed blur */
            background: linear-gradient(180deg, rgba(30, 25, 40, 0.5), rgba(25, 20, 30, 0.2));
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.02);
            /* REMOVED: backdrop-filter: blur(4px) */
            transition: border-color 0.3s ease, background 0.3s ease;
        }

        .cc-tickets .kanban-column.drag-over {
            background: rgba(150, 60, 150, 0.08);
            border-color: rgba(204, 163, 255, 0.3);
            box-shadow: inset 0 0 20px rgba(204, 163, 255, 0.05), 0 0 0 1px rgba(204, 163, 255, 0.1);
        }

        /* =========================================================
           TICKET CARDS
           ========================================================= */
        .cc-tickets .ticket-card {
            /* Made the background slightly more opaque since we removed the blur */
            background: linear-gradient(145deg, rgba(20, 18, 25, 0.6), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.03);
            transition: transform 0.2s cubic-bezier(0.2, 0.8, 0.2, 1),
                border-color 0.2s ease,
                box-shadow 0.2s ease;
            /*
               REMOVED: backdrop-filter: blur() (Major performance killer on lists)
               REMOVED: will-change: transform (Consumes too much GPU RAM for lists)
            */
        }

        .cc-tickets .ticket-card:hover {
            transform: translateY(-2px);
            border-color: rgba(204, 163, 255, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(204, 163, 255, 0.1),
                0 10px 30px rgba(150, 60, 150, 0.15);
        }

        .cc-tickets .ticket-card.is-dragging {
            opacity: 0.5;
            transform: scale(0.95) rotate(2deg);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
            border-color: var(--brand-lime);
        }

        /* =========================================================
           INPUTS & SELECTS
           ========================================================= */
        .cc-tickets .cc-input {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01));
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #fff;
            transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        .cc-tickets .cc-input:hover {
            border-color: rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.04);
        }

        .cc-tickets .cc-input:focus {
            border-color: var(--brand-light-purple);
            background: rgba(0, 0, 0, 0.3);
            box-shadow: 0 0 0 3px rgba(150, 60, 150, 0.2), 0 10px 35px rgba(0, 0, 0, 0.2);
            outline: none;
        }

        .cc-tickets .cc-input::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }

        /* =========================================================
           SCROLLBARS
           ========================================================= */
        .cc-tickets .cc-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .cc-tickets .cc-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .cc-tickets .cc-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 999px;
        }

        .cc-tickets .cc-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(204, 163, 255, 0.4);
        }

        /* =========================================================
           LIGHT MODE
           ========================================================= */
        .cc-tickets.light {
            color: #171717;
            background:
                radial-gradient(800px circle at 10% -5%, rgba(150, 60, 150, 0.08), transparent 50%),
                radial-gradient(900px circle at 90% 10%, rgba(109, 40, 217, 0.06), transparent 50%),
                radial-gradient(600px circle at 50% 60%, rgba(204, 163, 255, 0.03), transparent 60%),
                radial-gradient(500px circle at 15% 100%, rgba(173, 211, 34, 0.04), transparent 60%),
                linear-gradient(135deg, #f8f8fa 0%, #f2f1f6 30%, #f7f7f9 55%, #eeeeF2 100%);
        }

        .cc-tickets.light .noise {
            background:
                radial-gradient(550px circle at 25% 18%, rgba(204, 163, 255, 0.05), transparent 70%),
                radial-gradient(450px circle at 80% 28%, rgba(173, 211, 34, 0.02), transparent 70%),
                linear-gradient(115deg, transparent 0%, rgba(0, 0, 0, 0.015) 45%, transparent 70%);
        }

        .cc-tickets.light .glass-panel {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.6));
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        }

        .cc-tickets.light .kanban-column {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.6), rgba(255, 255, 255, 0.4));
            border-color: rgba(0, 0, 0, 0.06);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 1);
        }

        .cc-tickets.light .kanban-column.drag-over {
            background: rgba(150, 60, 150, 0.04);
            border-color: rgba(150, 60, 150, 0.2);
            box-shadow: inset 0 0 20px rgba(150, 60, 150, 0.03), 0 0 0 1px rgba(150, 60, 150, 0.1);
        }

        .cc-tickets.light .ticket-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.8));
            border-color: rgba(0, 0, 0, 0.08);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03), inset 0 1px 0 rgba(255, 255, 255, 1);
        }

        .cc-tickets.light .ticket-card:hover {
            border-color: rgba(150, 60, 150, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08),
                0 0 0 1px rgba(150, 60, 150, 0.1),
                0 10px 30px rgba(150, 60, 150, 0.05);
        }

        .cc-tickets.light .cc-input {
            background: rgba(255, 255, 255, 0.7);
            border-color: rgba(0, 0, 0, 0.1);
            color: #171717;
        }

        .cc-tickets.light .cc-input:hover {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(0, 0, 0, 0.2);
        }

        .cc-tickets.light .cc-input:focus {
            background: #ffffff;
            border-color: var(--brand-purple);
            box-shadow: 0 0 0 3px rgba(150, 60, 150, 0.15), 0 10px 35px rgba(0, 0, 0, 0.05);
        }

        .cc-tickets.light .cc-input::placeholder {
            color: rgba(0, 0, 0, 0.4);
        }

        .cc-tickets.light .text-white {
            color: #171717 !important;
        }

        .cc-tickets.light .text-white\/30,
        .cc-tickets.light .text-white\/40,
        .cc-tickets.light .text-white\/50 {
            color: #686970 !important;
        }

        .cc-tickets.light .text-white\/70 {
            color: #3f3f46 !important;
        }

        .cc-tickets.light .border-white\/10,
        .cc-tickets.light .border-white\/\[0\.06\] {
            border-color: rgba(0, 0, 0, 0.08) !important;
        }

        .cc-tickets.light .bg-white\/\[0\.04\],
        .cc-tickets.light .bg-white\/\[0\.06\] {
            background-color: rgba(0, 0, 0, 0.04) !important;
        }

        .cc-tickets.light .bg-\[\#12101a\],
        .cc-tickets.light .bg-\[\#171420\] {
            background-color: #ffffff !important;
        }

        /* Text exceptions for status badges to remain legible */
        .cc-tickets.light .priority-badge {
            color: #171717 !important;
            mix-blend-mode: multiply;
        }
    </style>

    <div x-data="{
        htmlIsDark: document.documentElement.classList.contains('dark'),
        observer: null,
    
        dragging: false,
        draggingTicket: null,
        dragColumn: null,
        moving: false,
    
        init() {
            this.observer = new MutationObserver(() => {
                this.htmlIsDark = document.documentElement.classList.contains('dark');
            });
            this.observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class'],
            });
            this.$nextTick(() => {
                this.htmlIsDark = document.documentElement.classList.contains('dark');
            });
        },
    
        get isLightMode() {
            return !this.htmlIsDark;
        },
    
        startDrag(event, ticketId) {
            this.dragging = true;
            this.draggingTicket = String(ticketId);
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', String(ticketId));
            event.dataTransfer.setData('ticket-id', String(ticketId));
    
            // Use a slight delay to allow the drag image to capture the normal state before visually dimming
            setTimeout(() => {
                event.target.classList.add('is-dragging');
            }, 10);
        },
    
        endDrag(event) {
            this.dragging = false;
            this.draggingTicket = null;
            this.dragColumn = null;
            event.target.classList.remove('is-dragging');
        },
    
        dragOver(event, column) {
            event.preventDefault();
            if (!this.dragging || this.moving) return;
            event.dataTransfer.dropEffect = 'move';
            this.dragColumn = column;
        },
    
        dragEnter(event, column) {
            event.preventDefault();
            if (!this.dragging || this.moving) return;
            this.dragColumn = column;
        },
    
        dragLeave(event) {
            if (event.relatedTarget && event.currentTarget.contains(event.relatedTarget)) return;
            this.dragColumn = null;
        },
    
        async drop(event, column) {
            event.preventDefault();
            event.stopPropagation();
    
            if (this.moving || !this.dragging) return;
    
            const ticketId = event.dataTransfer.getData('ticket-id') || event.dataTransfer.getData('text/plain');
            if (!ticketId) return;
    
            this.moving = true;
            this.dragging = false;
            this.dragColumn = null;
    
            try {
                await $wire.call('moveTicket', Number(ticketId), column);
                this.$dispatch('ticket-moved');
            } catch (error) {
                console.error('Unable to move ticket:', error);
            } finally {
                this.moving = false;
                this.draggingTicket = null;
                this.dragColumn = null;
                // Cleanup any stuck dragging classes
                document.querySelectorAll('.is-dragging').forEach(el => el.classList.remove('is-dragging'));
            }
        }
    }" x-on:ticket-moved.window="moving = false" :class="{ 'light': isLightMode }"
        class="cc-tickets relative min-h-screen w-full overflow-hidden transition-colors duration-500 flex flex-col">

        <!-- Noise Texture -->
        <div class="noise"></div>

        {{-- *Header & Filters (Glassmorphism)* --}}
        <div class="glass-panel z-10 sticky top-0">
            {{-- Header --}}
            <div class="px-6 py-6 sm:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-white flex items-center gap-3">
                            Tickets
                            <span
                                class="inline-flex items-center rounded-full bg-[#ADD322]/10 px-2.5 py-0.5 text-xs font-medium text-[#ADD322] ring-1 ring-inset ring-[#ADD322]/20">
                                Support
                            </span>
                        </h1>
                        <p class="mt-2 text-sm text-white/50">
                            Manage, track, and resolve your team's conversations smoothly.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="border-t border-white/5 px-6 py-4 sm:px-8">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex flex-1 flex-col gap-4 sm:flex-row">
                        {{-- Search --}}
                        <div class="relative min-w-0 flex-1 sm:max-w-md group">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-white/40 transition group-focus-within:text-[#CCA3FF]"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                            <input type="search" wire:model.live.debounce.300ms="search"
                                placeholder="Search tickets..."
                                class="cc-input w-full rounded-xl py-2.5 pl-10 pr-4 text-sm" />
                        </div>

                        {{-- Priority --}}
                        <select wire:model.live="priority"
                            class="cc-input rounded-xl px-4 py-2.5 text-sm cursor-pointer">
                            <option value="all" class="bg-[#12101a] text-white">All priorities</option>
                            <option value="low" class="bg-[#12101a] text-white">Low</option>
                            <option value="normal" class="bg-[#12101a] text-white">Normal</option>
                            <option value="high" class="bg-[#12101a] text-white">High</option>
                            <option value="urgent" class="bg-[#12101a] text-white">Urgent</option>
                        </select>

                        {{-- Sort --}}
                        <select wire:model.live="sort" class="cc-input rounded-xl px-4 py-2.5 text-sm cursor-pointer">
                            <option value="latest" class="bg-[#12101a] text-white">Latest activity</option>
                            <option value="oldest" class="bg-[#12101a] text-white">Oldest activity</option>
                            <option value="priority_urgent" class="bg-[#12101a] text-white">Urgent first</option>
                            <option value="priority_high" class="bg-[#12101a] text-white">High first</option>
                            <option value="priority_normal" class="bg-[#12101a] text-white">Normal first</option>
                            <option value="priority_low" class="bg-[#12101a] text-white">Low first</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Status tabs --}}
            <div class="border-t border-white/5 px-6 sm:px-8">
                <div class="cc-scrollbar flex gap-2 overflow-x-auto pb-px">
                    @php
                        $statuses = [
                            'all' => 'All',
                            'open' => 'Open',
                            'pending' => 'Pending',
                            'resolved' => 'Resolved',
                            'closed' => 'Closed',
                        ];
                    @endphp
                    @foreach ($statuses as $statusKey => $statusLabel)
                        <button type="button" wire:click="$set('status', '{{ $statusKey }}')"
                            class="relative shrink-0 px-4 py-3.5 text-sm font-medium transition-colors hover:text-white group {{ $status === $statusKey ? 'text-white' : 'text-white/40' }}">
                            {{ $statusLabel }}
                            <span
                                class="ml-2 rounded-full px-2 py-0.5 text-xs transition-colors {{ $status === $statusKey ? 'bg-[#963c96] text-white' : 'bg-white/5 text-white/40 group-hover:bg-white/10' }}">
                                {{ $counts[$statusKey] ?? 0 }}
                            </span>
                            @if ($status === $statusKey)
                                <span
                                    class="absolute inset-x-2 bottom-0 h-0.5 rounded-t-full bg-gradient-to-r from-[#963c96] to-[#CCA3FF] shadow-[0_0_10px_rgba(204,163,255,0.5)]"></span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- *Kanban Board* --}}
        <div class="cc-scrollbar flex-1 overflow-x-auto p-6 sm:p-8">
            @php
                $columns = [
                    'open' => [
                        'label' => 'Open',
                        'description' => 'New and active tickets',
                        'dot' => 'bg-[#CCA3FF] shadow-[0_0_10px_rgba(204,163,255,0.6)]',
                    ],
                    'pending' => [
                        'label' => 'Pending',
                        'description' => 'Waiting for a response',
                        'dot' => 'bg-[#ADD322] shadow-[0_0_10px_rgba(173,211,34,0.6)]',
                    ],
                    'resolved' => [
                        'label' => 'Resolved',
                        'description' => 'Recently resolved',
                        'dot' => 'bg-[#ccfef4] shadow-[0_0_10px_rgba(204,254,244,0.6)]',
                    ],
                    'closed' => [
                        'label' => 'Closed',
                        'description' => 'Completed tickets',
                        'dot' => 'bg-white/40',
                    ],
                ];
            @endphp

            <div class="flex min-w-[1000px] gap-6 xl:grid xl:min-w-0 xl:grid-cols-4 h-full">
                @foreach ($columns as $column => $meta)
                    @php
                        $columnTickets = $tickets->where('status', $column)->values();
                    @endphp
                    <section wire:key="kanban-column-{{ $column }}"
                        class="kanban-column flex w-80 shrink-0 flex-col overflow-hidden rounded-2xl xl:w-auto h-full max-h-full"
                        :class="{ 'drag-over': dragColumn === '{{ $column }}' }">

                        {{-- Column header --}}
                        <div class="border-b border-white/5 px-5 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="size-2.5 rounded-full {{ $meta['dot'] }}"></span>
                                    <h2 class="text-sm font-semibold tracking-wide text-white uppercase">
                                        {{ $meta['label'] }}
                                    </h2>
                                </div>
                                <span
                                    class="rounded-full bg-white/10 px-2.5 py-0.5 text-xs font-medium text-white/70 backdrop-blur-md">
                                    {{ $columnTickets->count() }}
                                </span>
                            </div>
                            <p class="mt-1.5 text-xs text-white/40">
                                {{ $meta['description'] }}
                            </p>
                        </div>

                        {{-- Drop zone / Cards container --}}
                        <div class="cc-scrollbar relative flex flex-1 flex-col gap-4 overflow-y-auto p-4 transition-colors"
                            x-on:dragover.prevent="dragOver($event, '{{ $column }}')"
                            x-on:dragenter.prevent="dragEnter($event, '{{ $column }}')"
                            x-on:dragleave="dragLeave($event)"
                            x-on:drop.prevent.stop="drop($event, '{{ $column }}')">

                            @forelse($columnTickets as $ticket)
                                <div wire:key="ticket-{{ $ticket->id }}" draggable="true"
                                    x-on:dragstart="startDrag($event, '{{ $ticket->id }}')"
                                    x-on:dragend="endDrag($event)"
                                    class="ticket-card group cursor-grab rounded-xl p-5 active:cursor-grabbing">

                                    {{-- Ticket number / priority --}}
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="font-mono text-[11px] font-medium text-white/40">
                                            #{{ $ticket->ticket_number }}
                                        </span>
                                        @php
                                            $priorityClasses = [
                                                'low' => 'bg-slate-500/20 text-slate-300 ring-slate-500/30',
                                                'medium' => 'bg-blue-500/20 text-blue-300 ring-blue-500/30',
                                                'high' => 'bg-orange-500/20 text-orange-300 ring-orange-500/30',
                                                'urgent' => 'bg-red-500/20 text-red-300 ring-red-500/30',
                                            ];
                                        @endphp
                                        <span
                                            class="priority-badge rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset {{ $priorityClasses[$ticket->priority] ?? 'bg-white/10 text-white/60 ring-white/20' }}">
                                            {{ $ticket->priority }}
                                        </span>
                                    </div>

                                    {{-- Subject --}}
                                    <div class="mt-3.5">
                                        <h3
                                            class="line-clamp-2 text-sm font-medium leading-relaxed text-white group-hover:text-[#CCA3FF] transition-colors duration-300">
                                            {{ $ticket->subject }}
                                        </h3>
                                        <a href="{{ route('team.tickets.show', [
                                            'current_team' => $team->slug,
                                            'ticket' => $ticket->ticket_number,
                                        ]) }}"
                                            wire:navigate draggable="false" @click.stop
                                            class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-medium text-[#ADD322] transition-colors hover:text-[#c3e83a] hover:underline">
                                            View ticket
                                            <span aria-hidden="true"
                                                class="transition-transform group-hover:translate-x-1">→</span>
                                        </a>
                                    </div>

                                    {{-- Customer --}}
                                    <div class="mt-4 flex items-center gap-3">
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-white/10 to-white/5 text-xs font-semibold text-white shadow-inner ring-1 ring-white/10">
                                            {{ strtoupper(substr($ticket->name ?: $ticket->email, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-medium text-white/80">
                                                {{ $ticket->name ?: 'Unknown customer' }}
                                            </p>
                                            <p class="truncate text-[11px] text-white/40">
                                                {{ $ticket->email }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Footer --}}
                                    <div class="mt-5 flex items-center justify-between border-t border-white/5 pt-3">
                                        <div class="flex items-center gap-3 text-[11px] font-medium text-white/40">
                                            <span class="flex items-center gap-1.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path
                                                        d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" />
                                                </svg>
                                                {{ $ticket->messages_count }}
                                            </span>
                                            <span class="flex items-center gap-1.5">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <polyline points="12 6 12 12 16 14" />
                                                </svg>
                                                {{ $ticket->last_activity_at?->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                {{-- Empty column --}}
                                <div class="flex min-h-[200px] flex-1 items-center justify-center rounded-xl border border-dashed border-white/10 bg-white/[0.01] text-center transition-all duration-300"
                                    :class="{
                                        'border-[#CCA3FF]/50 bg-[#963c96]/10 scale-[0.98]': dragColumn === '{{ $column }}'
                                    }">
                                    <div>
                                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-white/5 ring-1 ring-white/10 transition-transform duration-300"
                                            :class="{ 'scale-110 bg-[#963c96]/20 ring-[#CCA3FF]/40': dragColumn === '{{ $column }}' }">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-white/30"
                                                :class="{ 'text-[#CCA3FF]': dragColumn === '{{ $column }}' }"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <rect width="18" height="18" x="3" y="3" rx="2" />
                                                <path d="M3 9h18" />
                                            </svg>
                                        </div>
                                        <p class="mt-4 text-xs font-medium text-white/30"
                                            :class="{ 'text-[#CCA3FF]/70': dragColumn === '{{ $column }}' }">
                                            Drop tickets here
                                        </p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</div>
