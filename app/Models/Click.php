<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Click extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'url_id',
        'clicked_at',
        'ip',
        'country',
        'referrer',
        'user_agent',
    ];

    public function getIpAttribute($value)
    {
        return $value ? inet_ntop($value) : null;
    }

    public function url()
    {
        return $this->belongsTo(Url::class);
    }
}
