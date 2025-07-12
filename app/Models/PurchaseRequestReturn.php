<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'purchase_request_item_id',
        'product_id',
        'reason',
        'photo',
        'status',
        'admin_response',
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
}
