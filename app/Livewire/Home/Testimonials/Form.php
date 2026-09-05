<?php

namespace App\Livewire\Home\Testimonials;

use App\Services\TestimonialService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Form extends Component
{
    public string $name = '';

    public string $email = '';

    public string $company = '';

    public string $role = '';

    public string $message = '';

    public int $rating = 0;

    /**
     * Honeypot field.
     *
     * Real users never see this.
     */
    public string $website = '';

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'company' => [
                'nullable',
                'string',
                'max:150',
            ],

            'role' => [
                'nullable',
                'string',
                'max:150',
            ],

            'message' => [
                'required',
                'string',
                'min:20',
                'max:2000',
            ],

            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],

            'website' => [
                'nullable',
                'string',
                'max:0',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Please tell us your name.',

            'email.email' => 'Please enter a valid email address.',

            'message.required' => 'Please share your experience with us.',

            'message.min' => 'Your testimonial should be at least 20 characters.',

            'message.max' => 'Your testimonial is too long.',

            'rating.required' => 'Please select a rating.',

            'rating.min' => 'Please select a rating.',

            'rating.max' => 'Please select a rating.',
        ];
    }

    public function setRating(int $rating): void
    {
        if ($rating < 1 || $rating > 5) {
            return;
        }

        $this->rating = $rating;
        $this->resetValidation('rating');
    }

    public function submit(TestimonialService $service): void
    {
        /*
         * Honeypot.
         *
         * Bots that fill the hidden website field are silently ignored.
         */
        if (filled($this->website)) {
            $this->resetForm();

            $this->submitted = true;

            return;
        }

        /*
         * Simple public submission rate limit.
         *
         * Three submissions per hour per IP.
         */
        $key = 'testimonial-submission:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError(
                'form',
                'Too many submissions. Please try again later.'
            );

            return;
        }

        RateLimiter::hit($key, 3600);

        $validated = $this->validate();

        $service->create($validated);

        $this->resetForm();

        $this->submitted = true;

        $this->dispatch(
            'testimonial-submitted'
        );
    }

    protected function resetForm(): void
    {
        $this->reset([
            'name',
            'email',
            'company',
            'role',
            'message',
            'website',
        ]);

        $this->rating = 0;
    }

    public function render()
    {
        return view(
            'livewire.home.testimonials.form'
        );
    }
}