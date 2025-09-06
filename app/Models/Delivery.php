<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'delivery_rider_id',
        'quantity',
        'tracking_number',
        'status',
        'delivery_date',
        'proof_delivery',
        'delivery_remarks',
        'sales_invoice_flg',
        'delivery_latitude',
        'delivery_longtitude',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected static function booted()
    {
        static::creating(function ($delivery) {
            if (empty($delivery->tracking_number)) {
                $delivery->tracking_number = strtoupper(Str::uuid());
            }
        });
    }

    public function deliveryUser()
    {
        return $this->belongsTo(User::class, 'delivery_rider_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function histories()
    {
        return $this->hasMany(DeliveryHistory::class);
    }

    public function latestHistory()
    {
        return $this->hasOne(DeliveryHistory::class)->latestOfMany('logged_at');
    }

    public function rating()
    {
        return $this->hasOne(DeliveryRating::class);
    }
}
