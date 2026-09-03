<div
    x-data="{
        dragging: false,
        draggingTicket: null,
        dragColumn: null,
        moving: false,

        startDrag(event, ticketId) {
            this.dragging = true;
            this.draggingTicket = String(ticketId);

            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', String(ticketId));
            event.dataTransfer.setData('ticket-id', String(ticketId));

            event.currentTarget.classList.add(
                'opacity-40',
                'scale-[0.97]',
                'rotate-1'
            );
        },

        endDrag(event) {
            this.dragging = false;
            this.draggingTicket = null;
            this.dragColumn = null;

            event.currentTarget.classList.remove(
                'opacity-40',
                'scale-[0.97]',
                'rotate-1'
            );
        },

        dragOver(event, column) {
            event.preventDefault();

            if (!this.dragging || this.moving) {
                return;
            }

            event.dataTransfer.dropEffect = 'move';
            this.dragColumn = column;
        },

        dragEnter(event, column) {
            event.preventDefault();

            if (!this.dragging || this.moving) {
                return;
            }

            this.dragColumn = column;
        },

        dragLeave(event) {
            if (
                event.relatedTarget &&
                event.currentTarget.contains(event.relatedTarget)
            ) {
                return;
            }

            this.dragColumn = null;
        },

        async drop(event, column) {
            event.preventDefault();
            event.stopPropagation();

            if (this.moving || !this.dragging) {
                return;
            }

            const ticketId =
                event.dataTransfer.getData('ticket-id') ||
                event.dataTransfer.getData('text/plain');

            if (!ticketId) {
                return;
            }

            this.moving = true;
            this.dragging = false;
            this.dragColumn = null;

            try {
                await $wire.call(
                    'moveTicket',
                    Number(ticketId),
                    column
                );

                this.$dispatch('ticket-moved');
            } catch (error) {
                console.error('Unable to move ticket:', error);
            } finally {
                this.moving = false;
                this.draggingTicket = null;
                this.dragColumn = null;
            }
        }
    }"
    x-on:ticket-moved.window="moving = false"
    class="min-h-full overflow-hidden bg-[#09070f] text-white"
>
    {{-- Header --}}
    <div class="border-b border-white/10 px-6 py-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Tickets
                </h1>

                <p class="mt-1 text-sm text-white/50">
                    Manage and track your team's support tickets.
                </p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="border-b border-white/10 px-6 py-4">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex flex-1 flex-col gap-3 sm:flex-row">

                {{-- Search --}}
                <div class="relative min-w-0 flex-1 sm:max-w-md">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-white/40"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>

                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search tickets..."
                        class="w-full rounded-xl border border-white/10 bg-white/[0.04] py-2.5 pl-10 pr-4 text-sm text-white placeholder:text-white/30 outline-none transition focus:border-violet-500/50 focus:ring-1 focus:ring-violet-500/30"
                    />
                </div>

                {{-- Priority --}}
                <select
                    wire:model.live="priority"
                    class="rounded-xl border border-white/10 bg-white/[0.04] px-4 py-2.5 text-sm text-white outline-none focus:border-violet-500/50"
                >
                    <option value="all" class="bg-[#12101a]">
                        All priorities
                    </option>

                    <option value="low" class="bg-[#12101a]">
                        Low
                    </option>

                    <option value="medium" class="bg-[#12101a]">
                        Medium
                    </option>

                    <option value="high" class="bg-[#12101a]">
                        High
                    </option>

                    <option value="urgent" class="bg-[#12101a]">
                        Urgent
                    </option>
                </select>

                {{-- Sort --}}
                <select
                    wire:model.live="sort"
                    class="rounded-xl border border-white/10 bg-white/[0.04] px-4 py-2.5 text-sm text-white outline-none focus:border-violet-500/50"
                >
                    <option value="latest" class="bg-[#12101a]">
                        Latest activity
                    </option>

                    <option value="oldest" class="bg-[#12101a]">
                        Oldest activity
                    </option>
                </select>
            </div>
        </div>
    </div>

    {{-- Status tabs --}}
    <div class="border-b border-white/10 px-6">
        <div class="flex gap-1 overflow-x-auto">
            @php
                $statuses = [
                    'all' => 'All',
                    'open' => 'Open',
                    'pending' => 'Pending',
                    'resolved' => 'Resolved',
                    'closed' => 'Closed',
                ];
            @endphp

            @foreach($statuses as $statusKey => $statusLabel)
                <button
                    type="button"
                    wire:click="$set('status', '{{ $statusKey }}')"
                    class="relative shrink-0 px-4 py-3 text-sm font-medium transition {{ $status === $statusKey ? 'text-white' : 'text-white/40 hover:text-white/70' }}"
                >
                    {{ $statusLabel }}

                    <span class="ml-1.5 text-xs text-white/30">
                        {{ $counts[$statusKey] ?? 0 }}
                    </span>

                    @if($status === $statusKey)
                        <span class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-violet-500"></span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Kanban --}}
    <div class="overflow-x-auto p-6">
        @php
            $columns = [
                'open' => [
                    'label' => 'Open',
                    'description' => 'New and active tickets',
                    'dot' => 'bg-blue-400',
                ],
                'pending' => [
                    'label' => 'Pending',
                    'description' => 'Waiting for a response',
                    'dot' => 'bg-amber-400',
                ],
                'resolved' => [
                    'label' => 'Resolved',
                    'description' => 'Recently resolved',
                    'dot' => 'bg-emerald-400',
                ],
                'closed' => [
                    'label' => 'Closed',
                    'description' => 'Completed tickets',
                    'dot' => 'bg-white/40',
                ],
            ];
        @endphp

        <div wire:replace class="grid min-w-[1200px] grid-cols-4 gap-4">
            @foreach($columns as $column => $meta)

                @php
                    $columnTickets = $tickets->where('status', $column);
                @endphp

                <section
                    wire:key="kanban-column-{{ $column }}"
                    class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-white/10 bg-white/[0.025]"
                >
                    {{-- Column header --}}
                    <div class="border-b border-white/10 px-4 py-4">
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 rounded-full {{ $meta['dot'] }}"></span>

                            <h2 class="text-sm font-semibold text-white">
                                {{ $meta['label'] }}
                            </h2>

                            <span class="rounded-full bg-white/[0.06] px-2 py-0.5 text-xs text-white/50">
                                {{ $columnTickets->count() }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-white/30">
                            {{ $meta['description'] }}
                        </p>
                    </div>

                    {{-- Drop zone --}}
                    <div
                        class="relative flex min-h-[520px] flex-1 flex-col gap-3 p-3 transition"
                        x-on:dragover.prevent="dragOver($event, '{{ $column }}')"
                        x-on:dragenter.prevent="dragEnter($event, '{{ $column }}')"
                        x-on:dragleave="dragLeave($event)"
                        x-on:drop.prevent.stop="drop($event, '{{ $column }}')"
                        :class="{
                            'bg-violet-500/[0.08] ring-1 ring-inset ring-violet-400/30':
                                dragColumn === '{{ $column }}'
                        }"
                    >

                        @forelse($columnTickets as $ticket)

                            <div
                                wire:key="ticket-{{ $ticket->id }}"
                                draggable="true"
                                x-on:dragstart="startDrag($event, '{{ $ticket->id }}')"
                                x-on:dragend="endDrag($event)"
                                class="group cursor-grab rounded-xl border border-white/10 bg-[#12101a] p-4 shadow-sm transition hover:border-white/20 hover:bg-[#171420] active:cursor-grabbing"
                                :class="{
                                    'opacity-40 scale-[0.97] rotate-1':
                                        draggingTicket === '{{ $ticket->id }}'
                                }"
                            >
                                {{-- Ticket number / priority --}}
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-mono text-xs text-white/35">
                                        #{{ $ticket->ticket_number }}
                                    </span>

                                    @php
                                        $priorityClasses = [
                                            'low' => 'bg-slate-400/10 text-slate-300',
                                            'medium' => 'bg-blue-400/10 text-blue-300',
                                            'high' => 'bg-orange-400/10 text-orange-300',
                                            'urgent' => 'bg-red-400/10 text-red-300',
                                        ];
                                    @endphp

                                    <span
                                        class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $priorityClasses[$ticket->priority] ?? 'bg-white/10 text-white/50' }}"
                                    >
                                        {{ $ticket->priority }}
                                    </span>
                                </div>

                                {{-- Subject --}}
                                <div class="mt-3">
                                    <div class="mt-3">
                                    <h3 class="line-clamp-2 text-sm font-medium leading-5 text-white">
                                        {{ $ticket->subject }}
                                    </h3>

                                    <a
                                        href="{{ route('team.tickets.show', [
                                            'current_team' => $team->slug,
                                            'ticket' => $ticket->ticket_number,
                                        ]) }}"
                                        wire:navigate
                                        draggable="false"
                                        @click.stop
                                        class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-violet-400 transition hover:text-violet-300 hover:underline"
                                    >
                                        View ticket
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </div>

                                {{-- Customer --}}
                                <div class="mt-3 flex items-center gap-2">
                                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-white/[0.06] text-xs font-semibold text-white/50">
                                        {{ strtoupper(substr($ticket->name ?: $ticket->email, 0, 1)) }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-medium text-white/70">
                                            {{ $ticket->name ?: 'Unknown customer' }}
                                        </p>

                                        <p class="truncate text-[11px] text-white/30">
                                            {{ $ticket->email }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Footer --}}
                                <div class="mt-4 flex items-center justify-between border-t border-white/[0.06] pt-3">
                                    <div class="flex items-center gap-3 text-[11px] text-white/30">
                                        <span class="inline-flex items-center gap-1">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="size-3.5"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z" />
                                            </svg>

                                            {{ $ticket->messages_count }}
                                        </span>

                                        <span>
                                            {{ $ticket->last_activity_at?->diffForHumans() }}
                                        </span>
                                    </div>

                                    <span class="size-1.5 rounded-full {{ $meta['dot'] }}"></span>
                                </div>
                            </div>

                        @empty

                            {{-- Empty column --}}
                            <div
                                class="flex min-h-[180px] flex-1 items-center justify-center rounded-xl border border-dashed border-white/10 text-center transition"
                                :class="{
                                    'border-violet-400/30 bg-violet-500/[0.04]':
                                        dragColumn === '{{ $column }}'
                                }"
                            >
                                <div>
                                    <div class="mx-auto flex size-10 items-center justify-center rounded-full bg-white/[0.04]">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="size-5 text-white/20"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                        >
                                            <rect
                                                width="18"
                                                height="18"
                                                x="3"
                                                y="3"
                                                rx="2"
                                            />

                                            <path d="M3 9h18" />
                                        </svg>
                                    </div>

                                    <p class="mt-3 text-xs text-white/25">
                                        No tickets
                                    </p>
                                </div>
                            </div>

                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    {{-- Pagination --}}
    @if($tickets->hasPages())
        <div class="border-t border-white/10 px-6 py-4">
            {{ $tickets->links() }}
        </div>
    @endif
</div>