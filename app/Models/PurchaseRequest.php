<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'bank_id', 'status', 'proof_payment', 'pr_remarks'];

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
}
