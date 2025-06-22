<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class b2bDetails extends Model
{
    use HasFactory;

    protected $table = 'b2b_details';

    protected $fillable = [
        'user_id', 'firstname', 'lastname', 'birthday', 'city', 'state', 'zipcode', 'phone'
    ];
}
