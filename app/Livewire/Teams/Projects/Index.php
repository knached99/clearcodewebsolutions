<?php

namespace App\Livewire\Teams\Projects;

use App\Models\Work;
use App\Services\WorkService;
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
    public int $sort_order = 0;

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

            'sort_order' => [
                'required',
                'integer',
                'min:0',
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
        $this->sort_order = $work->sort_order;

        $this->images = [];
        $this->removeImages = [];

        $this->showForm = true;
    }

    public function save(WorkService $service): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            $work = Work::query()->findOrFail($this->editingId);

            $service->update(
                $work,
                $validated,
                $this->images,
                $this->removeImages
            );

            $message = 'Project updated successfully.';
        } else {
            $service->create(
                $validated,
                $this->images
            );

            $message = 'Project created successfully.';
        }

        $this->resetForm();

        $this->dispatch(
            'notify',
            message: $message
        );
    }

    public function delete(int $id, WorkService $service): void
    {
        $work = Work::query()->findOrFail($id);

        $service->delete($work);

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
        if (! in_array($path, $this->removeImages, true)) {
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

    protected function resetForm(): void
    {
        $this->reset([
            'editingId',
            'title',
            'description',
            'url',
            'category',
            'is_published',
            'sort_order',
            'images',
            'removeImages',
            'showForm',
        ]);

        $this->is_published = true;
        $this->category = '';
        $this->sort_order = 0;
    }

    public function render()
    {
        $works = Work::query()
            ->orderBy('sort_order')
            ->latest('id')
            ->get();

        return view(
            'livewire.teams.projects.index',
            compact('works')
        );
    }
}