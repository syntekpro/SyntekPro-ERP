<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUpdate extends Model
{
    protected $fillable = [
        'version',
        'name',
        'notes',
        'released_at',
        'checked_at',
        'is_prerelease',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'released_at' => 'datetime',
            'checked_at' => 'datetime',
            'is_prerelease' => 'boolean',
        ];
    }
}
