<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasePatient extends Model
{
    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PLANNING = 'in_planning';
    const STATUS_APPROVAL = 'approval';
    const STATUS_REJECTED = 'rejected';
    const STATUS_IN_PRODUCTION = 'in_production';
    const STATUS_SHIPPED = 'shipped';

    protected $fillable = [
        'case_id',
        'patient_id',
        'doctor_id',
        'technician_id',
        'technician_comment',
        'laboratory_id',
        'laboratory_comment',
        'date',
        'time',
        'status',
        'doctor_instruction',
        'treatment_treat',
        'treatment_type',
        'treatment_overjet',
        'treatment_overbite',
        'treatment_midline',
        'treatment_irp',
        'treatment_attachments',
        'patient_chief_complaint',
        'accepted_date',
        'rejected_date',
        'type_of_scan',
        'priority',
        'price',
        'advance_payment',
        'remaining_balance',
        'wetransfer_link',
        'price_added_by',
        'price_added_at',
        'estimated_completion_date',
        'price_accepted_at',
        'price_rejected_at',
        'price_rejection_reason',
        'finition_requested_at',
        'finition_requested_by',
        'finition_request_note',
        'finition_description',
        'finition_completed_at',
        // Temporarily disabled for debugging
        // 'zip_status',
        // 'zip_google_drive_id',
        // 'zip_google_drive_link',
        // 'zip_created_at',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function laboratory()
    {
        return $this->belongsTo(User::class, 'laboratory_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'price_added_by');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function toothProblem()
    {
        return $this->belongsTo(ToothProblem::class);
    }
    public function treatmentType()
    {
        return $this->hasMany(TreatmentType::class, 'case_id');
    }

    /**
     * Finition files uploaded by the technician (FileUpload rubrique = 'finition').
     */
    public function finitionFiles()
    {
        return $this->hasMany(\App\Models\FileUpload::class, 'case_id')
                    ->where('wich_rubrique', 'finition')
                    ->latest();
    }

    /**
     * The doctor who requested the finition.
     */
    public function finitionRequester()
    {
        return $this->belongsTo(User::class, 'finition_requested_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'case_id');
    }

    public function latestInvoice()
    {
        return $this->hasOne(Invoice::class, 'case_id')->latest();
    }

    public function weTransferNotifications()
    {
        return $this->hasMany(WeTransferNotification::class, 'case_id');
    }

    public function latestWeTransferNotification()
    {
        return $this->hasOne(WeTransferNotification::class, 'case_id')->latest();
    }

    // Status check methods
    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }
    public function isInPlanning()
    {
        return $this->status === self::STATUS_IN_PLANNING;
    }

    public function isApproval()
    {
        return $this->status === self::STATUS_APPROVAL;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isInProduction()
    {
        return $this->status === self::STATUS_IN_PRODUCTION;
    }

    public function isShipped()
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    // Status update methods
    public function setDraft()
    {
        $this->update(['status' => self::STATUS_DRAFT]);
    }

    public function setPending()
    {
        $this->update(['status' => self::STATUS_PENDING]);
    }   

    public function setInPlanning()
    {
        $this->update(['status' => self::STATUS_IN_PLANNING]);
    }

    public function setApproval()
    {
        $this->update(['status' => self::STATUS_APPROVAL]);
    }

    public function setRejected()
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_date' => now()
        ]);
    }

    public function setInProduction()
    {
        $this->update(['status' => self::STATUS_IN_PRODUCTION]);
    }

    

    public function setShipped()
    {
        $this->update(['status' => self::STATUS_SHIPPED]);
    }

   
    // Get all possible statuses
    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_IN_PLANNING,
            self::STATUS_APPROVAL,
            self::STATUS_REJECTED,
            self::STATUS_IN_PRODUCTION,
            self::STATUS_SHIPPED
        ];
    }

    protected $casts = [
        'accepted_date' => 'datetime',
        'rejected_date' => 'datetime',
        'price_added_at' => 'datetime',
        'estimated_completion_date' => 'datetime',
        'price_accepted_at' => 'datetime',
        'price_rejected_at' => 'datetime',
        'finition_requested_at' => 'datetime',
        'finition_completed_at' => 'datetime',
        'zip_created_at' => 'datetime',
    ];

   
}
