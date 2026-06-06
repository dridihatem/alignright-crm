<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //
    protected $fillable = ['title', 'message', 'type', 'status', 'case_id', 'doctor_id', 'technician_id', 'laboratory_id'];
}







