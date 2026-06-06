<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentType extends Model
{
    protected $fillable = [
        'irp_file', // renamed from 'name'
        'link_viewer', // new column for 3D viewer link
        'status', 
        'description', 
        'case_id', 
        'type_file',
        'uploaded_by', // technician_id who uploaded
        'accepted_by', // doctor_id who accepted
        'accepted_at', // when accepted
        'rejected_by', // doctor_id who rejected
        'rejected_at', // when rejected
        'rejection_reason', // reason for rejection
        'treatment_plan_uploaded_at', // when treatment plan was uploaded
        'doctor_approved_at', // when doctor approved
        'estimated_completion_date' // estimated completion date
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'treatment_plan_uploaded_at' => 'datetime',
        'doctor_approved_at' => 'datetime',
        'estimated_completion_date' => 'datetime',
    ];

    public function case()
    {
        return $this->belongsTo(CasePatient::class, 'case_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'price_added_by');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

   
}
