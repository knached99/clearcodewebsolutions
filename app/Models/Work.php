<?php

namespace App\Models;

use App\Models\Team; 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Support\Facades\Storage;

class Work extends Model
{
    protected $fillable = [
        'team_id',
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

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function works(): HasMany 
    {
        return $this->hasMany(Work::class);
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
                fn (string $image) => Storage::disk('public')->url($image)
            )
            ->values()
            ->all();
    }
}
