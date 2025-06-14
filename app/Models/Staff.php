<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'staffs';

    protected $fillable = [
        'profile','firstname', 'lastname', 'birthday', 'email', 'city', 'state', 'zipcode', 'phone'
    ];
}
