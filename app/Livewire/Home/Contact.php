<?php

namespace App\Livewire\Home;

use App\Services\TicketService;
use App\Notifications\NewTicketNotification;
use App\Notifications\TicketReceivedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Contact extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public string $website = '';

    public int $formLoadedAt = 0;

    public bool $submitted = false;

    public ?string $ticketNumber = null;

    public function mount(): void
    {
        $this->formLoadedAt = now()->timestamp;
    }

  protected function rules(): array
{
    return [
        'name' => [
            'required',
            'string',
            'min:2',
            'max:80',
            'regex:/^[\pL\pM\s\'\-.]+$/u',
        ],

        'email' => [
            'required',
            'string',
            'lowercase',
            'max:254',
            'email:rfc,dns,spoof',
        ],

        'subject' => [
            'required',
            'string',
            'in:Website Design,Website Development,Mobile App Development,E-Commerce,Website Redesign,Maintenance & Support,Other',
        ],

        'message' => [
            'required',
            'string',
            'min:20',
            'max:5000',
        ],

        'website' => [
            'prohibited',
        ],
    ];
}

    public function updated($property): void
    {
        $rules = $this->typingRules($property);

        if ($rules === []) {
            return;
        }

        $this->validateOnly($property, [
            $property => $rules,
        ]);
    }

    protected function typingRules(string $property): array
    {
        return match ($property) {
            'name' => [
                'required',
                'string',
                'min:2',
                'max:80',
                'regex:/^[\pL\pM\s\'\-.]+$/u',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'max:254',
                'email:rfc',
            ],

            'subject' => [
                'required',
                'string',
                'in:Website Design,Website Development,Mobile App Development,E-Commerce,Website Redesign,Maintenance & Support,Other'
            ],

            'message' => [
                'required',
                'string',
                'min:20',
                'max:5000',
            ],

            default => [],
        };
    }

    public function submit(TicketService $ticketService): void
    {
        $this->submitted = false;


        if ($this->website !== '') {
            return;
        }


        if (
            $this->formLoadedAt &&
            now()->timestamp - $this->formLoadedAt < 3
        ) {
            throw ValidationException::withMessages([
                'message' =>
                    'Please take a moment to review your message.',
            ]);
        }


        $key = 'contact-form:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' =>
                    "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        RateLimiter::hit($key, 60);


        $validated = $this->validate();


        $result = $ticketService->createTicket(
            $validated
        );

        $ticket = $result['ticket'];

        $token = $result['token'];


        // Customer email
        Notification::route(
            'mail',
            $ticket->email
        )->notify(
            new TicketReceivedNotification(
                $ticket,
                $token
            )
        );

        // Admin email
        Notification::route(
            'mail',
            config('mail.admin_address')
        )->notify(
            new NewTicketNotification($ticket)
        );


        $this->ticketNumber = $ticket->ticket_number;

        $this->reset([
            'name',
            'email',
            'subject',
            'message',
            'website',
        ]);

        $this->submitted = true;

        $this->formLoadedAt = now()->timestamp;
    }

    public function render()
    {
        return view('livewire.home.contact');
    }
}