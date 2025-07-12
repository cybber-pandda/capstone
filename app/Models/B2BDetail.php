<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class B2BDetail extends Model
{
    use HasFactory;

    protected $table = 'b2b_details';

    protected $fillable = [
        'user_id', 'certificate_registration', 'business_permit', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
