<?php

namespace App\Livewire\Teams\Projects;

use App\Models\Team;
use App\Models\Work;
use App\Services\WorkService;
use Illuminate\Support\Facades\Auth;
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
    | Current Team
    |--------------------------------------------------------------------------
    */

    public Team $team;

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
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->team = $this->resolveCurrentTeam();
    }

    /*
    |--------------------------------------------------------------------------
    | Current Team
    |--------------------------------------------------------------------------
    */

    private function resolveCurrentTeam(): Team
    {
        $user = Auth::user();

        abort_unless(
            $user !== null,
            403,
            'You must be authenticated.',
        );

        $team = $user->currentTeam;

        abort_unless(
            $team !== null,
            403,
            'No current team is selected.',
        );

        abort_unless(
            $user->belongsToTeam($team),
            403,
            'You do not belong to this team.',
        );

        return $team;
    }

    /*
    |--------------------------------------------------------------------------
    | Team Work
    |--------------------------------------------------------------------------
    |
    | Always retrieve projects through the current team.
    |
    */

    private function findTeamWork(int $id): Work
    {
        return $this->team
            ->works()
            ->findOrFail($id);
    }

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
            ],

            'images' => [
                'nullable',
                'array',
                'max:'.self::MAX_IMAGES,
            ],

            'images.*' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:'.self::MAX_IMAGE_SIZE_KB,
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
        $this->authorize(
            'create',
            Work::class,
        );

        $this->resetForm();

        $this->showForm = true;

        /*
         * Only projects belonging to the current team.
         */
        $this->sortAfterId = $this->team
            ->works()
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->value('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Remove New Image
    |--------------------------------------------------------------------------
    */

    public function removeNewImage(int $index): void
    {
        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(int $id): void
    {
        /*
         * Centralized team-scoped lookup.
         */
        $work = $this->findTeamWork($id);

        $this->authorize(
            'update',
            $work,
        );

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
         * Find previous project ONLY within current team.
         */
        $this->sortAfterId = $this->team
            ->works()
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
        $work = null;

        if ($this->editingId !== null) {
            /*
             * Centralized team-scoped lookup.
             */
            $work = $this->findTeamWork(
                $this->editingId
            );

            $this->authorize(
                'update',
                $work,
            );
        } else {
            $this->authorize(
                'create',
                Work::class,
            );
        }

        $this->title = trim($this->title);

        $this->description = trim($this->description);

        $this->url = trim($this->url);

        $this->category = trim($this->category);

        $validated = $this->validate();

        $this->validateTotalImageSize();

        $sortAfterId = $this->sortAfterId !== null
            ? (int) $this->sortAfterId
            : null;

        /*
         * Cannot move a project after itself.
         */
        if (
            $this->editingId !== null
            && $sortAfterId !== null
            && $sortAfterId === (int) $this->editingId
        ) {
            $sortAfterId = null;
        }

        /*
         * The selected "after" project must belong to
         * the current team.
         */
        if ($sortAfterId !== null) {
            $afterExists = $this->team
                ->works()
                ->whereKey($sortAfterId)
                ->exists();

            if (! $afterExists) {
                $sortAfterId = null;
            }
        }

        /*
         * Temporarily put the project at the end of THIS team's list.
         */
        $validated['sort_order'] =
            ((int) $this->team
                ->works()
                ->max('sort_order')) + 1;

        try {
            DB::transaction(
                function () use (
                    $service,
                    $validated,
                    $sortAfterId,
                ): void {
                    if ($this->editingId !== null) {
                        /*
                         * Team-scoped + locked.
                         */
                        $work = $this->team
                            ->works()
                            ->lockForUpdate()
                            ->findOrFail(
                                $this->editingId
                            );

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
                     * Service gets the team and assigns team_id.
                     */
                    $work = $service->create(
                        (int) $this->team->id,
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
    */

    protected function reorderProjects(
        Work $project,
        ?int $afterId,
    ): void {
        /*
         * IMPORTANT:
         *
         * The ordering is per-team.
         */
        $projects = $this->team
            ->works()
            ->lockForUpdate()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $orderedProjects = $projects
            ->reject(
                fn (Work $work): bool => (int) $work->id === (int) $project->id,
            )
            ->values();

        if ($afterId === null) {
            $orderedProjects->prepend($project);
        } else {
            $afterIndex = $orderedProjects->search(
                fn (Work $work): bool => (int) $work->id === (int) $afterId,
            );

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

        foreach (
            $orderedProjects->values() as $position => $work
        ) {
            $position = (int) $position;

            if ((int) $work->sort_order === $position) {
                continue;
            }

            $this->team
                ->works()
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
        /*
         * Centralized team-scoped lookup.
         */
        $work = $this->findTeamWork($id);

        $this->authorize(
            'delete',
            $work,
        );

        DB::transaction(
            function () use (
                $work,
                $service,
            ): void {
                /*
                 * Lock the correct team row.
                 */
                $work = $this->team
                    ->works()
                    ->lockForUpdate()
                    ->findOrFail($work->id);

                $service->delete($work);

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
        /*
         * Centralized team-scoped lookup.
         */
        $work = $this->findTeamWork($id);

        $this->authorize(
            'update',
            $work,
        );

        $this->team
            ->works()
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

    public function removeExistingImage(string $image): void
    {
        $image = trim($image);

        if ($image === '') {
            return;
        }

        if (! in_array($image, $this->removeImages, true)) {
            $this->removeImages[] = $image;
        }

        $this->removeImages = array_values(
            array_unique($this->removeImages)
        );
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
        $this->validateUploadedImages();
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
                'images' => 'The combined size of uploaded images may not exceed 50 MB.',
            ]);
        }
    }

    private function validateUploadedImages(): void
    {
        $this->validate([
            'images' => [
                'nullable',
                'array',
                'max:'.self::MAX_IMAGES,
            ],

            'images.*' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:'.self::MAX_IMAGE_SIZE_KB,
            ],
        ]);
        $this->validateTotalImageSize();
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
        /*
         * Only normalize this team's projects.
         */
        $projects = $this->team
            ->works()
            ->lockForUpdate()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach (
            $projects as $position => $work
        ) {
            $position = (int) $position;

            if ((int) $work->sort_order === $position) {
                continue;
            }

            $this->team
                ->works()
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

    private function removeUploadedImage(int $index): void
    {
        if (! array_key_exists($index, $this->images)) {
            return;
        }

        unset($this->images[$index]);
        $this->images = array_values($this->images);
        $this->validateUploadedImages();
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

        $works = $this->team
            ->works()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $orderOptions = $works
            ->reject(
                fn (Work $work): bool => $this->editingId !== null
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
