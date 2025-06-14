<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Adopter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'fullname',
        'bday',
        'city',
        'state',
        'zipcode',
        'phone', 
        'residence_type',
        'house_ownership',
        'household_pettype',
        'household_petage',
        'household_petprocedure',
        'household_petvaccination',
        // 'prefered_animaltype',
        // 'prefered_petgender',
        // 'prefered_petsize',
        // 'prefered_character',
        // 'prefered_activitylevel',
        'about_shelter',
        'reason_adopting'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function adoptionApplications()
    {
        return $this->hasMany(AdoptionApplication::class, 'animal_id');
    }

}
