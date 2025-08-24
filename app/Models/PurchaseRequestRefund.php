<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestRefund extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'purchase_request_item_id',
        'product_id',
        'amount',
        'method',
        'reference',
        'proof',
        'admin_response',
        'processed_by',
        'status',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function purchaseRequestItem()
    {
        return $this->belongsTo(PurchaseRequestItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
