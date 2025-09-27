<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class B2BAddress extends Model
{
    use HasFactory;

    protected $table = 'b2b_address';

    protected $fillable = [
        'user_id',
        'street',
        'barangay',
        'city',
        'province',
        'zip_code',
        'full_address',
        'address_notes',
        'delivery_address_lat',
        'delivery_address_lng',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
