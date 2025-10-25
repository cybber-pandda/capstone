<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_request_id', 'product_id', 'quantity', 'subtotal','unit_price',];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function returnRequest()
    {
        return $this->hasOne(\App\Models\PurchaseRequestReturn::class, 'purchase_request_item_id');
    }

    public function refundRequest()
    {
        return $this->hasOne(\App\Models\PurchaseRequestRefund::class, 'purchase_request_item_id');
    }
}
