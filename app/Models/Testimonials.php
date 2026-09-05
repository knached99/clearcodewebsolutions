<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonials extends Model
{
    protected $fillable = [
        'name',
        'email',
        'company',
        'role',
        'message',
        'rating',
        'is_published',
        'is_featured',
        'status',
        'sort_order',
        'published_at',
        'moderated_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
            'moderated_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    */

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_DENIED = 'denied';

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_APPROVED)
            ->where('is_published', true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_PENDING
        );
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_APPROVED
        );
    }

    public function scopeDenied(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_DENIED
        );
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isDenied(): bool
    {
        return $this->status === self::STATUS_DENIED;
    }

    /*
    |--------------------------------------------------------------------------
    | Display
    |--------------------------------------------------------------------------
    */

    public function getInitialsAttribute(): string
    {
        return collect(
            preg_split('/\s+/', trim($this->name))
        )
            ->filter()
            ->take(2)
            ->map(
                fn (string $name) => mb_strtoupper(
                    mb_substr($name, 0, 1)
                )
            )
            ->implode('');
    }

    public function getDisplayRoleAttribute(): ?string
    {
        return collect([
            $this->role,
            $this->company,
        ])
            ->filter()
            ->implode(', ');
    }
}