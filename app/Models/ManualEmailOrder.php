<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
class ManualEmailOrder extends Model
{
    use HasFactory, Notifiable;
    
    protected $table = 'manual_email_order';

    protected $fillable = [
        'customer_name',
        'customer_type',
        'customer_email',
        'customer_address',
        'customer_phone_number',
        'order_date',
        'purchase_request',
        'remarks',
        'delivery_fee',
        'status'
    ];


}
