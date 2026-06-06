<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_id',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date'
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
    

    // Payment method constants
    const METHOD_CASH = 'cash';
    const METHOD_CARD = 'card';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CHECK = 'check';
    const METHOD_OTHER = 'other';

    // Payment method check methods
    public function isCash(): bool
    {
        return $this->payment_method === self::METHOD_CASH;
    }

    public function isCard(): bool
    {
        return $this->payment_method === self::METHOD_CARD;
    }

    public function isBankTransfer(): bool
    {
        return $this->payment_method === self::METHOD_BANK_TRANSFER;
    }

    public function isCheck(): bool
    {
        return $this->payment_method === self::METHOD_CHECK;
    }

    // Get payment method display name
    public function getPaymentMethodDisplayAttribute(): string
    {
        return match($this->payment_method) {
            self::METHOD_CASH => 'Cash',
            self::METHOD_CARD => 'Card',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_CHECK => 'Check',
            self::METHOD_OTHER => 'Other',
            default => 'Unknown'
        };
    }

    // Boot method to update invoice status after payment
    protected static function booted()
    {
        static::created(function ($payment) {
            $payment->invoice->updateStatus();
        });

        static::updated(function ($payment) {
            $payment->invoice->updateStatus();
        });

        static::deleted(function ($payment) {
            $payment->invoice->updateStatus();
        });
    }

}
