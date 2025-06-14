<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Donation extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'donations';

    protected $fillable = [
        'shelter_id', 
        'user_id',
        'gcash_setting_id',
        'donation_amount',
        'upload_proof_donation',
        'status',
    ];

    public function shelter()
    {
        return $this->belongsTo(Shelter::class);
    }

    
}
