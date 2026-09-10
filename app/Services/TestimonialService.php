<?php

namespace App\Services;

use App\Models\Testimonials;
use App\Models\User;
use App\Notifications\NewTestimonialSubmittedNotification;
use App\Notifications\TestimonialApprovedNotification;
use App\Notifications\TestimonialSubmittedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TestimonialService
{
    public function create(array $data): Testimonials
    {
        $testimonial = DB::transaction(function () use ($data) {
            return Testimonials::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'company' => $data['company'] ?? null,
                'role' => $data['role'] ?? null,
                'message' => $data['message'],
                'rating' => $data['rating'] ?? 5,
                'status' => Testimonials::STATUS_PENDING,
                'is_published' => false,
                'is_featured' => false,
                'sort_order' => 0,
                'published_at' => null,
                'moderated_at' => null,
            ]);
        });

        /*
         * Send confirmation directly to the person
         * who submitted the testimonial.
         */
        if (filled($testimonial->email)) {
            Notification::route(
                'mail',
                $testimonial->email
            )->notify(
                new TestimonialSubmittedNotification(
                    $testimonial
                )
            );
        }

        /*
         * Send notification to every user/admin.
         */
        $admins = User::query()
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->get(['id', 'name', 'email']);

        foreach ($admins as $admin) {

            Notification::route(
                'mail',
                $admin->email
            )->notify(
                new NewTestimonialSubmittedNotification(
                    $testimonial
                )
            );

        }

        return $testimonial;
    }

    public function approve(Testimonials $testimonial): Testimonials
    {
        $wasAlreadyApproved = $testimonial->isApproved();

        $testimonial->update([
            'status' => Testimonials::STATUS_APPROVED,
            'is_published' => true,
            'published_at' => $testimonial->published_at ?? now(),
            'moderated_at' => now(),
        ]);

        $testimonial->refresh();

        if (! $wasAlreadyApproved && filled($testimonial->email)) {
            Notification::route(
                'mail',
                $testimonial->email
            )->notify(
                new TestimonialApprovedNotification(
                    $testimonial
                )
            );
        }

        return $testimonial;
    }

    public function deny(Testimonials $testimonial): Testimonials
    {
        $testimonial->update([
            'status' => Testimonials::STATUS_DENIED,
            'is_published' => false,
            'published_at' => null,
            'moderated_at' => now(),
        ]);

        return $testimonial->refresh();
    }

    public function delete(Testimonials $testimonial): void
    {
        $testimonial->delete();
    }

    public function publish(Testimonials $testimonial): Testimonials
    {
        return $this->approve($testimonial);
    }

    public function unpublish(Testimonials $testimonial): Testimonials
    {
        $testimonial->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        return $testimonial->refresh();
    }

    public function toggleFeatured(
        Testimonials $testimonial
    ): Testimonials {
        $testimonial->update([
            'is_featured' => ! $testimonial->is_featured,
        ]);

        return $testimonial->refresh();
    }
}
