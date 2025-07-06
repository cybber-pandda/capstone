<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'latitude',
        'longitude',
        'logged_at',
        'remarks',
    ];

    protected $dates = ['logged_at'];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

}
