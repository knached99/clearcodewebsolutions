<div
    x-data="ticketKanban()"
    x-on:ticket-moved.window="moving = false"
    class="min-h-full overflow-hidden bg-[#09070f] text-white"
>
    {{-- Ambient background --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -left-40 -top-40 h-[500px] w-[500px] rounded-full bg-violet-600/20 blur-[140px]"></div>
        <div class="absolute right-[-180px] top-[15%] h-[500px] w-[500px] rounded-full bg-fuchsia-600/10 blur-[150px]"></div>
        <div class="absolute bottom-[-200px] left-[35%] h-[500px] w-[500px] rounded-full bg-purple-700/10 blur-[160px]"></div>
    </div>

    <div class="relative mx-auto max-w-[1900px] px-4 py-5 sm:px-6 lg:px-8">

        {{-- Header --}}
        <header class="mb-7">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">

                <div>
                    <div class="mb-3 flex items-center gap-2">
                        <div class="flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-500 shadow-lg shadow-violet-500/20">
                            <flux:icon.chat-bubble-left-right class="size-4 text-white" />
                        </div>

                        <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-violet-300/80">
                            Customer Support
                        </span>
                    </div>

                    <h1 class="text-3xl font-semibold tracking-[-0.03em] text-white sm:text-4xl">
                        Support inbox
                    </h1>

                    <p class="mt-2 max-w-xl text-sm leading-6 text-white/40">
                        Keep every customer conversation moving.
                        Drag tickets between stages to update their status.
                    </p>
                </div>

                <div class="flex items-center gap-3">

                    {{-- Live indicator --}}
                    <div class="hidden items-center gap-2 rounded-full border border-white/10 bg-white/[0.04] px-4 py-2.5 text-xs text-white/50 sm:flex">
                        <span class="relative flex size-2">
                            <span class="absolute inset-0 animate-ping rounded-full bg-emerald-400/70"></span>
                            <span class="relative size-2 rounded-full bg-emerald-400"></span>
                        </span>

                        Live inbox
                    </div>

                    <div class="rounded-2xl border border-violet-400/20 bg-gradient-to-r from-violet-500/10 to-fuchsia-500/10 px-4 py-2.5">
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-white/30">
                            Total tickets
                        </div>

                        <div class="mt-0.5 text-lg font-semibold text-white">
                            {{ $counts['all'] ?? $tickets->total() ?? 0 }}
                        </div>
                    </div>

                </div>
            </div>

            {{-- Toolbar --}}
            <div class="mt-7 flex flex-col gap-3 lg:flex-row lg:items-center">

                {{-- Search --}}
                <div class="relative flex-1 lg:max-w-xl">
                    <flux:icon.magnifying-glass
                        class="absolute left-4 top-1/2 size-4 -translate-y-1/2 text-white/25"
                    />

                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search tickets, customers, email..."
                        class="h-12 w-full rounded-2xl border border-white/10 bg-white/[0.045] pl-11 pr-4 text-sm text-white outline-none backdrop-blur-xl transition placeholder:text-white/25 focus:border-violet-400/40 focus:bg-white/[0.07] focus:ring-4 focus:ring-violet-500/10"
                    />
                </div>

                {{-- Sort --}}
                <select
                    wire:model.live="sort"
                    class="h-12 rounded-2xl border border-white/10 bg-white/[0.045] px-4 text-sm text-white/60 outline-none backdrop-blur-xl focus:border-violet-400/40"
                >
                    <option value="latest" class="bg-zinc-900">
                        Latest activity
                    </option>

                    <option value="oldest" class="bg-zinc-900">
                        Oldest
                    </option>
                </select>

                {{-- Priority --}}
                <select
                    wire:model.live="priority"
                    class="h-12 rounded-2xl border border-white/10 bg-white/[0.045] px-4 text-sm text-white/60 outline-none backdrop-blur-xl focus:border-violet-400/40"
                >
                    <option value="all" class="bg-zinc-900">
                        All priorities
                    </option>

                    <option value="urgent" class="bg-zinc-900">
                        Urgent
                    </option>

                    <option value="high" class="bg-zinc-900">
                        High
                    </option>

                    <option value="normal" class="bg-zinc-900">
                        Normal
                    </option>
                </select>

            </div>
        </header>

        {{-- Kanban --}}
        <div class="grid gap-4 overflow-x-auto pb-8 xl:grid-cols-4">

            @php
                $columns = [
                    'open' => [
                        'label' => 'Open',
                        'description' => 'Needs attention',
                        'glow' => 'from-emerald-500 to-teal-400',
                    ],

                    'pending' => [
                        'label' => 'Pending',
                        'description' => 'Waiting on reply',
                        'glow' => 'from-amber-500 to-orange-400',
                    ],

                    'resolved' => [
                        'label' => 'Resolved',
                        'description' => 'Issue solved',
                        'glow' => 'from-blue-500 to-cyan-400',
                    ],

                    'closed' => [
                        'label' => 'Closed',
                        'description' => 'Completed',
                        'glow' => 'from-zinc-500 to-zinc-400',
                    ],
                ];
            @endphp

            @foreach($columns as $column => $meta)

                @php
                    $columnTickets = $tickets->where('status', $column);
                @endphp

                <section
                    wire:key="kanban-column-{{ $column }}"
                    class="group relative flex min-w-[290px] flex-col overflow-hidden rounded-[24px] border border-white/[0.08] bg-white/[0.035] p-3 shadow-2xl shadow-black/20 backdrop-blur-2xl"
                >

                    {{-- Column glow --}}
                    <div
                        class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b {{ $meta['glow'] }} opacity-[0.06] blur-2xl"
                    ></div>

                    {{-- Column header --}}
                    <div class="relative mb-3 px-2 pb-2 pt-1">

                        <div class="flex items-center justify-between">

                            <div class="flex items-center gap-3">

                                <div class="flex size-9 items-center justify-center rounded-xl border border-white/10 bg-white/[0.06]">

                                    @if($column === 'open')
                                        <flux:icon.sparkles class="size-4 text-emerald-400" />
                                    @elseif($column === 'pending')
                                        <flux:icon.clock class="size-4 text-amber-400" />
                                    @elseif($column === 'resolved')
                                        <flux:icon.check-circle class="size-4 text-blue-400" />
                                    @else
                                        <flux:icon.archive-box class="size-4 text-zinc-400" />
                                    @endif

                                </div>

                                <div>
                                    <div class="flex items-center gap-2">

                                        <h2 class="text-sm font-semibold text-white">
                                            {{ $meta['label'] }}
                                        </h2>

                                        <span class="rounded-full bg-white/[0.07] px-2 py-0.5 text-[10px] font-semibold text-white/40">
                                            {{ $columnTickets->count() }}
                                        </span>

                                    </div>

                                    <p class="mt-0.5 text-[10px] text-white/25">
                                        {{ $meta['description'] }}
                                    </p>
                                </div>

                            </div>

                            <div
                                class="size-2 rounded-full bg-gradient-to-r {{ $meta['glow'] }} shadow-lg"
                            ></div>

                        </div>

                    </div>

                    {{-- DROP ZONE --}}
                    <div
                        class="relative flex min-h-[520px] flex-1 flex-col gap-3 rounded-[18px] p-1 transition-all duration-200"
                        x-on:dragover.prevent="dragOver($event, '{{ $column }}')"
                        x-on:dragenter.prevent="dragEnter($event, '{{ $column }}')"
                        x-on:dragleave="dragLeave($event)"
                        x-on:drop.prevent.stop="drop($event, '{{ $column }}')"
                        :class="{
                            'bg-violet-500/[0.08] ring-1 ring-inset ring-violet-400/30': dragColumn === '{{ $column }}'
                        }"
                    >

                        @forelse($columnTickets as $ticket)

                            <div
                                wire:key="ticket-{{ $ticket->id }}-{{ $ticket->status }}"
                                draggable="true"

                                x-on:dragstart="
                                    startDrag($event, '{{ $ticket->id }}')
                                "

                                x-on:dragend="
                                    endDrag($event)
                                "

                                class="ticket-card group/card relative cursor-grab overflow-hidden rounded-[18px] border border-white/[0.08] bg-[#11101a]/90 p-4 shadow-lg shadow-black/10 transition-all duration-200 hover:-translate-y-0.5 hover:border-violet-400/25 hover:bg-[#151321] hover:shadow-xl hover:shadow-violet-950/20 active:cursor-grabbing"
                            >

                                {{-- Top --}}
                                <div class="flex items-center justify-between gap-3">

                                    <button
                                        type="button"
                                        draggable="false"
                                        x-on:click.stop="
                                            window.Livewire.navigate('{{ route('team.tickets.show', [
                                                'current_team' => request()->route('current_team'),
                                                'ticket' => $ticket->ticket_number,
                                            ]) }}')
                                        "
                                        class="cursor-pointer rounded-md border border-white/[0.06] bg-white/[0.035] px-2 py-1 font-mono text-[9px] font-medium tracking-wide text-white/35 transition hover:border-violet-400/20 hover:text-violet-300"
                                    >
                                        {{ $ticket->ticket_number }}
                                    </button>

                                    <span class="text-[9px] text-white/25">
                                        {{ $ticket->last_activity_at?->diffForHumans() }}
                                    </span>

                                </div>

                                {{-- Subject --}}
                                <button
                                    type="button"
                                    draggable="false"
                                    x-on:click.stop="
                                        window.Livewire.navigate('{{ route('team.tickets.show', [
                                            'current_team' => request()->route('current_team'),
                                            'ticket' => $ticket->ticket_number,
                                        ]) }}')
                                    "
                                    class="mt-3 block w-full cursor-pointer text-left"
                                >
                                    <h3 class="line-clamp-2 text-[13px] font-semibold leading-5 text-white/90 transition group-hover/card:text-white">
                                        {{ $ticket->subject }}
                                    </h3>
                                </button>

                                {{-- Customer --}}
                                <div class="mt-4 flex items-center gap-2.5">

                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500/20 to-fuchsia-500/20 text-[10px] font-bold text-violet-200 ring-1 ring-inset ring-white/10">
                                        {{ strtoupper(substr($ticket->name ?? '?', 0, 1)) }}
                                    </div>

                                    <div class="min-w-0">

                                        <div class="truncate text-[11px] font-medium text-white/65">
                                            {{ $ticket->name }}
                                        </div>

                                        <div class="truncate text-[10px] text-white/25">
                                            {{ $ticket->email }}
                                        </div>

                                    </div>

                                </div>

                                {{-- Footer --}}
                                <div class="mt-4 flex items-center justify-between border-t border-white/[0.06] pt-3">

                                    <div class="flex items-center gap-2">

                                        <span
                                            @class([
                                                'rounded-lg px-2 py-1 text-[9px] font-semibold',

                                                'bg-red-500/10 text-red-300 ring-1 ring-inset ring-red-400/10'
                                                    => $ticket->priority === 'urgent',

                                                'bg-orange-500/10 text-orange-300 ring-1 ring-inset ring-orange-400/10'
                                                    => $ticket->priority === 'high',

                                                'bg-white/[0.05] text-white/35 ring-1 ring-inset ring-white/[0.06]'
                                                    => !in_array($ticket->priority, ['urgent', 'high']),
                                            ])
                                        >
                                            {{ ucfirst($ticket->priority) }}
                                        </span>

                                        @if($ticket->messages_count)
                                            <span class="flex items-center gap-1 text-[9px] text-white/25">
                                                <flux:icon.chat-bubble-left class="size-3" />
                                                {{ $ticket->messages_count }}
                                            </span>
                                        @endif

                                    </div>

                                    <button
                                        type="button"
                                        draggable="false"
                                        x-on:click.stop="
                                            window.Livewire.navigate('{{ route('team.tickets.show', [
                                                'current_team' => request()->route('current_team'),
                                                'ticket' => $ticket->ticket_number,
                                            ]) }}')
                                        "
                                        class="flex size-7 cursor-pointer items-center justify-center rounded-lg bg-white/[0.035] text-white/20 transition hover:bg-violet-500/10 hover:text-violet-300"
                                    >
                                        <flux:icon.arrow-up-right class="size-3.5" />
                                    </button>

                                </div>

                            </div>

                        @empty

                            <div
                                class="flex min-h-[220px] flex-1 items-center justify-center rounded-[18px] border border-dashed border-white/[0.08]"
                            >
                                <div class="text-center">

                                    <div class="mx-auto flex size-11 items-center justify-center rounded-2xl border border-white/[0.06] bg-white/[0.035]">
                                        <flux:icon.inbox class="size-5 text-white/20" />
                                    </div>

                                    <p class="mt-3 text-xs font-medium text-white/30">
                                        Nothing here
                                    </p>

                                    <p class="mt-1 text-[10px] text-white/15">
                                        Drag a ticket into this stage
                                    </p>

                                </div>
                            </div>

                        @endforelse

                    </div>

                </section>

            @endforeach

        </div>

    </div>

    {{-- Dragging indicator --}}
    <div
        x-show="dragging"
        x-transition.opacity
        class="pointer-events-none fixed bottom-6 left-1/2 z-50 -translate-x-1/2"
    >
        <div class="flex items-center gap-3 rounded-2xl border border-violet-400/20 bg-[#171320]/95 px-5 py-3 shadow-2xl shadow-violet-950/40 backdrop-blur-xl">

            <div class="flex size-7 items-center justify-center rounded-lg bg-violet-500/15">
                <flux:icon.arrows-pointing-out class="size-3.5 text-violet-300" />
            </div>

            <span class="text-xs font-medium text-white/70">
                Drop into a column to change status
            </span>

        </div>
    </div>

    {{-- Toast --}}
    <div
        x-data="{ show: false }"
        x-on:ticket-moved.window="
            show = true;
            setTimeout(() => show = false, 2200)
        "
        class="pointer-events-none fixed bottom-6 right-6 z-50"
    >
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-2 opacity-0 scale-95"
            x-transition:enter-end="translate-y-0 opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 rounded-2xl border border-emerald-400/20 bg-[#121018]/95 px-4 py-3 shadow-2xl backdrop-blur-xl"
        >

            <div class="flex size-8 items-center justify-center rounded-xl bg-emerald-500/10">
                <flux:icon.check class="size-4 text-emerald-400" />
            </div>

            <div>
                <div class="text-xs font-semibold text-white">
                    Ticket updated
                </div>

                <div class="text-[10px] text-white/30">
                    Status changed successfully
                </div>
            </div>

        </div>
    </div>

    <script>
        function ticketKanban() {
            return {
                dragging: false,
                draggingTicket: null,
                dragColumn: null,
                moving: false,

                startDrag(event, ticketId) {
                    this.dragging = true;
                    this.draggingTicket = String(ticketId);

                    event.dataTransfer.effectAllowed = 'move';

                    event.dataTransfer.setData(
                        'text/plain',
                        String(ticketId)
                    );

                    event.dataTransfer.setData(
                        'ticket-id',
                        String(ticketId)
                    );

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

                    if (!this.dragging) {
                        return;
                    }

                    event.dataTransfer.dropEffect = 'move';

                    this.dragColumn = column;
                },

                dragEnter(event, column) {
                    event.preventDefault();

                    if (!this.dragging) {
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

                    if (this.moving) {
                        return;
                    }

                    const ticketId =
                        event.dataTransfer.getData('ticket-id') ||
                        event.dataTransfer.getData('text/plain');

                    if (!ticketId) {
                        return;
                    }

                    if (!this.dragging) {
                        return;
                    }

                    this.moving = true;
                    this.dragging = false;
                    this.dragColumn = null;

                    try {
                        /*
                         * IMPORTANT:
                         * Use Livewire's call API rather than relying on
                         * Alpine's expression parser to resolve the method.
                         */
                        await this.$wire.call(
                            'moveTicket',
                            Number(ticketId),
                            column
                        );

                        this.$dispatch('ticket-moved');

                    } catch (error) {
                        console.error(
                            'Unable to move ticket:',
                            error
                        );
                    } finally {
                        this.moving = false;
                        this.draggingTicket = null;
                        this.dragColumn = null;
                    }
                }
            };
        }
    </script>
</div>