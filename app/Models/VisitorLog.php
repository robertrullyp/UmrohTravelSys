<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'visited_on',
    'visited_at',
    'path',
    'route_name',
    'ip_hash',
    'user_agent_hash',
])]
class VisitorLog extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visited_on' => 'date',
            'visited_at' => 'datetime',
        ];
    }
}
