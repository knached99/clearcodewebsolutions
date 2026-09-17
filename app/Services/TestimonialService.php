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
    /**
     * Create a pending testimonial.
     *
     * Database work is completed before synchronous emails are sent.
     */
    public function create(array $data): Testimonials
    {
        $testimonial = DB::transaction(function () use ($data): Testimonials {
            return Testimonials::query()->create([
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
         * Synchronous email to the person who submitted
         * the testimonial.
         */
        if (filled($testimonial->email)) {
            Notification::route('mail', $testimonial->email)
                ->notify(
                    new TestimonialSubmittedNotification($testimonial)
                );
        }

        /*
         * Synchronous notification to all administrators/users.
         *
         * Keep this after the transaction so a mail failure
         * cannot roll back the testimonial itself.
         */
        $admins = User::query()
            ->whereNotNull('email')
            ->where('email', '<>', '')
            ->get(['id', 'name', 'email']);

        if ($admins->isNotEmpty()) {
            Notification::send(
                $admins,
                new NewTestimonialSubmittedNotification($testimonial)
            );
        }

        return $testimonial;
    }

    /**
     * Approve and publish a testimonial.
     *
     * The approval notification is sent only when the testimonial
     * transitions from a non-approved state to approved.
     */
    public function approve(Testimonials $testimonial): Testimonials
    {
        $result = DB::transaction(function () use ($testimonial): array {
            $testimonial = Testimonials::query()
                ->whereKey($testimonial->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $wasAlreadyApproved = $testimonial->isApproved();

            $testimonial->update([
                'status' => Testimonials::STATUS_APPROVED,
                'is_published' => true,
                'published_at' => $testimonial->published_at ?? now(),
                'moderated_at' => now(),
            ]);

            return [
                'testimonial' => $testimonial->fresh(),
                'wasAlreadyApproved' => $wasAlreadyApproved,
            ];
        });

        $testimonial = $result['testimonial'];

        if (
            ! $result['wasAlreadyApproved']
            && filled($testimonial->email)
        ) {
            Notification::route('mail', $testimonial->email)
                ->notify(
                    new TestimonialApprovedNotification($testimonial)
                );
        }

        return $testimonial;
    }

    /**
     * Deny a testimonial and remove it from publication.
     */
    public function deny(Testimonials $testimonial): Testimonials
    {
        return DB::transaction(function () use ($testimonial): Testimonials {
            $testimonial = Testimonials::query()
                ->whereKey($testimonial->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $testimonial->update([
                'status' => Testimonials::STATUS_DENIED,
                'is_published' => false,
                'published_at' => null,
                'moderated_at' => now(),
            ]);

            return $testimonial->fresh();
        });
    }

    /**
     * Permanently delete a testimonial.
     */
    public function delete(Testimonials $testimonial): void
    {
        DB::transaction(function () use ($testimonial): void {
            $testimonial = Testimonials::query()
                ->whereKey($testimonial->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $testimonial->delete();
        });
    }

    /**
     * Publish a testimonial.
     */
    public function publish(Testimonials $testimonial): Testimonials
    {
        return $this->approve($testimonial);
    }

    /**
     * Remove a testimonial from the public website
     * without changing its moderation status.
     */
    public function unpublish(Testimonials $testimonial): Testimonials
    {
        return DB::transaction(function () use ($testimonial): Testimonials {
            $testimonial = Testimonials::query()
                ->whereKey($testimonial->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $testimonial->update([
                'is_published' => false,
                'published_at' => null,
            ]);

            return $testimonial->fresh();
        });
    }

    /**
     * Toggle the featured state.
     */
    public function toggleFeatured(Testimonials $testimonial): Testimonials
    {
        return DB::transaction(function () use ($testimonial): Testimonials {
            $testimonial = Testimonials::query()
                ->whereKey($testimonial->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $testimonial->update([
                'is_featured' => ! $testimonial->is_featured,
            ]);

            return $testimonial->fresh();
        });
    }
}
