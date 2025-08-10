<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPartialPayment extends Model
{  

    protected $table = 'credit_partial_payments';

    protected $fillable = [
        'purchase_request_id',
        'bank_id',
        'paid_amount',
        'due_date',
        'amount_to_pay',
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

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function customer()
    {
        return $this->hasOneThrough(
            User::class,
            PurchaseRequest::class,
            'id',
            'id',
            'purchase_request_id',
            'customer_id'
        );
    }

    public function items()
    {
        return $this->hasManyThrough(
            PurchaseRequestItem::class,
            PurchaseRequest::class,
            'id',
            'purchase_request_id',
            'purchase_request_id',
            'id'
        );
    }

    public function isOverdue()
    {
        return $this->status === 'unpaid' && $this->due_date->isPast();
    }

}
