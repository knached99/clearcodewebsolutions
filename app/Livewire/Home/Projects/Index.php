<?php

namespace App\Livewire\Home\Projects;

use App\Models\Work;
use Livewire\Component;

class Index extends Component
{
    public $works;

    public function mount(): void
    {
        $this->works = Work::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->latest('id')
            ->get();
    }

    public function render()
    {
        return view('livewire.home.projects.index')
            ->layout('layouts.home_layout', [
                'title' => 'Our Work | Clear Code Web Solutions',
                'description' => 'Explore websites and digital experiences created by Clear Code Web Solutions.',
                'og_title' => 'Our Work | Clear Code Web Solutions',
                'og_description' => 'Explore our latest websites, digital experiences, and development projects.',
            ]);
    }
}
