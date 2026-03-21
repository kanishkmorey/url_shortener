<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Url extends Model
{
    use HasFactory;
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
        'expires_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function clicks()
    {
        return $this->hasMany(Click::class);
    }
}
