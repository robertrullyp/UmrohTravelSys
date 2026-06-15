<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'umrah_package_id',
        'departure_date',
        'quota',
        'status',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function umrahPackage(): BelongsTo
    {
        return $this->belongsTo(UmrahPackage::class);
    }
}
