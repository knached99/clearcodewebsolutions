<?php

namespace app\Livewire\Home\Projects;

use App\Models\Work;
use Livewire\Component;

class Featured extends Component
{
    public $works;

    public bool $hasMore = false;

    public function mount(): void
    {
        $this->works = Work::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->latest('id')
            ->take(5)
            ->get();

        $this->hasMore = $this->works->count() > 4;

        $this->works = $this->works->take(4); // only display the first 4 on the home page
    }

    public function render()
    {
        return view('livewire.home.projects.featured');
    }
}
