<?php

namespace App\Livewire\Teams\Projects;

use App\Models\Work;
use App\Services\WorkService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public string $url = '';

    public string $category = '';

    public bool $is_published = true;

    /**
     * ID of the project this project should appear AFTER.
     *
     * null = first position.
     */
    public ?int $sortAfterId = null;

    /**
     * Newly uploaded images.
     */
    public array $images = [];

    /**
     * Existing images selected for removal.
     */
    public array $removeImages = [];

    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'url' => [
                'nullable',
                'url',
                'max:2048',
            ],

            'category' => [
                'required',
                'string',
                'in:Website Design,Website Development,Mobile App Development,E-Commerce,Website Redesign,Maintenance & Support,Other',
            ],

            'is_published' => [
                'boolean',
            ],

            'sortAfterId' => [
                'nullable',
                'integer',
                'exists:works,id',
            ],

            'images' => [
                'array',
            ],

            'images.*' => [
                'image',
                'max:10240',
            ],
        ];
    }

    public function create(): void
    {
        $this->resetForm();

        $this->showForm = true;

        /*
         * New projects default to the end of the list.
         *
         * sortAfterId stores the ID of the project that the new
         * project should be inserted after.
         */
        $this->sortAfterId = Work::query()
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->value('id');
    }

    public function edit(int $id): void
    {
        $work = Work::query()->findOrFail($id);

        $this->editingId = $work->id;
        $this->title = $work->title;
        $this->description = $work->description ?? '';
        $this->url = $work->url ?? '';
        $this->category = $work->category ?? '';
        $this->is_published = $work->is_published;

        /*
         * Find the project immediately before this one.
         *
         * That becomes the selected "After..." option.
         */
        $this->sortAfterId = Work::query()
            ->where(function ($query) use ($work) {
                $query
                    ->where('sort_order', '<', $work->sort_order)
                    ->orWhere(function ($query) use ($work) {
                        $query
                            ->where('sort_order', $work->sort_order)
                            ->where('id', '<', $work->id);
                    });
            })
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->value('id');

        $this->images = [];
        $this->removeImages = [];
        $this->showForm = true;
    }

    public function save(WorkService $service): void
    {
        $validated = $this->validate();

        /*
         * This is the Work ID selected by the placement dropdown.
         *
         * null = beginning
         * 2    = immediately after Work ID 2
         * 17   = immediately after Work ID 17
         */
        $sortAfterId = $this->sortAfterId;

        /*
         * A project cannot be placed after itself.
         */
        if (
            $this->editingId !== null &&
            $sortAfterId !== null &&
            (int) $sortAfterId === (int) $this->editingId
        ) {
            $sortAfterId = null;
        }

        DB::transaction(function () use (
            $service,
            $validated,
            $sortAfterId
        ) {
            /*
             * Temporarily put the project at the end.
             *
             * reorderProjects() immediately calculates the real
             * final position afterwards.
             */
            $validated['sort_order'] =
                ((int) Work::query()->max('sort_order')) + 1;

            if ($this->editingId !== null) {
                $work = Work::query()
                    ->lockForUpdate()
                    ->findOrFail($this->editingId);

                $service->update(
                    $work,
                    $validated,
                    $this->images,
                    $this->removeImages
                );

                /*
                 * IMPORTANT:
                 *
                 * Use the Work ID from sortAfterId, not sort_order.
                 */
                $this->reorderProjects(
                    $work->fresh(),
                    $sortAfterId
                );

                session()->flash(
                    'project_success',
                    'Project updated successfully.'
                );
            } else {
                $work = $service->create(
                    $validated,
                    $this->images
                );

                /*
                 * For a new project, sortAfterId is also a Work ID.
                 */
                $this->reorderProjects(
                    $work->fresh(),
                    $sortAfterId
                );

                session()->flash(
                    'project_success',
                    'Project created successfully.'
                );
            }
        });

        $this->resetForm();

        $this->dispatch(
            'notify',
            message: session('project_success')
        );
    }

    /**
     * Rebuild the complete project order.
     *
     * The selected project is inserted immediately after
     * $afterId. If $afterId is null, it becomes the first project.
     */
    protected function reorderProjects(
        Work $project,
        ?int $afterId
    ): void {
        $projects = Work::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        /*
         * Remove the project we are moving from its current position.
         */
        $orderedProjects = $projects
            ->reject(fn (Work $work) => $work->id === $project->id)
            ->values();

        /*
         * No afterId means:
         *
         *     project goes to the beginning.
         */
        if ($afterId === null) {
            $orderedProjects->prepend($project);
        } else {
            /*
             * Find the index of the project we want to place
             * the current project AFTER.
             */
            $afterIndex = $orderedProjects->search(
                fn (Work $work) => (int) $work->id === (int) $afterId
            );

            /*
             * If the requested Work ID doesn't exist in the current
             * ordering, fall back to the beginning.
             */
            if ($afterIndex === false) {
                $orderedProjects->prepend($project);
            } else {
                /*
                 * Convert to a normal PHP array and explicitly insert
                 * the project after the target.
                 */
                $items = $orderedProjects->all();

                array_splice(
                    $items,
                    $afterIndex + 1,
                    0,
                    [$project]
                );

                $orderedProjects = collect($items);
            }
        }

        /*
         * Normalize the entire ordering.
         *
         * Database:
         *
         *     first  = 0
         *     second = 1
         *     third  = 2
         *     ...
         */
        foreach ($orderedProjects->values() as $position => $work) {
            Work::query()
                ->whereKey($work->id)
                ->update([
                    'sort_order' => $position,
                ]);
        }
    }

    public function delete(
        int $id,
        WorkService $service
    ): void {
        $work = Work::query()->findOrFail($id);

        $service->delete($work);

        /*
         * Close the gap left by the deleted project.
         */
        $this->normalizeProjectOrder();

        $this->dispatch(
            'notify',
            message: 'Project deleted successfully.'
        );
    }

    public function togglePublished(int $id): void
    {
        $work = Work::query()->findOrFail($id);

        $work->update([
            'is_published' => ! $work->is_published,
        ]);
    }

    public function removeExistingImage(string $path): void
    {
        if (
            ! in_array(
                $path,
                $this->removeImages,
                true
            )
        ) {
            $this->removeImages[] = $path;
        }
    }

    public function restoreExistingImage(string $path): void
    {
        $this->removeImages = array_values(
            array_diff(
                $this->removeImages,
                [$path]
            )
        );
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    protected function normalizeProjectOrder(): void
    {
        Work::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->each(
                function (Work $work, int $position) {
                    if ($work->sort_order !== $position) {
                        $work->update([
                            'sort_order' => $position,
                        ]);
                    }
                }
            );
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId',
            'title',
            'description',
            'url',
            'category',
            'is_published',
            'sortAfterId',
            'images',
            'removeImages',
            'showForm',
        ]);

        $this->is_published = true;
        $this->category = '';
        $this->sortAfterId = null;
    }

    public function render()
    {
        $works = Work::query()
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $orderOptions = $works
            ->reject(
                fn (Work $work) => $this->editingId !== null &&
                    $work->id === $this->editingId
            )
            ->values();

        return view(
            'livewire.teams.projects.index',
            compact(
                'works',
                'orderOptions'
            )
        );
    }
}
