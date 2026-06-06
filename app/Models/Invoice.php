<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'case_id',
        'invoice_number',
        'total_amount',
        'advance_payment',
        'remaining_balance',
        'status',
        'due_date',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'advance_payment' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'due_date' => 'date'
    ];

    // Relationships
    public function case(): BelongsTo
    {
        return $this->belongsTo(CasePatient::class, 'case_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';

    // Status check methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE;
    }

    // Generate invoice number
    public static function generateInvoiceNumber(): string
    {
        $lastInvoice = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastInvoice ? (int)$lastInvoice->id + 1 : 1;
        return 'INV-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    // Calculate remaining balance
    public function calculateRemainingBalance(): void
    {
        $totalPaid = $this->payments()->sum('amount');
        $this->remaining_balance = $this->total_amount - $totalPaid;
        $this->advance_payment = $totalPaid; // Update advance payment to reflect actual payments
        $this->save();
    }

    // Update status based on payments
    public function updateStatus(): void
    {
        $this->calculateRemainingBalance();
        
        if ($this->remaining_balance <= 0) {
            $this->status = self::STATUS_PAID;
        } elseif ($this->due_date && now()->isAfter($this->due_date)) {
            $this->status = self::STATUS_OVERDUE;
        } else {
            $this->status = self::STATUS_PENDING;
        }
        
        $this->save();
    }

    // Check if invoice is fully paid
    public function isFullyPaid(): bool
    {
        return $this->remaining_balance <= 0;
    }

    // Check if invoice has partial payment
    public function hasPartialPayment(): bool
    {
        $totalPaid = $this->payments()->sum('amount');
        return $totalPaid > 0 && $totalPaid < $this->total_amount;
    }
}
