<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'about',
        'vision',
        'missions',
        'photo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'missions' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
