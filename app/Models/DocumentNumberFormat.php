<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumberFormat extends Model
{
    protected $fillable = [
        'key',
        'label',
        'prefix',
        'reset_frequency',
        'next_reset',
    ];

    protected function casts(): array
    {
        return [
            'next_reset' => 'date',
        ];
    }
}