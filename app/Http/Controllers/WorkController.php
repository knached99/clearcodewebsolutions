<?php

namespace App\Http\Controllers;

use App\Models\Work;
use Illuminate\View\View;

class WorkController extends Controller
{
    /**
     * Display all published projects.
     */
    // public function index(): View
    // {
    //     $works = Work::query()
    //         ->where('is_published', true)
    //         ->orderBy('sort_order')
    //         ->latest('id')
    //         ->get();

    //     return view('home.projects.index', [
    //         'works' => $works,
    //     ]);
    // }

    /**
     * Display a single published project.
     */
    public function show(Work $work): View
    {
        abort_unless(
            $work->is_published,
            404
        );

        return view('home.projects.show', [
            'work' => $work,
        ]);
    }
}
