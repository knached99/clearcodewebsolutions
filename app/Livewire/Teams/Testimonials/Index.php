<?php

namespace App\Livewire\Teams\Testimonials;

use App\Models\Testimonials;
use App\Services\TestimonialService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'all';

    public string $search = '';

    public ?int $selectedTestimonialId = null;

    public ?string $pendingAction = null;

    public bool $showActionModal = false;

    public ?int $highlightedTestimonialId = null;

    protected int $perPage = 10;

    public function mount(?Testimonials $testimonial = null): void
    {
        /*
         * Only users with testimonial:view can access
         * the testimonials page at all.
         */
        $this->authorize(
            'viewAny',
            Testimonials::class
        );

        if (! $testimonial) {
            return;
        }

        /*
         * Direct links from email always show the requested
         * testimonial regardless of whether it is pending,
         * approved, or denied.
         */
        $this->highlightedTestimonialId = $testimonial->id;

        $this->status = 'all';

        /*
         * Find the pagination page containing this testimonial.
         */
        $ids = Testimonials::query()
            ->latest()
            ->pluck('id')
            ->values();

        $index = $ids->search($testimonial->id);

        if ($index !== false) {
            $page = (int) floor($index / $this->perPage) + 1;

            $this->setPage($page);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /*
     * Generic action used by the current modal.
     */
    public function confirmAction(
        int $testimonialId,
        string $action
    ): void {
        if (! in_array($action, [
            'approve',
            'deny',
            'delete',
        ], true)) {
            return;
        }

        $this->selectedTestimonialId = $testimonialId;

        $this->pendingAction = $action;

        $this->showActionModal = true;
    }

    /*
     * Compatibility method for existing FE buttons.
     */
    public function confirmApprove(int $testimonialId): void
    {
        $this->confirmAction(
            $testimonialId,
            'approve'
        );
    }

    /*
     * Compatibility method for existing FE buttons.
     */
    public function confirmDeny(int $testimonialId): void
    {
        $this->confirmAction(
            $testimonialId,
            'deny'
        );
    }

    /*
     * Compatibility method for existing FE buttons.
     */
    public function confirmDelete(int $testimonialId): void
    {
        $this->confirmAction(
            $testimonialId,
            'delete'
        );
    }

    public function cancelAction(): void
    {
        $this->selectedTestimonialId = null;

        $this->pendingAction = null;

        $this->showActionModal = false;
    }

    public function executeAction(
        TestimonialService $service
    ): void {
        if (
            ! $this->selectedTestimonialId ||
            ! $this->pendingAction
        ) {
            return;
        }

        $testimonial = Testimonials::findOrFail(
            $this->selectedTestimonialId
        );

        switch ($this->pendingAction) {

            case 'approve':

                $this->authorize(
                    'approve',
                    $testimonial
                );

                $service->approve($testimonial);

                session()->flash(
                    'testimonial_success',
                    'Testimonial approved and published.'
                );

                break;

            case 'deny':

                $this->authorize(
                    'deny',
                    $testimonial
                );

                $service->deny($testimonial);

                session()->flash(
                    'testimonial_success',
                    'Testimonial denied.'
                );

                break;

            case 'delete':

                $this->authorize(
                    'delete',
                    $testimonial
                );

                $service->delete($testimonial);

                session()->flash(
                    'testimonial_success',
                    'Testimonial permanently deleted.'
                );

                break;
        }

        $this->cancelAction();

        $this->resetPage();
    }

    /*
     * Compatibility methods if the FE directly calls these.
     */
    public function approve(
        int $testimonialId,
        TestimonialService $service
    ): void {
        $testimonial = Testimonials::findOrFail(
            $testimonialId
        );

        $this->authorize(
            'approve',
            $testimonial
        );

        $service->approve($testimonial);

        session()->flash(
            'testimonial_success',
            'Testimonial approved and published.'
        );
    }

    public function deny(
        int $testimonialId,
        TestimonialService $service
    ): void {
        $testimonial = Testimonials::findOrFail(
            $testimonialId
        );

        $this->authorize(
            'deny',
            $testimonial
        );

        $service->deny($testimonial);

        session()->flash(
            'testimonial_success',
            'Testimonial denied.'
        );
    }

    public function delete(
        int $testimonialId,
        TestimonialService $service
    ): void {
        $testimonial = Testimonials::findOrFail(
            $testimonialId
        );

        $this->authorize(
            'delete',
            $testimonial
        );

        $service->delete($testimonial);

        session()->flash(
            'testimonial_success',
            'Testimonial permanently deleted.'
        );

        $this->resetPage();
    }

    public function render()
    {
        $query = Testimonials::query()
            ->when(
                $this->status !== 'all',
                fn ($query) => $query->where(
                    'status',
                    $this->status
                )
            )
            ->when(
                filled($this->search),
                function ($query) {
                    $search = '%'.$this->search.'%';

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search)
                            ->orWhere('company', 'like', $search)
                            ->orWhere('role', 'like', $search)
                            ->orWhere('message', 'like', $search);
                    });
                }
            )
            ->latest();

        /*
         * Dashboard counts.
         */
        $totalCount = Testimonials::query()->count();

        $pendingCount = Testimonials::query()
            ->where(
                'status',
                Testimonials::STATUS_PENDING
            )
            ->count();

        $approvedCount = Testimonials::query()
            ->where(
                'status',
                Testimonials::STATUS_APPROVED
            )
            ->count();

        $deniedCount = Testimonials::query()
            ->where(
                'status',
                Testimonials::STATUS_DENIED
            )
            ->count();

        $publishedCount = Testimonials::query()
            ->where('is_published', true)
            ->count();

        $featuredCount = Testimonials::query()
            ->where('is_featured', true)
            ->count();

        return view(
            'livewire.teams.testimonials.index',
            [
                'testimonials' => $query->paginate(
                    $this->perPage
                ),

                'totalCount' => $totalCount,

                'pendingCount' => $pendingCount,

                'approvedCount' => $approvedCount,

                'deniedCount' => $deniedCount,

                'publishedCount' => $publishedCount,

                'featuredCount' => $featuredCount,
            ]
        );
    }
}