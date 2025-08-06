<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['customer_id', 'bank_id', 'status', 'vat', 'delivery_fee', 'credit', 'payment_method', 'proof_payment', 'reference_number', 'pr_remarks'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function returns()
    {
        return $this->hasMany(PurchaseRequestReturn::class);
    }

    public function refunds()
    {
        return $this->hasMany(PurchaseRequestRefund::class);
    }

    public function creditPayment()
    {
        return $this->hasOne(CreditPayment::class);
    }

    public function createCreditPayment($dueDate, $totalAmount)
    {
        return $this->creditPayment()->create([
            'credit_amount' => $totalAmount,
            'due_date' => $dueDate,
            'status' => 'unpaid'
        ]);
    }
}
