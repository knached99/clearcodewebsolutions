<?php

namespace App\Livewire\Home\Testimonials;

use App\Models\Testimonials;
use Livewire\Component;

class Slider extends Component
{
    public function render()
    {
        $testimonials = Testimonials::query()
            ->published()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest('published_at')
            ->get();

        return view(
            'livewire.home.testimonials.slider',
            compact('testimonials')
        );
    }
}