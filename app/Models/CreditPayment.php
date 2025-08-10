<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'bank_id',
        'paid_amount',
        'due_date',
        'paid_date',
        'status',
        'proof_payment',
        'reference_number',
        'approved_at',
        'approved_by',
        'notes'
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'paid_amount' => 'decimal:2'
    ];

    // Relationship to PurchaseRequest
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    // Relationship to Customer through PurchaseRequest
    public function customer()
    {
        return $this->hasOneThrough(
            User::class,
            PurchaseRequest::class,
            'id', // Foreign key on PurchaseRequest table
            'id', // Foreign key on User table
            'purchase_request_id', // Local key on CreditPayment table
            'customer_id' // Local key on PurchaseRequest table
        );
    }

    // Relationship to Items through PurchaseRequest
    public function items()
    {
        return $this->hasManyThrough(
            PurchaseRequestItem::class,
            PurchaseRequest::class,
            'id', // Foreign key on PurchaseRequest table
            'purchase_request_id', // Foreign key on PurchaseRequestItem table
            'purchase_request_id', // Local key on CreditPayment table
            'id' // Local key on PurchaseRequest table
        );
    }

    // Helper method to check if payment is overdue
    public function isOverdue()
    {
        return $this->status === 'unpaid' && $this->due_date->isPast();
    }

    // Helper method to record a payment
    public function recordPayment($amount, $date = null)
    {
        $this->paid_amount += $amount;
        
        if (!$this->paid_date) {
            $this->paid_date = $date ?? now();
        }

        if ($this->paid_amount >= $this->credit_amount) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
        }

        $this->save();
    }
}