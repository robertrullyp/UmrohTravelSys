<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'umrah_package_id',
        'departure_date',
        'capacity',
        'quota',
        'status',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'capacity' => 'integer',
            'quota' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function umrahPackage(): BelongsTo
    {
        return $this->belongsTo(UmrahPackage::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public static function statusForQuota(int $quota): string
    {
        return match (true) {
            $quota <= 0 => 'Penuh',
            $quota <= 5 => 'Hampir Penuh',
            default => 'Tersedia',
        };
    }
}
