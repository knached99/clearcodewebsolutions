@extends('layouts.home_layout')

@section('title', 'Ticket ' . $ticket->ticket_number)

@section('description', 'View your support ticket and conversation with Clear Code Web Solutions.')

@section('content')

    <main class="ticket-page">
        <div class="ticket-shell">
            {{-- 
            <a href="{{ url('/') }}" class="ticket-back">
                <flux:icon.arrow-left class="size-4" />
                Back to website
            </a> --}}

            <livewire:home.tickets.guest-ticket :ticket="$ticket" wire:key="guest-ticket-{{ $ticket->id }}" />

        </div>
    </main>


@endsection
