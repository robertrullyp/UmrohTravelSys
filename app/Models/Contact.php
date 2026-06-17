<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'whatsapp',
        'email',
        'instagram',
        'map_embed_url',
        'latitude',
        'longitude',
        'is_active',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Contact $contact): void {
            if ($contact->is_primary) {
                $contact->is_active = true;
            }
        });

        static::saved(function (Contact $contact): void {
            if (! $contact->is_primary) {
                return;
            }

            static::query()
                ->whereKeyNot($contact->getKey())
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        });
    }
}
