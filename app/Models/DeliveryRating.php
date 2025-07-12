<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'rating',
        'feedback',
    ];

    public function delivery()
    {
        return $this->belongsTo(\App\Models\Delivery::class);
    }
}
