<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tickets extends Model
{
    //
    protected $table = 'tickets';
    protected $fillable = ['message', 'subject','user_id','assigned_to', 'status', 'priority','ticket_id', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
        

    public function assigned_to()
    {
        return $this->belongsTo(User::class);
    }
}
