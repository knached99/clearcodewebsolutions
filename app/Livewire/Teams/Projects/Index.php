<?php

namespace App\Livewire\Teams\Projects;

use App\Models\Work;
use App\Services\WorkService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class Index extends Component
{
    use WithFileUploads;

    /*
    |--------------------------------------------------------------------------
    | Form State
    |--------------------------------------------------------------------------
    */

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public string $url = '';

    public string $category = '';

    public bool $is_published = true;

    /*
    |--------------------------------------------------------------------------
    | Ordering
    |--------------------------------------------------------------------------
    |
    | ID of the project this project should appear AFTER.
    |
    | null = first position.
    |
    */

    public ?int $sortAfterId = null;

    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    */

    public array $images = [];

    public array $removeImages = [];

    /*
    |--------------------------------------------------------------------------
    | UI
    |--------------------------------------------------------------------------
    */

    public bool $showForm = false;

    /*
    |--------------------------------------------------------------------------
    | Limits
    |--------------------------------------------------------------------------
    */

    private const MAX_IMAGES = 10;

    private const MAX_IMAGE_SIZE_KB = 10240;

    private const MAX_TOTAL_IMAGE_SIZE_KB = 51200;

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

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
                'max:50000',
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
                'nullable',
                'array',
                'max:' . self::MAX_IMAGES,
            ],

            'images.*' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:' . self::MAX_IMAGE_SIZE_KB,
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'Please enter a project title.',

            'title.max' => 'The project title may not exceed 255 characters.',

            'description.max' => 'The project description is too long.',

            'url.url' => 'Please enter a valid project URL.',

            'url.max' => 'The project URL is too long.',

            'category.required' => 'Please select a project category.',

            'category.in' => 'Please select a valid project category.',

            'sortAfterId.exists' => 'The selected project could not be found.',

            'images.max' => 'You may upload up to 10 images.',

            'images.*.image' => 'Each uploaded file must be a valid image.',

            'images.*.mimes' => 'Only JPG, JPEG, PNG, WEBP, and GIF images are allowed.',

            'images.*.max' => 'Each image may not exceed 10 MB.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): void
    {
        $this->authorize('create', Work::class);

        $this->resetForm();

        $this->showForm = true;

        /*
         * New projects default to the end of the current list.
         *
         * sortAfterId stores the project after which the new project
         * should be inserted.
         */
        $this->sortAfterId = Work::query()
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->value('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(int $id): void
    {
        $work = Work::query()
            ->findOrFail($id);

        $this->authorize('update', $work);

        $this->editingId = $work->id;

        $this->title = (string) $work->title;

        $this->description = (string) (
            $work->description ?? ''
        );

        $this->url = (string) (
            $work->url ?? ''
        );

        $this->category = (string) (
            $work->category ?? ''
        );

        $this->is_published = (bool) $work->is_published;

        /*
         * Find the project immediately before this one.
         *
         * Because ordering is:
         *
         * sort_order ASC
         * id ASC
         *
         * the previous project is the highest project with an ordering
         * position before the current project.
         */
        $this->sortAfterId = Work::query()
            ->where(function ($query) use ($work) {
                $query
                    ->where(
                        'sort_order',
                        '<',
                        $work->sort_order,
                    )
                    ->orWhere(function ($query) use ($work) {
                        $query
                            ->where(
                                'sort_order',
                                $work->sort_order,
                            )
                            ->where(
                                'id',
                                '<',
                                $work->id,
                            );
                    });
            })
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->value('id');

        /*
         * Clear temporary upload state when switching projects.
         */
        $this->images = [];

        $this->removeImages = [];

        $this->showForm = true;
    }

    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    public function save(WorkService $service): void
    {
        $this->authorize(
            $this->editingId !== null
                ? 'update'
                : 'create',
            $this->editingId !== null
                ? Work::query()->findOrFail($this->editingId)
                : Work::class,
        );

        /*
         * Normalize simple fields before validation.
         */
        $this->title = trim($this->title);

        $this->description = trim($this->description);

        $this->url = trim($this->url);

        $this->category = trim($this->category);

        /*
         * Validate all submitted data.
         */
        $validated = $this->validate();

        /*
         * Validate the aggregate upload size.
         */
        $this->validateTotalImageSize();

        /*
         * Capture the selected placement.
         */
        $sortAfterId = $this->sortAfterId !== null
            ? (int) $this->sortAfterId
            : null;

        /*
         * A project cannot be positioned after itself.
         */
        if (
            $this->editingId !== null
            && $sortAfterId !== null
            && $sortAfterId === (int) $this->editingId
        ) {
            $sortAfterId = null;
        }

        /*
         * Verify that sortAfterId still exists.
         *
         * The dropdown may have been rendered several seconds/minutes
         * ago and another project could have been deleted meanwhile.
         */
        if ($sortAfterId !== null) {
            $afterExists = Work::query()
                ->whereKey($sortAfterId)
                ->exists();

            if (! $afterExists) {
                $sortAfterId = null;
            }
        }

        /*
         * Temporarily place the project at the end.
         *
         * reorderProjects() will calculate the final position.
         */
        $validated['sort_order'] =
            ((int) Work::query()->max('sort_order')) + 1;

        try {
            DB::transaction(
                function () use (
                    $service,
                    $validated,
                    $sortAfterId,
                ): void {
                    /*
                     * Editing an existing project.
                     */
                    if ($this->editingId !== null) {
                        $work = Work::query()
                            ->lockForUpdate()
                            ->findOrFail($this->editingId);

                        $this->authorize(
                            'update',
                            $work,
                        );

                        $service->update(
                            $work,
                            $validated,
                            $this->images,
                            $this->removeImages,
                        );

                        /*
                         * Re-fetch the project so the reorder operates
                         * on its current persisted state.
                         */
                        $work->refresh();

                        $this->reorderProjects(
                            $work,
                            $sortAfterId,
                        );

                        session()->flash(
                            'project_success',
                            'Project updated successfully.',
                        );

                        return;
                    }

                    /*
                     * Creating a new project.
                     */
                    $work = $service->create(
                        $validated,
                        $this->images,
                    );

                    $this->reorderProjects(
                        $work->fresh(),
                        $sortAfterId,
                    );

                    session()->flash(
                        'project_success',
                        'Project created successfully.',
                    );
                },
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            $this->addError(
                'title',
                'The project could not be saved. Please try again.',
            );

            return;
        }

        /*
         * Reset only after the transaction succeeds.
         */
        $message = session('project_success');

        $this->resetForm();

        $this->dispatch(
            'notify',
            message: $message,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reorder Projects
    |--------------------------------------------------------------------------
    |
    | Rebuild the complete ordering.
    |
    | null = first position.
    | otherwise = immediately after $afterId.
    |
    */

    protected function reorderProjects(
        Work $project,
        ?int $afterId,
    ): void {
        /*
         * Lock the ordering set while rebuilding it.
         *
         * This prevents two simultaneous reorder operations from reading
         * the same old ordering and overwriting one another.
         */
        $projects = Work::query()
            ->lockForUpdate()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        /*
         * Remove the project being moved.
         */
        $orderedProjects = $projects
            ->reject(
                fn (Work $work): bool =>
                    (int) $work->id === (int) $project->id,
            )
            ->values();

        /*
         * Insert at the requested location.
         */
        if ($afterId === null) {
            $orderedProjects->prepend($project);
        } else {
            $afterIndex = $orderedProjects->search(
                fn (Work $work): bool =>
                    (int) $work->id === (int) $afterId,
            );

            /*
             * If the target disappeared between validation and the
             * transaction, safely fall back to the beginning.
             */
            if ($afterIndex === false) {
                $orderedProjects->prepend($project);
            } else {
                $items = $orderedProjects->all();

                array_splice(
                    $items,
                    $afterIndex + 1,
                    0,
                    [$project],
                );

                $orderedProjects = collect($items);
            }
        }

        /*
         * Normalize positions.
         */
        $orderedProjects = $orderedProjects->values();

        /*
         * Avoid unnecessary UPDATE queries for projects that are
         * already in the correct position.
         */
        foreach (
            $orderedProjects as $position => $work
        ) {
            $position = (int) $position;

            if ((int) $work->sort_order === $position) {
                continue;
            }

            Work::query()
                ->whereKey($work->id)
                ->update([
                    'sort_order' => $position,
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        int $id,
        WorkService $service,
    ): void {
        $work = Work::query()
            ->findOrFail($id);

        $this->authorize(
            'delete',
            $work,
        );

        /*
         * Keep deletion + ordering normalization atomic.
         */
        DB::transaction(
            function () use (
                $work,
                $service,
            ): void {
                /*
                 * Lock the row before deleting it.
                 */
                $work->refresh();

                /*
                 * WorkService remains responsible for deleting the
                 * project's associated data/files.
                 */
                $service->delete($work);

                /*
                 * Normalize remaining projects.
                 */
                $this->normalizeProjectOrder();
            },
        );

        $this->resetForm();

        $this->dispatch(
            'notify',
            message: 'Project deleted successfully.',
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle Published
    |--------------------------------------------------------------------------
    */

    public function togglePublished(int $id): void
    {
        $work = Work::query()
            ->findOrFail($id);

        $this->authorize(
            'update',
            $work,
        );

        /*
         * Atomic boolean toggle.
         *
         * This avoids trusting the stale value that may be sitting
         * inside the Livewire component.
         */
        Work::query()
            ->whereKey($work->id)
            ->update([
                'is_published' => ! (bool) $work->is_published,
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Remove Existing Image
    |--------------------------------------------------------------------------
    */

    public function removeExistingImage(
        string $path,
    ): void {
        $path = trim($path);

        if ($path === '') {
            return;
        }

        /*
         * Do not allow arbitrary values to accumulate in the component.
         *
         * The actual WorkService should also verify that every removal
         * path belongs to the Work being edited before deleting anything.
         */
        if (! in_array(
            $path,
            $this->removeImages,
            true,
        )) {
            $this->removeImages[] = $path;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Restore Existing Image
    |--------------------------------------------------------------------------
    */

    public function restoreExistingImage(
        string $path,
    ): void {
        $path = trim($path);

        if ($path === '') {
            return;
        }

        $this->removeImages = array_values(
            array_diff(
                $this->removeImages,
                [$path],
            ),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Upload Validation
    |--------------------------------------------------------------------------
    */

    public function updatedImages(): void
    {
        $this->validate([
            'images' => [
                'nullable',
                'array',
                'max:' . self::MAX_IMAGES,
            ],

            'images.*' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:' . self::MAX_IMAGE_SIZE_KB,
            ],
        ]);

        $this->validateTotalImageSize();
    }

    /*
    |--------------------------------------------------------------------------
    | Total Image Size
    |--------------------------------------------------------------------------
    */

    protected function validateTotalImageSize(): void
    {
        if ($this->images === []) {
            return;
        }

        $totalBytes = 0;

        foreach ($this->images as $image) {
            $size = $image->getSize();

            if (is_int($size)) {
                $totalBytes += $size;
            }
        }

        $maxBytes =
            self::MAX_TOTAL_IMAGE_SIZE_KB * 1024;

        if ($totalBytes > $maxBytes) {
            throw ValidationException::withMessages([
                'images' =>
                    'The combined size of uploaded images may not exceed 50 MB.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(): void
    {
        $this->resetForm();
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize Project Order
    |--------------------------------------------------------------------------
    */

    protected function normalizeProjectOrder(): void
    {
        $projects = Work::query()
            ->lockForUpdate()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach (
            $projects as $position => $work
        ) {
            $position = (int) $position;

            /*
             * Avoid unnecessary UPDATE statements.
             */
            if ((int) $work->sort_order === $position) {
                continue;
            }

            Work::query()
                ->whereKey($work->id)
                ->update([
                    'sort_order' => $position,
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Form
    |--------------------------------------------------------------------------
    */

    protected function resetForm(): void
    {
        $this->reset([
            'editingId',
            'title',
            'description',
            'url',
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

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        $this->authorize(
            'viewAny',
            Work::class,
        );

        /*
         * One query gets everything needed for both the project list
         * and the ordering dropdown.
         */
        $works = Work::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $orderOptions = $works
            ->reject(
                fn (Work $work): bool =>
                    $this->editingId !== null
                    && (int) $work->id === (int) $this->editingId,
            )
            ->values();

        return view(
            'livewire.teams.projects.index',
            compact(
                'works',
                'orderOptions',
            ),
        );
    }
}