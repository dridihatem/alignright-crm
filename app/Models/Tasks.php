<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start',
        'end',
        'color',
        'url',
        'user_id',
    ];

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }
}
