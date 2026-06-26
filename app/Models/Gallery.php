<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'taken_at',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getCoverImagePathAttribute(): ?string
    {
        $photo = $this->relationLoaded('photos')
            ? $this->photos->first()
            : $this->photos()->first();

        return $photo?->image_path ?: $this->image_path;
    }
}
