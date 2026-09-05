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
         */
        $this->sortAfterId = Work::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->latest('id')
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

        $sortAfterId = $this->sortAfterId;

        /*
         * Never allow a project to be positioned after itself.
         */
        if (
            $this->editingId &&
            $sortAfterId === $this->editingId
        ) {
            $sortAfterId = null;
        }

        /*
         * The service still receives sort_order because the Work
         * model/database uses it. We initially put the project at
         * the end, then normalize the entire ordering below.
         */
        $validated['sort_order'] = Work::query()->max('sort_order') + 1;

        DB::transaction(function () use (
            $service,
            $validated,
            $sortAfterId
        ) {
            if ($this->editingId) {
                $work = Work::query()
                    ->lockForUpdate()
                    ->findOrFail($this->editingId);

                $service->update(
                    $work,
                    $validated,
                    $this->images,
                    $this->removeImages
                );

                $this->reorderProjects(
                    $work->fresh(),
                    $sortAfterId
                );

                $message = 'Project updated successfully.';
            } else {
                $work = $service->create(
                    $validated,
                    $this->images
                );

                $this->reorderProjects(
                    $work->fresh(),
                    $sortAfterId
                );

                $message = 'Project created successfully.';
            }

            session()->flash(
                'project_success',
                $message
            );
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
            ->get()
            ->reject(
                fn (Work $work) => $work->id === $project->id
            )
            ->values();

        /*
         * No "after" project means this project goes first.
         */
        if ($afterId === null) {
            $projects->prepend($project);
        } else {
            $insertAt = $projects->search(
                fn (Work $work) => $work->id === $afterId
            );

            if ($insertAt === false) {
                $projects->prepend($project);
            } else {
                $projects->splice(
                    $insertAt + 1,
                    0,
                    [$project]
                );
            }
        }

        /*
         * Normalize to 0, 1, 2, 3...
         */
        foreach ($projects->values() as $position => $work) {
            $work->update([
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
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        /*
         * Projects available in the ordering dropdown.
         *
         * When editing, the current project is excluded.
         */
        $orderOptions = $works
            ->reject(
                fn (Work $work) =>
                    $this->editingId &&
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