<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Url extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'url',
        'short_code',
        'is_active',
        'is_blocked',
        'title',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
    ];
}
