<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Work extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category',
        'url',
        'images',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        $image = $this->images[0] ?? null;

        return $image
            ? Storage::disk('public')->url($image)
            : null;
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->map(
                fn (string $image) =>
                    Storage::disk('public')->url($image)
            )
            ->values()
            ->all();
    }
}