<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeTransferNotification extends Model
{
    protected $fillable = [
        'case_id',
        'technician_id',
        'laboratory_id',
        'wetransfer_link',
        'message',
        'sent_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime'
    ];

    public function case()
    {
        return $this->belongsTo(CasePatient::class, 'case_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function laboratory()
    {
        return $this->belongsTo(User::class, 'laboratory_id');
    }
}
