<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start',
        'end',
        'color',
        'event_url',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
