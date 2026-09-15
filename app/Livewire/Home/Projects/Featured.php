<?php

namespace App\Livewire\Home\Projects;

use App\Models\Work;
use Livewire\Component;

class Featured extends Component
{
    public $works;

    public bool $hasMore = false;

    public function mount(): void
    {
        // Fetch 5 so we can determine whether more than 4 exist.
        $works = Work::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->latest('id')
            ->take(5)
            ->get();

        $this->hasMore = $works->count() > 4;

        // Only show 4 on the homepage.
        $this->works = $works->take(4);
    }

    public function render()
    {
        return view('livewire.home.projects.featured');
    }
}
