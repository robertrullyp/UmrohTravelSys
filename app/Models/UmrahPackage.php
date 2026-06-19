<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class UmrahPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image_path',
        'duration_days',
        'price',
        'airline',
        'makkah_hotel',
        'madinah_hotel',
        'departure_month',
        'description',
        'seo_title',
        'seo_description',
        'seo_image_path',
        'includes',
        'is_featured',
        'is_active',
        'is_indexable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'includes' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'is_indexable' => 'boolean',
        ];
    }

    protected function seoTitle(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $this->sanitizeSeoText($value),
        );
    }

    protected function seoDescription(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $this->sanitizeSeoText($value),
        );
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    private function sanitizeSeoText(?string $value): ?string
    {
        $value = trim(strip_tags((string) $value));

        return $value !== '' ? $value : null;
    }
}
