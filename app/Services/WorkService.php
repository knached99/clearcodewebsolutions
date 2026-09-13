<?php

namespace App\Services;

use App\Models\Work;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WorkService
{
    private const DISK = 'public';

    private const IMAGE_DIRECTORY = 'works';

    public function create(
        int $teamId, 
        array $data,
        array $images = []
    ): Work {
        $data['images'] = $this->storeImages($images);

        return Work::create(
            $this->workData($data, $teamId)
        );
    }

    public function update(
        Work $work,
        array $data,
        array $images = [],
        array $removeImages = []
    ): Work {
        $currentImages = $work->images ?? [];

        $imagesToRemove = array_intersect(
            $currentImages,
            $removeImages
        );

        if ($imagesToRemove) {
            Storage::disk(self::DISK)
                ->delete($imagesToRemove);

            $currentImages = array_values(
                array_diff(
                    $currentImages,
                    $imagesToRemove
                )
            );
        }

        $newImages = $this->storeImages($images);

        $data['images'] = [
            ...$currentImages,
            ...$newImages,
        ];

        $work->update(
            $this->workData($data, (int) $work->team_id)
        );

        return $work->refresh();
    }

    public function delete(Work $work): void
    {
        $images = $work->images ?? [];

        if ($images) {
            Storage::disk(self::DISK)
                ->delete($images);
        }

        $work->delete();
    }

    protected function storeImages(
        array $images
    ): array {
        if (! $images) {
            return [];
        }

        return array_map(
            fn (UploadedFile $image): string => $image->store(
                self::IMAGE_DIRECTORY,
                self::DISK
            ),
            $images
        );
    }

    protected function workData(array $data,int $teamId): array 
    {
    return [
        'team_id' => $teamId,
        'title' => $data['title'],
        'description' => $data['description'] ?? null,
        'category' => $data['category'] ?? 'WEB DEVELOPMENT',
        'url' => $data['url'] ?? null,
        'images' => $data['images'] ?? [],
        'is_published' => $data['is_published'] ?? true,
        'sort_order' => $data['sort_order'] ?? 0,
    ];
    }

}
