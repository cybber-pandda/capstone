<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdoptionRequirement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shelter_id','requirement_type','requirement_name','status'
    ];
}
